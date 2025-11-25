<?php

namespace App;

class JsonLoader
{
    public static function load(string $filename): array
    {
        $path =  $filename;
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: $path");
        }
        return json_decode(file_get_contents($path), true);
    }
}