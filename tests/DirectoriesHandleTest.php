<?php

declare(strict_types=1);

namespace Tests;

use Reliability\Reliability;

class DirectoriesHandleTest extends TestCase
{
    protected function setUp(): void
    {
        $this->setupDirectories();
    }

    /** @test */
    public function removeDirectory()
    {
        $path = $this->pathTestFiles('origin');
        @mkdir($path, 0777, true);
        file_put_contents($path . DIRECTORY_SEPARATOR . 'teste.txt', 'origin');

        $this->assertDirectoryExists($path);
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'teste.txt');

        $object = new Reliability();
        $object->removeDirectory($path);
        
        $this->assertDirectoryDoesNotExist($path);
        $this->assertFileDoesNotExist($path . DIRECTORY_SEPARATOR . 'teste.txt');
    }

    /** @test */
    public function removeDirectoryOnlyContents()
    {
        $path = $this->pathTestFiles('origin');
        @mkdir($path, 0777, true);
        file_put_contents($path . DIRECTORY_SEPARATOR . 'teste.txt', 'teste');

        $this->assertDirectoryExists($path);
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'teste.txt');

        $object = new Reliability();
        $object->removeDirectory($path, true);

        $this->assertDirectoryExists($path);
        $this->assertFileDoesNotExist($path . DIRECTORY_SEPARATOR . 'teste.txt');
    }

    /** @test */
    public function removeFile()
    {
        $originPath      = $this->pathTestFiles('origin');
        $destinationPath = $this->pathTestFiles('destination');

        $file = $originPath . DIRECTORY_SEPARATOR . 'one.txt';
        file_put_contents($file, 'teste');
        

        $object = new Reliability();

        $this->assertFileExists($file);
        $object->removeFile($file);
        $this->assertFileDoesNotExist($file);
    }

    /** @test */
    public function copyDirectory()
    {
        $originPath      = $this->pathTestFiles('origin');
        $destinationPath = $this->pathTestFiles('destination');

        @mkdir($originPath . DIRECTORY_SEPARATOR . 'subdir');

        $filesList = [
            $originPath . DIRECTORY_SEPARATOR . 'one.txt',
            $originPath . DIRECTORY_SEPARATOR . 'two.txt',
            $originPath . DIRECTORY_SEPARATOR . 'three.txt',
            implode(DIRECTORY_SEPARATOR, [$originPath, 'subdir', 'one.txt']),
            implode(DIRECTORY_SEPARATOR, [$originPath, 'subdir', 'two.txt']),
            implode(DIRECTORY_SEPARATOR, [$originPath, 'subdir', 'three.txt']),
        ];

        array_walk($filesList, function($filePath){
            file_put_contents($filePath, 'teste');
        });

        array_walk($filesList, function($filePath){
            $this->assertFileExists($filePath);
        });

        $object = new Reliability();
        $object->copyDirectory($originPath, $destinationPath);
        
        array_walk($filesList, function($filePath) use ($originPath, $destinationPath){
            $filePath = str_replace($originPath, $destinationPath, $filePath);
            $this->assertFileExists($filePath);
        });
    }

    /** @test */
    public function copyFile()
    {
        $originPath      = $this->pathTestFiles('origin');
        $destinationPath = $this->pathTestFiles('destination');

        $file = $originPath . DIRECTORY_SEPARATOR . 'one.txt';
        file_put_contents($file, 'teste');
        $this->assertFileExists($file);

        $object = new Reliability();

        $copy = $destinationPath . DIRECTORY_SEPARATOR . 'copy.txt';
        $object->copyFile($file, $copy);
        
        $this->assertFileExists($file);
        $this->assertFileExists($copy);
    }

    /** @test */
    public function moveDirectory()
    {
        $originPath      = $this->pathTestFiles('origin');
        $destinationPath = $this->pathTestFiles('destination');

        @mkdir($originPath . DIRECTORY_SEPARATOR . 'subdir');

        $filesList = [
            $originPath . DIRECTORY_SEPARATOR . 'one.txt',
            $originPath . DIRECTORY_SEPARATOR . 'two.txt',
            $originPath . DIRECTORY_SEPARATOR . 'three.txt',
            implode(DIRECTORY_SEPARATOR, [$originPath, 'subdir', 'one.txt']),
            implode(DIRECTORY_SEPARATOR, [$originPath, 'subdir', 'two.txt']),
            implode(DIRECTORY_SEPARATOR, [$originPath, 'subdir', 'three.txt']),
        ];

        array_walk($filesList, function($filePath){
            file_put_contents($filePath, 'teste');
        });

        array_walk($filesList, function($filePath){
            $this->assertFileExists($filePath);
        });

        $object = new Reliability();
        $object->moveDirectory($originPath, $destinationPath);
        
        // Arquivos originais não existem mais
        array_walk($filesList, function($filePath){
            $this->assertFileDoesNotExist($filePath);
        });

        // Novos arquivos criados com sucesso
        array_walk($filesList, function($filePath) use ($originPath, $destinationPath){
            $filePath = str_replace($originPath, $destinationPath, $filePath);
            $this->assertFileExists($filePath);
        });
    }

    /** @test */
    public function moveFile()
    {
        $originPath      = $this->pathTestFiles('origin');
        $destinationPath = $this->pathTestFiles('destination');

        $file = $originPath . DIRECTORY_SEPARATOR . 'one.txt';
        file_put_contents($file, 'teste');
        $this->assertFileExists($file);

        $object = new Reliability();

        $copy = $destinationPath . DIRECTORY_SEPARATOR . 'copy.txt';
        $object->moveFile($file, $copy);
        
        $this->assertFileDoesNotExist($file);
        $this->assertFileExists($copy);
    }
}