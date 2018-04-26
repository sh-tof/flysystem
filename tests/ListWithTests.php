<?php

namespace League\Flysystem\Adapter;

use League\Flysystem\Plugin\ListWith;
use PHPUnit\Framework\TestCase;

class ListWithTests extends TestCase
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

    public function testHandle()
    {
        $prophecy = $this->prophesize('League\Flysystem\Filesystem');
        $prophecy->listContents('', true)->willReturn(array(
           array('path' => 'path.txt', 'type' => 'file'),
        ));
        $prophecy->getMimetype('path.txt')->willReturn('text/plain');
        $filesystem = $prophecy->reveal();

        $plugin = new ListWith();
        $plugin->setFilesystem($filesystem);
        $this->assertEquals('listWith', $plugin->getMethod());
        $listing = $plugin->handle(array('mimetype'), '', true);
        $this->assertContainsOnly('array', $listing, true);
        $first = reset($listing);
        $this->assertArrayHasKey('mimetype', $first);
    }

    public function testInvalidInput()
    {
        $prophecy = $this->prophesize('League\Flysystem\Filesystem');
        $prophecy->listContents('', true)->willReturn(array(
            array('path' => 'path.txt', 'type' => 'file'),
        ));
        $filesystem = $prophecy->reveal();

        $this->expectException('InvalidArgumentException');
        $plugin = new ListWith();
        $plugin->setFilesystem($filesystem);
        $plugin->handle(array('invalid'), '', true);
    }
}
