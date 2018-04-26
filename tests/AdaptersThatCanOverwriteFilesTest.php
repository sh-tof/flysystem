<?php

namespace League\Flysystem\Adapter;

use League\Flysystem\Filesystem;
use League\Flysystem\Stub\FileOverwritingAdapterStub;
use PHPUnit\Framework\TestCase;

class AdaptersThatCanOverwriteFilesTest extends TestCase
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
     * @test
     */
    public function overwriting_files_with_put()
    {
        $filesystem = new Filesystem($adapter = new FileOverwritingAdapterStub());
        $filesystem->put('path.txt', 'string contents');

        $this->assertEquals('path.txt', $adapter->writtenPath);
        $this->assertEquals('string contents', $adapter->writtenContents);
    }

    /**
     * @test
     */
    public function overwriting_files_with_putStream()
    {
        $filesystem = new Filesystem($adapter = new FileOverwritingAdapterStub());
        $stream = tmpfile();
        fwrite($stream, 'stream contents');
        $filesystem->putStream('path.txt',$stream);
        fclose($stream);

        $this->assertEquals('path.txt', $adapter->writtenPath);
        $this->assertEquals('stream contents', $adapter->writtenContents);
    }
}
