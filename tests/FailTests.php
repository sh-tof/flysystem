<?php

namespace League\Flysystem\Adapter
{
    function file_put_contents($name)
    {
        if (strpos($name, 'pleasefail') !== false) {
            return false;
        }

        return call_user_func_array('file_put_contents', func_get_args());
    }

    function file_get_contents($name)
    {
        if (strpos($name, 'pleasefail') !== false) {
            return false;
        }

        return call_user_func_array('file_get_contents', func_get_args());
    }
}

namespace League\Flysystem
{
    use PHPUnit\Framework\TestCase;

    class FailTests extends TestCase
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

        public function testFails()
        {
            $adapter = new Adapter\Local(__DIR__ . '/files');
            $this->assertFalse($adapter->write('pleasefail.txt', 'content', new Config()));
            $this->assertFalse($adapter->update('pleasefail.txt', 'content', new Config()));
            $this->assertFalse($adapter->read('pleasefail.txt'));
            $this->assertFalse($adapter->deleteDir('non-existing'));
        }
    }
}
