<?php

use League\Flysystem\Plugin\ForcedRename;
use PHPUnit\Framework\TestCase;

class ForcedRenamePluginTests extends TestCase
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

    protected $filesystem;
    protected $plugin;

    public function setUp()
    {
        $this->filesystem = $this->prophesize('League\Flysystem\FilesystemInterface');
        $this->plugin = new ForcedRename();
        $this->plugin->setFilesystem($this->filesystem->reveal());
    }

    public function testPluginSuccess()
    {
        $this->assertSame('forceRename', $this->plugin->getMethod());

        $this->filesystem->delete('newpath')->willReturn(true)->shouldBeCalled();
        $this->filesystem->rename('path', 'newpath')->willReturn(true)->shouldBeCalled();

        $this->assertTrue($this->plugin->handle('path', 'newpath'));
    }

    public function testPluginDeleteNotExists()
    {
        $this->filesystem->delete('newpath')
            ->willThrow('League\Flysystem\FileNotFoundException', 'newpath')
            ->shouldBeCalled();

        $this->filesystem->rename('path', 'newpath')->willReturn(true)->shouldBeCalled();

        $this->assertTrue($this->plugin->handle('path', 'newpath'));
    }

    public function testPluginDeleteFail()
    {
        $this->filesystem->delete('newpath')->willReturn(false)->shouldBeCalled();
        $this->assertFalse($this->plugin->handle('path', 'newpath'));
    }
}
