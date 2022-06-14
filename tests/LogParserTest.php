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
        $data = new LogParser($this->logfile, 100.0, 30, 0);

        $this->assertSame(100.0, $data->uptime);
        $this->assertSame(0, $data->count);
        $this->assertSame(0, $data->errors);
        $this->assertEmpty($data->intervals()->count());
    }

    public function testNotFile()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Не удалось открыть файл');

        (new LogParser('123123', 100, 30))->run();
    }

    public function testWrongLogFormat()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Некоректный формат лог файла');

        $truncated = '187.69.247.82 - - [10/06/2022:04:51:39 +1000] "PUT /rest/v1.4/documents?zone=default&_rid=e356713 HTTP/1.1" 200 2 40.0000 "-" "@list-item-updater" prio:0
        107.55.12.134 - - [10/06/2022:04:52:35 +1000] "POST /rest/v1.4/documents?zone=default&_rid=e356713 HTTP/1.1" 200 2 28.0000 "-" "@list-item-updater" prio:0
        118.228.37.131 - - "POST /rest/v1.4/documents?zone=default&_rid=e356713 HTTP/1.1" 2 24.0000';

        $file = __DIR__ . '/test.log';
        file_put_contents($file, $truncated);
        $random = new LogParser($file, 100, 20);

        try {
            $random->run();
        } finally {
            unlink($file);
        }
    }

    public function testOutOfMemoryMessage()
    {
        $this->expectWarning(E_USER_WARNING);
        $this->expectWarningMessage('Использование потоковой обработки с сохранением в память может вызвать недостаток памяти');

        new LogParser($this->logfile, 100, 30, 5, true, true);
    }

    public function testSaveToMemoryAnalize()
    {
        (new Generator($this->logfile, 300, 70))->run();

        $random = new LogParser($this->logfile, 100, 30, 5, false, true);
        $result = $random->run();

        $this->assertIsArray($result->intervals()->get());
        $this->assertTrue($result->intervals()->count() > 0);

        $this->assertGreaterThanOrEqual(2000000, memory_get_usage());
        unlink($this->logfile);
    }

    public function testSimpleAnalize()
    {
        (new Generator($this->logfile, 300, 70))->run();
        $random = new LogParser($this->logfile, 100, 30, 5, false, false);
        $result = $random->run();

        $this->assertTrue($result->intervals()->count() == 0);
        unlink($this->logfile);
    }

    public function testPrint()
    {
        $logs = new LogParser($this->logfile, 100, 30, 0);
        $data = (new Intervals())->add(new Interval(0, 2, 99.9));
        $logs->intervals()->set($data->get());

        foreach ($logs->intervals()->get() as $interval) {
            $startdate = date('H:i:s', $interval->start);
            $enddate = date('H:i:s', $interval->end);
            $uptime = number_format($interval->uptime, 1);

            echo "{$startdate} - {$enddate} | {$uptime}\n";
        }

        $interval = $data->get()[0];
        $expected = date('H:i:s', $interval->start) . ' - ' . date('H:i:s', $interval->end) . ' | ' . number_format($interval->uptime, 1) . PHP_EOL;
        $this->expectOutputString($expected);
    }
}
