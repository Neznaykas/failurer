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
        unlink($this->logfile);
    }

    public function testWrite()
    {
        $errorHappened = true;

        try {
            new Generator('123', 250, 50);
        } catch (\Exception $e) {
            $errorHappened = true;
        }

        $this->assertTrue($errorHappened);
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
