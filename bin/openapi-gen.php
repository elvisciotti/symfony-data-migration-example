<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$openapi = \OpenApi\scan('src');
\header('Content-Type: application/x-yaml');

echo $openapi->toYaml();

$openapi->saveAs(__DIR__ . '/../openapi30.yaml');
