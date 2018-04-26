<?php

use League\Flysystem\Plugin\GetWithMetadata;
use PHPUnit\Framework\TestCase;

class GetWithMetadataTests extends TestCase
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

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $prophecy;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @before
     */
    public function setupFilesystem()
    {
        $this->prophecy = $this->prophesize('League\Flysystem\FilesystemInterface');
        $this->filesystem = $this->prophecy->reveal();
    }

    public function testGetMethod()
    {
        $plugin = new GetWithMetadata();
        $this->assertEquals('getWithMetadata', $plugin->getMethod());
    }

    public function testHandle()
    {
        $this->prophecy->getMetadata('path.txt')->willReturn(array(
            'path' => 'path.txt',
            'type' => 'file',
        ));
        $this->prophecy->getMimetype('path.txt')->willReturn('text/plain');

        $plugin = new GetWithMetadata();
        $plugin->setFilesystem($this->filesystem);
        $output = $plugin->handle('path.txt', array('mimetype'));
        $this->assertEquals(array(
            'path' => 'path.txt',
            'type' => 'file',
            'mimetype' => 'text/plain',
        ), $output);
    }

    public function testHandleFail()
    {
        $this->prophecy->getMetadata('path.txt')->willReturn(false);
        $plugin = new GetWithMetadata();
        $plugin->setFilesystem($this->filesystem);
        $output = $plugin->handle('path.txt', array('mimetype'));
        $this->assertFalse($output);
    }

    public function testHandleInvalid()
    {
        $this->expectException('InvalidArgumentException');
        $this->prophecy->getMetadata('path.txt')->willReturn(array(
            'path' => 'path.txt',
            'type' => 'file',
        ));

        $plugin = new GetWithMetadata();
        $plugin->setFilesystem($this->filesystem);
        $output = $plugin->handle('path.txt', array('invalid'));
    }
}
