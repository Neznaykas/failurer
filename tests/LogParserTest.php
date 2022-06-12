<?php

namespace Failure;

use PHPUnit\Framework\TestCase;

use Failure\Model\Interval;
use Failure\Model\Intervals;

class LogParserTest extends TestCase
{
    private string $logfile;

    protected function setUp(): void
    {
        $this->logfile = __DIR__ . '/access.log';
        (new Generator($this->logfile, 250, 50))->run();
    }

    protected function tearDown(): void
    {
        unlink($this->logfile);
    }

    public function testInstance()
    {
        $logger = $this->getMockBuilder(LogParser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(LogParser::class, $logger);
    }

    public function testClassConstructor()
    {
        $data = new LogParser($this->logfile, 100.0, 30);

        $this->assertSame(100.0, $data->uptime);
        $this->assertSame(0, $data->count);
        $this->assertSame(0, $data->errors);
        $this->assertEmpty($data->intervals->count());
    }

    public function testNotFile()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Не удалось открыть файл');

        (new LogParser('123123', 100, 30))->run();
    }

    public function testSimpleAnalize()
    {
        $random = new LogParser($this->logfile, 100, 30);
        $result = $random->run();

        $this->assertIsArray($result->intervals->get());
        $this->assertTrue($result->intervals->count() > 0);
    }

    public function testPrint()
    {
        $logs = new LogParser($this->logfile, 100, 30);
        $data = (new Intervals())->add(new Interval(0, 2, 99.9));
        $logs->intervals->set($data->get());
        $logs->print();

        $interval = $data->get()[0];

        $expected = date('H:i:s', $interval->start) . ' - ' . date('H:i:s', $interval->end) . ' | ' . number_format($interval->uptime, 1) . PHP_EOL;

        $this->expectOutputString($expected);
    }
}
