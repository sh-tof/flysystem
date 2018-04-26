<?php

use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;

class NullAdapterTest extends TestCase
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
     * @return Filesystem
     */
    protected function getFilesystem()
    {
        return new Filesystem(new NullAdapter());
    }

    protected function getAdapter()
    {
        return new NullAdapter();
    }

    public function testWrite()
    {
        $fs = $this->getFilesystem();
        $result = $fs->write('path', 'contents');
        $this->assertTrue($result);
        $this->assertFalse($fs->has('path'));
    }

    /**
     * @expectedException  \League\Flysystem\FileNotFoundException
     */
    public function testRead()
    {
        $fs = $this->getFilesystem();
        $fs->read('something');
    }

    public function testHas()
    {
        $fs = $this->getFilesystem();
        $this->assertFalse($fs->has('something'));
    }

    public function testDelete()
    {
        $adapter = $this->getAdapter();
        $this->assertFalse($adapter->delete('something'));
    }

    public function expectedFailsProvider()
    {
        return array(
            array('read'),
            array('update'),
            array('read'),
            array('rename'),
            array('delete'),
            array('listContents', array()),
            array('getMetadata'),
            array('getSize'),
            array('getMimetype'),
            array('getTimestamp'),
            array('getVisibility'),
            array('deleteDir'),
        );
    }

    /**
     * @dataProvider expectedFailsProvider
     */
    public function testExpectedFails($method, $result = false)
    {
        $adapter = new NullAdapter();
        $this->assertEquals($result, $adapter->{$method}('one', 'two', new Config()));
    }

    public function expectedArrayResultProvider()
    {
        return array(
            array('write'),
            array('setVisibility'),
        );
    }

    /**
     * @dataProvider expectedArrayResultProvider
     */
    public function testArrayResult($method)
    {
        $adapter = new NullAdapter();
        $this->assertInternalType('array', $adapter->{$method}('one', tmpfile(), new Config(array('visibility' => 'public'))));
    }

    public function testArrayResultForCreateDir()
    {
        $adapter = new NullAdapter();
        $this->assertInternalType('array', $adapter->createDir('one', new Config(array('visibility' => 'public'))));
    }
}
