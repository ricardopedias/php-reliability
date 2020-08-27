<?php

declare(strict_types=1);

namespace Reliability;

use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\Util;
use League\Flysystem\Adapter;
use LogicException;

/**
 * Esta cladse contém funções críticas do PHP
 * para centralização e implementadas com uma
 * abordagem mais segura.
 */
class Reliability
{
    /**
     * Obtém o nome + extensão de um arquivo especificado.
     * Ex: /dir/meu-arquivo.md -> meu-arquivo.md
     * @param string $filename
     * @return string
     */
    public function basename(string $filename): string
    {
        $filename = $this->removeInvalidWhiteSpaces($filename);
        return $this->pathinfo($filename)['basename'];
    }

    /**
     * Obtém o nome de um arquivo especificado.
     * Ex: /dir/meu-arquivo.md -> meu-arquivo
     * @param string $filename
     * @return string
     */
    public function filename(string $filename): string
    {
        $filename = $this->removeInvalidWhiteSpaces($filename);
        return $this->pathinfo($filename)['filename'];
    }

    /**
     * Obtém o nome de um diretório com base no caminho especificado.
     * Ex: /dir/meu-arquivo.md -> /dir
     * @param string $filenameOrDir
     * @param int $levelsCount
     * @return string
     */
    public function dirname(string $filenameOrDir, int $levelsCount = 1): string
    {
        $dir = $filenameOrDir;
        for ($level = 1; $level <= $levelsCount; $level++) {
            $filenameOrDir = $this->removeInvalidWhiteSpaces($dir);
            $dir = $this->pathinfo($filenameOrDir)['dirname'];
        }
        
        return $dir;
    }

    /**
     * Verifica se o caminho especificado existe e é um diretório.
     * @param string $path
     * @return bool
     */
    public function isDirectory(string $path): bool
    {
        $info = $this->pathinfo($path);
        $hasExtension = isset($info['extension']) === true
            && is_numeric($info['extension']) === false;

        return $this->pathExists($path) && $hasExtension === false;
    }

    /**
     * Verifica se o caminho especificado existe e é um diretório,
     * caso contrário, emite uma exceção.
     * @param string $path
     * @return bool
     */
    public function isDirectoryOrException(string $path): bool
    {
        if ($path === "" || $this->isDirectory($path) === false) {
            throw new Exception("The path {$path} does not exist or is not valid");
        }

        return true;
    }

    /**
     * Verifica se o caminho especificado existe e é um arquivo.
     * @param string $filename
     * @return bool
     */
    public function isFile(string $filename): bool
    {
        $info = $this->pathinfo($filename);
        return $this->pathExists($filename) && isset($info['extension']);
    }

    /**
     * Remove comentários e espaços desnecessários em um script PHP.
     * @param string $file
     * @return string
     */
    public function readFileWithoutCommentsAndWhiteSpaces(string $file): string
    {
        $file = (string)filter_var($file, FILTER_SANITIZE_STRING);
        return php_strip_whitespace($file);
    }

    /**
     * Devolve todas as linhas de um arquivo em forma de array
     * @param string $file
     * @return array<string>
     */
    public function readFileLines(string $file): array
    {
        $directory  = $this->dirname($file);
        $basename   = $this->basename($file);

        $filesystem = $this->mountDirectory($directory);
        $contents   = (string)$filesystem->read($basename);
        $lines      = explode("\n", $contents);

        // Se o array for vazio, devolve true
        if (!array_filter($lines)) {
            return [];
        }

        return $lines;
    }

    /**
     * Devolve uma instância do Filesystem apontando para o
     * diretório especificado.
     * @param string $path
     * @return \League\Flysystem\Filesystem
     * @throws LogicException
     */
    public function mountDirectory(string $path): Filesystem
    {
        $adapter = new Adapter\Local($path);
        return new Filesystem($adapter);
    }

    /**
     * Remove o diretório especificado.
     * @param string $path
     * @param bool $onlyContents
     * @return void
     */
    public function removeDirectory(string $path, bool $onlyContents = false): void
    {
        $parentDirectory = $this->dirname($path);
        $mainDirectory = $this->basename($path);

        $iterator = $this->mountDirectory($parentDirectory);

        $cleanup = $iterator->listContents("{$mainDirectory}/");
        foreach ($cleanup as $item) {
            if ($item['type'] === 'dir') {
                $iterator->deleteDir("{$item['path']}");
                continue;
            }
            $iterator->delete("{$item['path']}");
        }
        
        if ($onlyContents === false) {
            $iterator->deleteDir($mainDirectory);
        }
    }

