<?php

namespace League\Flysystem\Adapter;

use League\Flysystem\Stub\StreamedCopyStub;
use PHPUnit\Framework\TestCase;

class StreamedCopyPolyfilTests extends TestCase
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

    public function testReadFail()
    {
        $copy = new StreamedCopyStub(false, null);

        $this->assertFalse($copy->copy('from', 'to'));
    }

    public function testWriteFail()
    {
        $stream = tmpfile();
        $readResponse = compact('stream');
        $copy = new StreamedCopyStub($readResponse, false);

        $this->assertFalse($copy->copy('from', 'to'));
        fclose($stream);
    }

    public function testSuccess()
    {
        $stream = tmpfile();
        $readResponse = compact('stream');
        $copy = new StreamedCopyStub($readResponse, $readResponse);

        $this->assertTrue($copy->copy('from', 'to'));

        if (is_resource($stream)) {
            fclose($stream);
        }
    }
}
