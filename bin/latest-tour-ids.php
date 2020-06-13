#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->loadEnv(__DIR__ . '/../.env');

$komoot = new \Janthomas89\KomootApiClient\KomootApiClient(
    $_SERVER['KOMOOT_EMAIL'],
    $_SERVER['KOMOOT_PASSWORD']
);
$latestTourIds = $komoot->getLatestTourIds();

echo json_encode($latestTourIds, JSON_PRETTY_PRINT);
