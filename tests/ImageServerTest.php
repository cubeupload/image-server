<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ImageServerTest extends TestCase
{
    public static $pngStub = [];

    public static function setUpBeforeClass()
    {
        static::$pngStub['file'] = __DIR__ . '/stubs/testimage.png';
        static::$pngStub['size'] = filesize(static::$pngStub['file']);
        static::$pngStub['hash'] = md5_file(static::$pngStub['file']);
        static::$pngStub['mime'] = mime_content_type(static::$pngStub['file']);
        
        Storage::disk('s3')->put(split_to_path(static::$pngStub['hash']), file_get_contents(static::$pngStub['file']));
        DB::table('images')->insert([
            'filename' => 'testimage.png',
            'filehash' => static::$pngStub['hash'],
            'filesize' => static::$pngStub['size'],
            'mimetype' => static::$pngStub['mime']
        ]);
    }

    public static function tearDownAfterClass()
    {
        Storage::disk('s3')->delete(split_to_path(static::$pngStub['hash']));
        DB::table('images')->where('filename', 'testimage.png')->delete();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGuestImage()
    {
        $response = $this->get('/testimage.png');

        $this->seeStatusCode(200);

        $this->assertEquals(
            static::$pngStub['hash'], md5($this->response->getContent())
        );
        
        $this->assertEquals(static::$pngStub['size'], $this->response->headers->get('Content-Length'));
        $this->assertEquals(static::$pngStub['mime'], $this->response->headers->get('Content-Type'));
    }
}
