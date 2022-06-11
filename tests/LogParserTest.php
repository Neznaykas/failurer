<?php

use PHPUnit\Framework\TestCase;

use Failure\LogParser;
use Failure\Generator;
use PHPUnit\TextUI\TestFileNotFoundException;

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
        $data = new LogParser($this->logfile, 100.0, 30);

        $data->sort();

    
        $this->assertSame(100.0, $data->uptime);
        $this->assertEmpty($data->intervals->items);
    }

    public function testNotFile()
    {
        $this->expectException(Exception::class);
        new Generator('', 150, 100);
    }

    public function testNoAccess() 
    {
        $this->assertEquals(null, $this->logs->intervals);
    }

   /* public function testEmpty()
    {
        $this->assertNotEmpty($this->logs->intervals->items);
    }*/

   /* public function test()
    {
       // $intervals = (new LogParser($this->logfile, 95, 60))->intervals->sort()->get();

       // $this->assertEquals(200, $this->client->getStatusCode());
       
        // $this->assertNotEmpty($intervals);

       /* $data = json_decode($response, true);
        $this->assertArrayHasKey('start', $data);*/
    //}

}
