<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ImageServerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGuestImage()
    {
        $stub = __DIR__ .'/stubs/testimage.png';
        $hash = md5_file($stub);

        Storage::disk('s3')->put(split_to_path($hash), file_get_contents($stub));
        DB::table('images')->insert([
            'filename' => 'testimage.png',
            'filehash' => $hash,
            'filesize' => filesize($stub),
            'mimetype' => mime_content_type($stub)
        ]);

        $response = $this->get('/testimage.png');

        $this->seeStatusCode(200);

        $this->assertEquals(
            file_get_contents($stub), $this->response->getContent()
        );
        
        $this->assertEquals(filesize($stub), $this->response->headers->get('Content-Length'));
        $this->assertEquals(mime_content_type($stub), $this->response->headers->get('Content-Type'));
    }
}