    /**
     * Copia um diretório e seu conteúdo para outro lugar.
     * @param string $originPath
     * @param string $destinationPath
     * @return void
     * @trows Exception
     */
    public function copyDirectory(string $originPath, string $destinationPath): void
    {
        $origin      = $this->mountDirectory($originPath);
        $destination = $this->mountDirectory($destinationPath);

        $list = $origin->listContents("/", true);
        foreach ($list as $item) {
            if ($item['type'] === 'dir') {
                continue;
            }

            $contents = $origin->read($item['path']);
            if ($contents === false) {
                throw new Exception("The file called {$item['path']} cannot be read");
            }
            $destination->write($item['path'], $contents);
        }
    }

    /**
     * Move um diretório e seu conteúdo para outro lugar.
     * @param string $originPath
     * @param string $destinationPath
     * @return void
     */
    public function moveDirectory(string $originPath, string $destinationPath): void
    {
        $this->copyDirectory($originPath, $destinationPath);
        $this->removeDirectory($originPath);
    }

    /**
     * Remove um arquivo.
     * @param string $file
     * @return void
     */
    public function removeFile(string $file): void
    {
        $path = $this->dirname($file);
        $name = $this->basename($file);

        $location = $this->mountDirectory($path);
        $location->delete($name);
    }

    /**
     * Copia um arquivo para outro lugar.
     * @param string $originFile
     * @param string $destinationFile
     * @return void
     * @trows Exception
     */
    public function copyFile(string $originFile, string $destinationFile): void
    {
        $originPath          = $this->dirname($originFile);
        $destinationPath     = $this->dirname($destinationFile);
        $originFilename      = $this->basename($originFile);
        $destinationFilename = $this->basename($destinationFile);

        $origin      = $this->mountDirectory($originPath);
        $destination = $this->mountDirectory($destinationPath);

        $contents = $origin->read($originFilename);
        if ($contents === false) {
            throw new Exception("The file called {$originFilename} cannot be read");
        }

        $destination->write($destinationFilename, $contents);
    }

    /**
     * Move um arquivo para outro lugar.
     * @param string $originFile
     * @param string $destinationFile
     * @return void
     */
    public function moveFile(string $originFile, string $destinationFile): void
    {
        $this->copyFile($originFile, $destinationFile);
        $this->removeFile($originFile);
    }

    /**
     * @return array<string>
     */
    private function pathinfo(string $path): array
    {
        return Util::pathinfo($path);
    }

    /**
     * Verifica se o caminho especificado existe.
     * Pode ser um diretório ou um arquivo
     * @param string $path
     * @return bool
     */
    private function pathExists(string $path): bool
    {
        $path = $this->removeInvalidWhiteSpaces($path);
        return file_exists($path);
    }

    /**
     * Remove caracteres não imprimíveis e caracteres unicode inválidos.
     * @param string $path
     * @return string
     * @see vendor/league/flysystem/src/Util.php
     */
    private function removeInvalidWhiteSpaces(string $path): string
    {
        $path = (string)filter_var($path, FILTER_SANITIZE_STRING);

        while (preg_match('#\p{C}+|^\./#u', $path)) {
            $path = (string)preg_replace('#\p{C}+|^\./#u', '', $path);
        }

        return $path;
    }

    /**
     * Obtém o caminho absoluto do caminho relativo informado.
     * @see https://www.php.net/manual/en/function.realpath.php
     */
    public function absolutePath(string $path): ?string
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        }
        $search = explode('/', $path);
        $search = array_filter($search, function ($part) {
            return $part !== '.';
        });

        $append = [];
        $match  = false;
        while (count($search) > 0) {
            $match = realpath(implode('/', $search));
            if ($match !== false) {
                break;
            }
            array_unshift($append, array_pop($search));
        }
        if ($match === false) {
            $match = getcwd();
        }
        if (count($append) > 0) {
            $match .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $append);
        }
        return $match === false ? null : $match;
    }
}
