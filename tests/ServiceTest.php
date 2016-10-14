<?php

use CubeUpload\Storage\DataLibrary;

class ServiceTest extends TestCase
{
    public static $fixtures_dir;

    public static function setUpBeforeClass()
    {
        self::$fixtures_dir = base_path() . '/tests/fixtures';
    }

    public function testGuestImage()
    {
        $library = new DataLibrary(env('CONTENT_LIBRARY_DIR'));
        $library->put('testimage.png', self::$fixtures_dir . '/testimage.png');
        $response = $this->call('GET', '/testimage.png');

        $this->assertResponseOk($this);
        $this->assertEquals(file_get_contents(self::$fixtures_dir . '/testimage.png'), $response->content());
    }

    public function testGuestImageThumb()
    {
        $response = $this->call('GET', '/t/testimage.png');

        $this->assertResponseOk($this);        
    }

}