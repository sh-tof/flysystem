<?php

use League\Flysystem\Plugin\ListFiles;
use PHPUnit\Framework\TestCase;

class ListFilesTests extends TestCase
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

    private $filesystem;
    private $actualFilesystem;

    /**
     * @before
     */
    public function setupFilesystem()
    {
        $this->filesystem = $this->prophesize('League\Flysystem\FilesystemInterface');
        $this->actualFilesystem = $this->filesystem->reveal();
    }

    public function testHandle()
    {
        $plugin = new ListFiles();
        $this->assertEquals('listFiles', $plugin->getMethod());
        $this->filesystem->listContents('dirname', true)->willReturn(array(
            array('path' => 'dirname', 'type' => 'dir'),
            array('path' => 'dirname/path.txt', 'type' => 'file'),
        ));
        $plugin->setFilesystem($this->actualFilesystem);
        $output = $plugin->handle('dirname', true);
        $this->assertEquals(array(array('path' => 'dirname/path.txt', 'type' => 'file')), $output);
    }
}
