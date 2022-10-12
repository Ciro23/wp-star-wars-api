<?php
/**
Plugin Name: Swapi
Plugin URI: https://github.com/Ciro23/wp-star-wars-api
Author: ciro23
 **/

class Swapi {

    private $baseUrl = "https://swapi.dev/api/";
    private $query;
    private $storeFile;

    public function __construct(array $atts) {
        $this->setQuery($atts);
        $this->importCss();
    }

    public static function main(array $atts): mixed {
        ob_start();

        $swapi = new Swapi($atts);

        $swapi->displayContent();

        return ob_get_clean();
    }

    private function importCss(): void {
        echo "<style>";
        include_once __DIR__ . "/swapi-style.css";
        echo "</style>";
    }

    private function getContents(): stdClass {
        if ($this->canUseCacheFile()) {
            $contents = $this->readDataFromJson();
        } else {
            $contents = $this->makeHttpRequest();
            $contents = $this->writeDataToJson($contents);
        }

        return json_decode($contents) ?? new stdClass();
    }

    private function canUseCacheFile(): bool {
        if (!file_exists($this->storeFile)) {
            return false;
        }

        $lastEditDate = filemtime($this->storeFile);
        $now = time();

        if ($now - $lastEditDate > 24 * 60 * 60) {
            return false;
        }
        return true;
    }

    private function readDataFromJson(): string {
        $file = fopen($this->storeFile, "r");
        $contents = fread($file, filesize($this->storeFile));
        fclose($file);

        if ($contents === false) {
            return "{}";
        }
        return $contents;
    }

    private function writeDataToJson($contents) {
        $file = fopen($this->storeFile, "w");

        fwrite($file, $contents);
        fclose($file);

        return $contents;
    }

    private function makeHttpRequest(): string {
        return file_get_contents($this->baseUrl . $this->query);
    }

    private function displayContent(): void {
        $contents = $this->getContents();

        echo "<div class='swapi-container'>";
        switch ($this->query) {
            case "films":
                $this->displayFilms($contents);
                break;

            case "people":
                $this->displayPeople($contents);
                break;
        }
        echo "</div>";
    }

    private function displayFilms($contents): void {
        foreach ($contents->results as $content) {
            echo "<div>";
            echo "<h4>" . $content->title . "</h4>";
            echo "<div class='swapi-details-flexable'>";
                echo "<div>";
                    echo "<p>Director: " . $content->director . "</p>";
                    echo "<p>Release: " . $content->release_date . "</p>";
                echo "</div>";
            echo "</div>";
            echo "</div>";
        }
    }

    private function displayPeople($contents): void {
        foreach ($contents->results as $content) {
            echo "<div>";
            echo "<h4>" . $content->name . "</h4>";
            echo "<div class='swapi-details-flexable'>";
                echo "<div>";
                    echo "<p>Height: " . $content->height . "</p>";
                    echo "<p>Mass: " . $content->mass . "</p>";
                echo "</div>";
                echo "<div>";
                    echo "<p>Eyes: " . $content->eye_color . "</p>";
                    echo "<p>Gender: " . $content->gender . "</p>";
                echo "</div>";
                echo "<div>";
                    echo "<p>Birth year: " . $content->birth_year . "</p>";
                echo "</div>";
            echo "</div>";
            echo "</div>";
        }
    }

    private function setQuery($atts): void {
        if (isset($atts['query'])) {
            $this->query = $atts['query'];
        } else {
            $this->query = "films";
        }
        $this->setStoreFile();
    }

    private function setStoreFile(): void {
        $this->storeFile = "./swapi-cache-contents-{$this->query}.json";
    }
}

add_shortcode('swapi', 'Swapi::main');
