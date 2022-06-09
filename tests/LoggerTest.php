<?php

use PHPUnit\Framework\TestCase;

use Farpost\Logger;
use Farpost\Generator;

class LoggerTest extends TestCase
{
    private $logs;
    private $logfile;

    public function setUp(): void
    {
        //нагенерируем немного логов
        $this->logfile = __DIR__ . '/access.log';
        new Generator($this->logfile, 150, 100);

        $this->logs = new Logger($this->logfile, 100, 30);
    }

    public function tearDown(): void 
    {
        $this->logs = null;
        unlink($this->logfile);
    }

    public function testNoAccess() 
    {
        $this->assertEquals(null, new Generator('', 150, 100));
    }

    public function testProcess() 
    {
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testEmpty()
    {
        $this->assertNotEmpty($this->logs->intervals->get());
    }

    public function test()
    {
        $intervals = (new Logger($this->logfile, 95, 60))->intervals->sort()->get();

       // $this->assertEquals(200, $this->client->getStatusCode());
       
         $this->assertNotEmpty($intervals);

       /* $data = json_decode($response, true);
        $this->assertArrayHasKey('start', $data);*/
    }

}
