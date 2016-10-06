<?php

require __DIR__ . '/vendor/autoload.php';

use CubeUpload\Storage\DataLibrary;

$library = new DataLibrary("./storage/content/library");

$library->put('aaaaaa.png', './storage/content/anon/a/a/a/aaaaaa.png');
/*
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

$processed = 0;
foreach( $files as $name => $file )
{
    if ($file->getFilename() == "." || $file->getFilename() == "..")
        continue;

    $library->put($file->getFilename(), $file->getPathname());
    echo $file->getFilename() . "\n";
    ren
    $processed++;
}
echo "Processed {$processed} files.";
*/