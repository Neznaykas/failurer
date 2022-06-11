<?php

use PHPUnit\Framework\TestCase;

use Failure\LogParser;
use Failure\Generator;
use Failure\Model\Interval;
use Failure\Model\Intervals;

class LogParserTest extends TestCase
{
    private $logfile;

    public function setUp(): void
    {
        $this->logfile = __DIR__ . '/access.log';
        new Generator($this->logfile, 250, 50);
    }

    public function tearDown(): void 
    {
        $this->logs = null;
        unlink($this->logfile);
    }

    public function testProcess() 
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testGenerate()
    {
        unlink($this->logfile);

        new Generator($this->logfile, 250, 50);
        $this->assertFileExists($this->logfile);
    }

    public function testSortIntervals()
    {
        $sorted = new Intervals();
        $sorted->add(new Interval(5, 10, 10));
        $sorted->add(new Interval(4, 9, 10));
        $sorted->sort();

        $unsort = new Intervals();
        $unsort->add(new Interval(4, 9, 10));
        $unsort->add(new Interval(5, 10, 10));

        $this->assertIsArray($sorted->items);
        $this->assertIsArray($unsort->items);
        $this->assertEqualsCanonicalizing($sorted, $unsort);
    }
    
    public function testClassConstructor()
    {
        $data = new LogParser($this->logfile, 100.0, 30);

        $this->assertNotEmpty($data);
        $this->assertSame(100.0, $data->uptime);
        $this->assertIsArray($data->intervals->items);
    }

    public function testNotFile()
    {
        try {
            new LogParser('', 100.0, 30);
        } catch (Exception $e) {
            $this->assertFileDoesNotExist('Exception', $e);
        }
    }

    public function testNoAccess() 
    {
        $this->assertEquals(null, $this->logs->intervals);
    }

    public function testPrint() 
    {
        $logs = new LogParser($this->logfile, 100.0, 30);
        $data = (new Intervals())->add(new Interval(0, 2, 99.0));
        $logs->intervals->set($data->items);
        $logs->print();

        $expected = date('H:i:s', $data->get(0)->start) . ' - ' . date('H:i:s', $data->get(0)->end) . ' | ' . number_format($data->get(0)->uptime, 1) . PHP_EOL;
        $this->expectOutputString($expected);
    }
}
