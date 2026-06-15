<?php

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/resources/views'));

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (strpos($content, '"@context"') !== false) {
            file_put_contents($file->getPathname(), str_replace('"@context"', '"@@context"', $content));
            echo "Fixed: " . $file->getPathname() . "\n";
        }
    }
}
echo "Done.\n";
