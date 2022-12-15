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

    public function testUpload()
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

    public function testDontMoveIfFileNotUploaded()
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

    public function testUploadWithExistingFile()
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
}
