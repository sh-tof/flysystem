<?php

use League\Flysystem\Config;
use PHPUnit\Framework\TestCase;

class ConfigTests extends TestCase
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

    public function testGet()
    {
        $config = new Config();
        $this->assertFalse($config->has('setting'));
        $this->assertNull($config->get('setting'));
        $config->set('setting', 'value');
        $this->assertEquals('value', $config->get('setting'));
        $fallback = new Config(array('fallback_setting' => 'fallback_value'));
        $config->setFallback($fallback);
        $this->assertEquals('fallback_value', $config->get('fallback_setting'));
    }

    public function testFallingBackWhenCallingHas()
    {
        $config = new Config();
        $fallback = new Config(array('setting_name' => true));
        $config->setFallback($fallback);

        $this->assertTrue($config->has('setting_name'));
    }
}
