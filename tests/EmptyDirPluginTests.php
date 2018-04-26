<?php


use League\Flysystem\Plugin\EmptyDir;
use PHPUnit\Framework\TestCase;

class EmptyDirPluginTests extends TestCase
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

    public function testPlugin()
    {
        $filesystem = $this->prophesize('League\Flysystem\FilesystemInterface');
        $plugin = new EmptyDir();
        $this->assertEquals('emptyDir', $plugin->getMethod());
        $plugin->setFilesystem($filesystem->reveal());
        $filesystem->listContents('dirname', false)->willReturn(array(
           array('type' => 'dir', 'path' => 'dirname/dir'),
           array('type' => 'file', 'path' => 'dirname/file.txt'),
           array('type' => 'dir', 'path' => 'dirname/another_dir'),
           array('type' => 'file', 'path' => 'dirname/another_file.txt'),
        ))->shouldBeCalled();

        $filesystem->delete('dirname/file.txt')->shouldBeCalled();
        $filesystem->delete('dirname/another_file.txt')->shouldBeCalled();
        $filesystem->deleteDir('dirname/dir')->shouldBeCalled();
        $filesystem->deleteDir('dirname/another_dir')->shouldBeCalled();

        $plugin->handle('dirname');
    }
}
