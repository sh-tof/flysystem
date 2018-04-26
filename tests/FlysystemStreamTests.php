<?php

use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class FlysystemStreamTests extends TestCase
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

    public function testWriteStream()
    {
        $stream = tmpfile();
        $adapter = $this->prophesize('League\Flysystem\AdapterInterface');
        $adapter->has('file.txt')->willReturn(false)->shouldBeCalled();
        $adapter->writeStream('file.txt', $stream, Argument::type('League\Flysystem\Config'))
            ->willReturn(array('path' => 'file.txt'), false)
            ->shouldBeCalled();
        $filesystem = new Filesystem($adapter->reveal());
        $this->assertTrue($filesystem->writeStream('file.txt', $stream));
        $this->assertFalse($filesystem->writeStream('file.txt', $stream));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWriteStreamFail()
    {
        $filesystem = new Filesystem($this->createMock('League\Flysystem\AdapterInterface'));
        $filesystem->writeStream('file.txt', 'not a resource');
    }

    public function testUpdateStream()
    {
        $stream = tmpfile();
        $adapter = $this->prophesize('League\Flysystem\AdapterInterface');
        $adapter->has('file.txt')->willReturn(true)->shouldBeCalled();

        $adapter->updateStream('file.txt', $stream, Argument::type('League\Flysystem\Config'))
            ->willReturn(array('path' => 'file.txt'), false)
            ->shouldBeCalled();

        $filesystem = new Filesystem($adapter->reveal());

        $this->assertTrue($filesystem->updateStream('file.txt', $stream));
        $this->assertFalse($filesystem->updateStream('file.txt', $stream));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUpdateStreamFail()
    {
        $filesystem = new Filesystem($this->createMock('League\Flysystem\AdapterInterface'));
        $filesystem->updateStream('file.txt', 'not a resource');
    }

    public function testReadStream()
    {
        $adapter = $this->prophesize('League\Flysystem\AdapterInterface');
        $adapter->has(Argument::type('string'))->willReturn(true)->shouldBeCalled();
        $stream = tmpfile();
        $adapter->readStream('file.txt')->willReturn(array('stream' => $stream))->shouldBeCalled();
        $adapter->readStream('other.txt')->willReturn(false)->shouldBeCalled();
        $filesystem = new Filesystem($adapter->reveal());
        $this->assertInternalType('resource', $filesystem->readStream('file.txt'));
        $this->assertFalse($filesystem->readStream('other.txt'));
        fclose($stream);
        $this->assertFalse($filesystem->readStream('other.txt'));
    }
}
