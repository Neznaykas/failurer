<?php

use PHPUnit\Framework\TestCase;

use Failure\LogParser;
use Failure\Generator;

class LoggerTest extends TestCase
{
    private $logfile;

    public function setUp(): void
    {
        //нагенерируем немного логов
        $this->logfile = __DIR__ . '/access.log';
        new Generator($this->logfile, 150, 100);
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
    
    public function testClassConstructor()
    {
        $data = new LogParser($this->logfile, 100, 30);
    
        $this->assertSame(30, $data->uptime);
        $this->assertEmpty($data->intervals);
    }

    public function testNoAccess() 
    {
        $this->assertEquals(null, $this->logs->intervals);
    }

    public function testEmpty()
    {
        $this->assertNotEmpty($this->logs->intervals->get());
    }

    public function test()
    {
        $intervals = (new LogParser($this->logfile, 95, 60))->intervals->sort()->get();

       // $this->assertEquals(200, $this->client->getStatusCode());
       
         $this->assertNotEmpty($intervals);

       /* $data = json_decode($response, true);
        $this->assertArrayHasKey('start', $data);*/
    }

}
