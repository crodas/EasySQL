<?php

require __DIR__ . "/../vendor/autoload.php";

foreach (glob(__DIR__ . '/tmp/*') as $file) {
    @unlink($file);
}

crodas\FileUtil\File::overrideFilepathGenerator(function($prefix) {
    return __DIR__ . '/tmp/' . $prefix;
});

$pdo  = new PDO("sqlite::memory:");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conn = new EasySQL\EasySQL(__DIR__ . '/queries', $pdo);

