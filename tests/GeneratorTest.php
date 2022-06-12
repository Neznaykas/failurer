<?php

namespace Failure;

use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    private string $logfile;

    protected function setUp(): void
    {
        $this->logfile = __DIR__ . '/access.log';
    }

    protected function tearDown(): void 
    {

    }
 
    public function testWrite()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Ошибка открытия файла, для записи');

        (new Generator('/is-not-writeable/file', 250, 50))->run();
    }

    public function testGenerate()
    {
        (new Generator($this->logfile, 250, 50))->run();
        
        $buffer = __DIR__ . '/access1.log';
        (new Generator($buffer, 250, 50))->run();

        $this->assertFileExists($buffer);
        $this->assertFileExists($this->logfile);
        $this->assertFileNotEquals($buffer, $this->logfile);
        unlink($buffer);
    }
}
