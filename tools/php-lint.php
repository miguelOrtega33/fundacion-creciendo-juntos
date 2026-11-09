<?php

$directories = [
    __DIR__.'/../app',
    __DIR__.'/../bootstrap',
    __DIR__.'/../config',
    __DIR__.'/../database',
    __DIR__.'/../routes',
    __DIR__.'/../tests',
];

$failed = false;

foreach ($directories as $directory) {
    if (! is_dir($directory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $output = [];
        $exitCode = 0;
        exec(PHP_BINARY.' -l '.escapeshellarg($file->getPathname()), $output, $exitCode);

        if ($exitCode !== 0) {
            $failed = true;
            echo implode(PHP_EOL, $output).PHP_EOL;
        }
    }
}

exit($failed ? 1 : 0);
