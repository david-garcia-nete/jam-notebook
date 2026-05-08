#!/usr/bin/env php
<?php

declare(strict_types=1);

$projectRoot = __DIR__;
$envPath = $projectRoot . '/.env';
$backupDir = $projectRoot . '/backups';
$sailPath = $projectRoot . '/vendor/bin/sail';

function fail(string $message): void
{
    fwrite(STDERR, "Error: {$message}\n");
    exit(1);
}

function parseEnvValue(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (
        (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
        (str_starts_with($value, "'") && str_ends_with($value, "'"))
    ) {
        $value = substr($value, 1, -1);
    }

    return $value;
}

function readEnvVariables(string $envPath, array $keys): array
{
    if (!is_file($envPath)) {
        fail('.env file is missing.');
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        fail('Unable to read .env file.');
    }

    $values = [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);

        if (in_array($key, $keys, true)) {
            $values[$key] = parseEnvValue($value);
        }
    }

    return $values;
}

$requiredKeys = [
    'DB_CONNECTION',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
];

$config = readEnvVariables($envPath, $requiredKeys);

$missing = array_filter(
    $requiredKeys,
    static fn(string $key): bool => !array_key_exists($key, $config) || $config[$key] === ''
);

if ($missing !== []) {
    fail('Incomplete DB configuration in .env. Missing: ' . implode(', ', $missing));
}

if (strtolower($config['DB_CONNECTION']) !== 'mysql') {
    fail('This script currently supports only DB_CONNECTION=mysql.');
}

if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
    fail('Failed to create backups directory at ' . $backupDir);
}

if (!is_executable($sailPath)) {
    fail('Laravel Sail is unavailable. Expected executable at ' . $sailPath);
}

$timestamp = date('Y-m-d-His');
$fileName = sprintf('jam-notebook-%s.sql', $timestamp);
$backupPath = $backupDir . '/' . $fileName;

$dumpCommand = sprintf(
    '%s exec -T mysql sh -lc %s',
    escapeshellarg($sailPath),
    escapeshellarg(sprintf(
        'exec mysqldump -h%s -P%s -u%s -p%s %s',
        escapeshellarg($config['DB_HOST']),
        escapeshellarg($config['DB_PORT']),
        escapeshellarg($config['DB_USERNAME']),
        escapeshellarg($config['DB_PASSWORD']),
        escapeshellarg($config['DB_DATABASE'])
    ))
);

$fullCommand = $dumpCommand . ' > ' . escapeshellarg($backupPath) . ' 2>&1';

exec($fullCommand, $output, $exitCode);

if ($exitCode !== 0) {
    if (is_file($backupPath)) {
        @unlink($backupPath);
    }

    fail("mysqldump failed.\n" . implode("\n", $output));
}

if (!is_file($backupPath)) {
    fail('mysqldump completed, but no backup file was created.');
}

$size = filesize($backupPath);
if ($size === false) {
    fail('Backup created, but failed to determine file size.');
}

echo 'Database: ' . $config['DB_DATABASE'] . PHP_EOL;
echo 'Backup path: ' . $backupPath . PHP_EOL;
echo 'File size: ' . number_format($size) . ' bytes' . PHP_EOL;
