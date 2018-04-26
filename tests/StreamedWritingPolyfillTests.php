<?php

namespace League\Flysystem\Adapter;

use League\Flysystem\Config;
use League\Flysystem\Stub\StreamedWritingStub;
use PHPUnit\Framework\TestCase;

class StreamedWritingPolyfillTests extends TestCase
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

    public function testWrite()
    {
        $stream = tmpfile();
        fwrite($stream, 'contents');
        $stub = new StreamedWritingStub();
        $response = $stub->writeStream('path.txt', $stream, new Config());
        $this->assertEquals(array('path' => 'path.txt', 'contents' => 'contents'), $response);
        fclose($stream);
    }

    public function testUpdate()
    {
        $stream = tmpfile();
        fwrite($stream, 'contents');
        $stub = new StreamedWritingStub();
        $response = $stub->updateStream('path.txt', $stream, new Config());
        $this->assertEquals(array('path' => 'path.txt', 'contents' => 'contents'), $response);
        fclose($stream);
    }
}
