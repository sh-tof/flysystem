<?php

use League\Flysystem\Directory;
use League\Flysystem\File;
use PHPUnit\Framework\TestCase;

class HandlerTests extends TestCase
{
    /**
     * @param string $exception
     */
    public function expectException($exception)
    {
        if (is_callable('parent::expectException')) {
            return parent::expectException($exception);
        }

        parent::setExpectedException($exception);
    }

    /**
     * Returns a test double for the specified class.
     *
     * @param string $originalClassName
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     *
     * @throws PHPUnit_Framework_Exception
     */
    protected function createMock($originalClassName)
    {
        if (is_callable('parent::createMock')) {
            return parent::createMock($originalClassName);
        }

        return $this->getMock($originalClassName);
    }

    public function testFileRead()
    {
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $prophecy->read('path.txt')->willReturn('contents');
        $filesystem = $prophecy->reveal();
        $file = new File(null, 'path.txt');
        $file->setFilesystem($filesystem);
        $output = $file->read();
        $this->assertEquals('contents', $output);
    }

    public function testFileDelete()
    {
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $prophecy->delete('path.txt')->willReturn(true);
        $filesystem = $prophecy->reveal();
        $file = new File(null, 'path.txt');
        $file->setFilesystem($filesystem);
        $output = $file->delete();
        $this->assertTrue($output);
    }

    public function testFileReadStream()
    {
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $prophecy->readStream('path.txt')->willReturn('contents');
        $filesystem = $prophecy->reveal();
        $file = new File(null, 'path.txt');
        $file->setFilesystem($filesystem);
        $output = $file->readStream();
        $this->assertEquals('contents', $output);
    }

    public function testFileUpdate()
    {
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $prophecy->update('path.txt', 'contents')->willReturn(true);
        $filesystem = $prophecy->reveal();
        $file = new File(null, 'path.txt');
        $file->setFilesystem($filesystem);
        $output = $file->update('contents');
        $this->assertTrue($output);
    }

    public function testFileUpdateStream()
    {
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $prophecy->updateStream('path.txt', 'contents')->willReturn(true);
        $filesystem = $prophecy->reveal();
        $file = new File(null, 'path.txt');
        $file->setFilesystem($filesystem);
        $output = $file->updateStream('contents');
        $this->assertTrue($output);
    }

    public function getterProvider()
    {
        return array(
            array('getTimestamp', 123),
            array('getMimetype', 'text/plain'),
            array('getVisibility', 'private'),
            array('getMetadata', array('some' => 'metadata')),
            array('getSize', 123),
        );
    }

    /**
     * @dataProvider getterProvider
     *
     * @param $method
     * @param $response
     */
    public function testGetters($method, $response)
    {
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $prophecy->{$method}('path.txt')->willReturn($response);
        $filesystem = $prophecy->reveal();
        $file = new File(null, 'path.txt');
        $file->setFilesystem($filesystem);
        $output = $file->{$method}();
        $this->assertEquals($response, $output);
    }

    public function testFileIsFile()
    {
        $response = array('type' => 'file');
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $prophecy->getMetadata('path.txt')->willReturn($response);
        $filesystem = $prophecy->reveal();
        $file = new File(null, 'path.txt');
        $file->setFilesystem($filesystem);
        $this->assertTrue($file->isFile());
    }

    public function testFileIsDir()
    {
        $response = array('type' => 'file');
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $prophecy->getMetadata('path.txt')->willReturn($response);
        $filesystem = $prophecy->reveal();
        $file = new File();
        $file->setPath('path.txt');
        $file->setFilesystem($filesystem);
        $this->assertFalse($file->isDir());
    }

    public function testFileGetPath()
    {
        $file = new File();
        $file->setPath('path.txt');
        $this->assertEquals('path.txt', $file->getPath());
    }

    public function testDirDelete()
    {
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $prophecy->deleteDir('path')->willReturn(true);
        $filesystem = $prophecy->reveal();
        $dir = new Directory(null, 'path');
        $dir->setFilesystem($filesystem);
        $output = $dir->delete();
        $this->assertTrue($output);
    }

    public function testDirListContents()
    {
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $prophecy->listContents('path', true)->willReturn($listing = array('listing'));
        $filesystem = $prophecy->reveal();
        $dir = new Directory(null, 'path');
        $dir->setFilesystem($filesystem);
        $output = $dir->getContents(true);
        $this->assertEquals($listing, $output);
    }

    public function testGetFilesystem()
    {
        $prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $filesystem = $prophecy->reveal();
        $dir = new Directory(null, 'path');
        $dir->setFilesystem($filesystem);
        $this->assertEquals($filesystem, $dir->getFilesystem());
    }
}
