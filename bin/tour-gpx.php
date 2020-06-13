#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->loadEnv(__DIR__ . '/../.env');

$tourId = isset($argv[1]) ? (int)$argv[1] : 0;
if ($tourId <= 0) {
    throw new \RuntimeException('Invalid tour id "' . (isset($argv[1]) ? $argv[1] : '') . '" given.');
}

$komoot = new \Janthomas89\KomootApiClient\KomootApiClient(
    $_SERVER['KOMOOT_EMAIL'],
    $_SERVER['KOMOOT_PASSWORD']
);
$tourGpx = $komoot->getTourGpx($tourId);

echo $tourGpx;
