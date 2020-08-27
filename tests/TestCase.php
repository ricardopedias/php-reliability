<?php

declare(strict_types=1);

namespace Tests;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Reliability\Reliability;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

class TestCase extends PhpUnitTestCase
{
    protected function setupDirectories(): void
    {
        $originPath = $this->pathTestFiles('origin');
        if (is_dir($originPath) === false) {
            @mkdir($originPath, 0777, true);
        }
        $this->clearDirectory($originPath);

        $destinationPath = $this->pathTestFiles('destination');
        if (is_dir($destinationPath) === false) {
            @mkdir($destinationPath, 0777, true);
        }
        $this->clearDirectory($destinationPath);
    }

    private function clearDirectory($path): void
    {
        $directory = new Filesystem(new Local($path));

        $cleanup = $directory->listContents('/');
        foreach ($cleanup as $item) {
            if ($item['type'] === 'dir') {
                $directory->deleteDir("{$item['path']}");
                continue;
            }
            $directory->delete("{$item['path']}");
        }
    }

    protected function pathTestFiles(string $path): string
    {
        $path = trim($path, '/');
        return implode(DIRECTORY_SEPARATOR, [__DIR__, 'Files', $path]);
    }
}