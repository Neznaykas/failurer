<?php

use PHPUnit\Framework\TestCase;
use Farpost\Logger;

class ExampleApiTest extends TestCase
{
    private $logs;

    public function setUp(): void
    {
        //нагенерируем немного логов
        $logfile = __DIR__ . '/access.log';
        new Generator($logfile, 150);

        $this->logs = new Logger($logfile, 100, 30);
    }

    public function tearDown(): void {
        $this->logs = null;
    }

    public function testGetComments()
    {
        //403 Forbidden 
        //$this->assertEquals(403, $this->client->getStatusCode());

        //$this->assertNotEmpty($response);
    }

    public function testAddComment()
    {
        $params = [
            'message' => 'qwest',
            'owner' => '60d0fe4f5311236168a109d0',
            'post' => '60d21b7967d0d8992e610d1b'
        ];

        //$response = $this->client->addComment($params);

        //200 OK
        //$this->assertEquals(200, $this->client->getStatusCode());
      // $this->assertNotEmpty($response);
    }

    public function testUpdateComment()
    {
       /* $params = ['firstName' => 'qwest'];

        $response = $this->client->updateComment($params);

        //$this->assertEquals(200, $this->client->getStatusCode());
        $this->assertNotEmpty($response);

        $data = json_decode($response, true);
        $this->assertArrayHasKey('firstName', $data);*/
    }

}
