<?php

declare(strict_types=1);

namespace Tests;

use Reliability\Reliability;
use PHPUnit\Framework\TestCase;

class ReliabilityTest extends TestCase
{
    public function pathProvider()
    {
        return [
            ['/var/tmp/base/teste', 'teste'],
            ['/var/tmp/base/teste.txt', 'teste.txt'],
            ['/var/tmp/base/teste...txt', 'teste...txt'],
        ];
    }

    /** 
     * @test 
     * @dataProvider pathProvider
     */
    public function checkBasename($input, $expected)
    {
        $object = new Reliability();
        $this->assertEquals($expected, $object->basename($input));
    }

    public function filenameProvider()
    {
        return [
            ['/var/tmp/base/teste', 'teste'],
            ['/var/tmp/base/teste.txt', 'teste'],
            ['/var/tmp/base/teste...txt', 'teste..'],
        ];
    }

    /** 
     * @test 
     * @dataProvider filenameProvider
     */
    public function filename($input, $expected)
    {
        $object = new Reliability();
        $this->assertEquals($expected, $object->filename($input));
    }

    public function dirnameProvider()
    {
        return [
            ['/var/tmp/base/teste', '/var/tmp/base'],
            ['/../../base/teste.txt', '/../../base'],
            ['../../base/teste.txt', '../../base'],
        ];
    }

    /** 
     * @test 
     * @dataProvider dirnameProvider
     */
    public function dirname($input, $expected)
    {
        $object = new Reliability();
        $this->assertEquals($expected, $object->dirname($input));
    }

    public function isDirectoryProvider()
    {
        return [
            [__DIR__, true],
            [__DIR__ . '/bla/bla', false],
            [__DIR__ . '/ReliabilityTest.php', false],
            [__DIR__ . '/Files/v1.0.0', true],
            [__DIR__ . '/Files/v1.0.10', true],
            [__DIR__ . '/Files/v1.0.999', true],
            [__DIR__ . '/Files/v1.0.1', false],
            [__DIR__ . '/Files/v1.0.11', false],
            [__DIR__ . '/Files/v1.0.998', false],
        ];
    }

    /** 
     * @test 
     * @dataProvider isDirectoryProvider
     */
    public function isDirectory($input, $expected)
    {
        $object = new Reliability();
        $this->assertSame($expected, $object->isDirectory($input));
    }

    public function isFileProvider()
    {
        return [
            [__DIR__, false],
            [__DIR__ . '/bla/bla', false],
            [__DIR__ . '/ReliabilityTest.php', true],
            [__DIR__ . '/BlablaTest.php', false],
        ];
    }

    /** 
     * @test
     * @dataProvider isFileProvider
     */
    public function isFile($input, $expected)
    {
        $object = new Reliability();
        $this->assertSame($expected, $object->isFile($input));
    }

    /** @test */
    public function readFileLines()
    {
        $object = new Reliability();

        $file = $object->readFileLines(__DIR__ . '/ReliabilityTest.php');
        $this->assertIsArray($file);
        $this->assertEquals('<?php', $file[0]);
        $this->assertEquals('declare(strict_types=1);', $file[2]);
        $this->assertEquals('namespace Tests;', $file[4]);
    }

    /** @test */
    public function readFileLinesOfEmptyFile()
    {
        $object = new Reliability();

        $lines = $object->readFileLines(__DIR__ . '/Files/Empty.txt');
        $this->assertIsArray($lines);
        $this->assertCount(0, $lines);
    }

    /** @test */
    public function helper()
    {
        $object = reliability();
        $this->assertInstanceOf(Reliability::class, $object);
    }
}