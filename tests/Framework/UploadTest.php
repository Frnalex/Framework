<?php

namespace Tests\Framework;

use Framework\Upload;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

class UploadTest extends TestCase
{
    private Upload $upload;

    public function setUp(): void
    {
        $this->upload = new Upload('tests');
    }

    public function tearDown(): void
    {
        if (file_exists('tests' . DIRECTORY_SEPARATOR . 'test.jpg')) {
            unlink('tests' . DIRECTORY_SEPARATOR . 'test.jpg');
        }
    }

    public function testUpload(): void
    {
        $uplaodedFile = $this->getMockBuilder(UploadedFileInterface::class)->getMock();

        $uplaodedFile
            ->expects($this->any())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);

        $uplaodedFile
            ->expects($this->any())
            ->method('getClientFilename')
            ->willReturn('test.jpg');

        $uplaodedFile
            ->expects($this->once())
            ->method('moveTo')
            ->with($this->equalTo('tests' . DIRECTORY_SEPARATOR . 'test.jpg'));

        /** @var UploadedFileInterface $uplaodedFile */
        $this->assertEquals('test.jpg', $this->upload->upload($uplaodedFile));
    }

    public function testDontMoveIfFileNotUploaded(): void
    {
        $uplaodedFile = $this->getMockBuilder(UploadedFileInterface::class)->getMock();

        $uplaodedFile
            ->expects($this->any())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_CANT_WRITE);

        $uplaodedFile
            ->expects($this->any())
            ->method('getClientFilename')
            ->willReturn('test.jpg');

        $uplaodedFile
            ->expects($this->never())
            ->method('moveTo')
            ->with($this->equalTo('tests' . DIRECTORY_SEPARATOR . 'test.jpg'));

        /** @var UploadedFileInterface $uplaodedFile */
        $this->assertNull($this->upload->upload($uplaodedFile));
    }

    public function testUploadWithExistingFile(): void
    {
        $uplaodedFile = $this->getMockBuilder(UploadedFileInterface::class)->getMock();

        $uplaodedFile
            ->expects($this->any())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);

        $uplaodedFile
            ->expects($this->any())
            ->method('getClientFilename')
            ->willReturn('test.jpg');

        touch('tests' . DIRECTORY_SEPARATOR . 'test.jpg');

        $uplaodedFile
            ->expects($this->once())
            ->method('moveTo')
            ->with($this->equalTo('tests' . DIRECTORY_SEPARATOR . 'test_copy.jpg'));

        /** @var UploadedFileInterface $uplaodedFile */
        $this->assertEquals('test_copy.jpg', $this->upload->upload($uplaodedFile));
    }

    public function testDoNothingIfFileNotUploaded(): void
    {
        $file = $this->getMockBuilder(UploadedFileInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $file->method('getError')->willReturn(UPLOAD_ERR_CANT_WRITE);
        $file->expects($this->never())->method('moveTo');

        /** @var UploadedFileInterface $file */
        $this->upload->upload($file);
    }

    public function testCreateFormats(): void
    {
        @unlink('tests/demo.png');
        @unlink('tests/demo_thumb.png');
        $file = $this->getMockBuilder(UploadedFileInterface::class)->disableOriginalConstructor()->getMock();
        $file->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->method('getClientFileName')->willReturn('demo.png');
        $file->expects($this->once())->method('moveTo')->willReturnCallback(function () {
            imagepng(imagecreatetruecolor(1000, 1000), 'tests/demo.png');
        });

        // On crée un faux format
        $property = (new \ReflectionClass($this->upload))->getProperty('formats');
        $property->setAccessible(true);
        $property->setValue($this->upload, ['thumb' => [100, 200]]);

        // On s'attend à obtenir une image miniature
        /** @var UploadedFileInterface $file */
        $this->upload->upload($file);
        [$width, $height] = getimagesize('tests/demo_thumb.png');
        $this->assertEquals(100, $width);
        $this->assertEquals(200, $height);
        $this->assertFileExists('tests/demo_thumb.png');
        @unlink('tests/demo.png');
        @unlink('tests/demo_thumb.png');
    }

    public function testDeleteOldFormat(): void
    {
        // On crée un faux format
        $property = (new \ReflectionClass($this->upload))->getProperty('formats');
        $property->setAccessible(true);
        $property->setValue($this->upload, ['thumb' => [100, 200]]);
        // On s'attend à obtenir une image miniature
        touch('tests/demo.png');
        touch('tests/demo_thumb.png');
        $this->upload->delete('demo.png');
        $this->assertFileDoesNotExist('tests/demo.png');
        $this->assertFileDoesNotExist('tests/demo_thumb.png');
        @unlink('tests/demo.png');
        @unlink('tests/demo_thumb.png');
    }
}
