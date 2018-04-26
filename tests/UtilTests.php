<?php

namespace League\Flysystem;

use PHPUnit\Framework\TestCase;

class UtilTests extends TestCase
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

    public function testEmulateDirectories()
    {
        $input = array(
            array('dirname' => '', 'filename' => 'dummy', 'path' => 'dummy', 'type' => 'file'),
            array('dirname' => 'something', 'filename' => 'dummy', 'path' => 'something/dummy', 'type' => 'file'),
            array('dirname' => 'something', 'path' => 'something/dirname', 'type' => 'dir'),
        );
        $output = Util::emulateDirectories($input);
        $this->assertCount(4, $output);
    }

    public function testContentSize()
    {
        $this->assertEquals(5, Util::contentSize('12345'));
        $this->assertEquals(3, Util::contentSize('135'));
    }

    public function mapProvider()
    {
        return array(
            array(array('from.this' => 'value'), array('from.this' => 'to.this', 'other' => 'other'), array('to.this' => 'value')),
            array(array('from.this' => 'value', 'no.mapping' => 'lost'), array('from.this' => 'to.this'), array('to.this' => 'value')),
        );
    }

    /**
     * @dataProvider  mapProvider
     */
    public function testMap($from, $map, $expected)
    {
        $result = Util::map($from, $map);
        $this->assertEquals($expected, $result);
    }

    public function dirnameProvider()
    {
        return array(
            array('filename.txt', ''),
            array('dirname/filename.txt', 'dirname'),
            array('dirname/subdir', 'dirname'),
        );
    }

    /**
     * @dataProvider  dirnameProvider
     */
    public function testDirname($input, $expected)
    {
        $result = Util::dirname($input);
        $this->assertEquals($expected, $result);
    }

    public function testEnsureConfig()
    {
        $this->assertInstanceOf('League\Flysystem\Config', Util::ensureConfig(array()));
        $this->assertInstanceOf('League\Flysystem\Config', Util::ensureConfig(null));
        $this->assertInstanceOf('League\Flysystem\Config', Util::ensureConfig(new Config()));
    }

    /**
     * @expectedException  LogicException
     */
    public function testInvalidValueEnsureConfig()
    {
        Util::ensureConfig(false);
    }

    public function invalidPathProvider()
    {
        return array(
            array('something/../../../hehe'),
            array('/something/../../..'),
            array('..'),
            array('something\\..\\..'),
            array('\\something\\..\\..\\dirname'),
        );
    }

    /**
     * @expectedException  LogicException
     * @dataProvider       invalidPathProvider
     */
    public function testOutsideRootPath($path)
    {
        Util::normalizePath($path);
    }

    public function pathProvider()
    {
        return array(
            array('.', ''),
            array('/path/to/dir/.', 'path/to/dir'),
            array('/dirname/', 'dirname'),
            array('dirname/..', ''),
            array('dirname/../', ''),
            array('dirname./', 'dirname.'),
            array('dirname/./', 'dirname'),
            array('dirname/.', 'dirname'),
            array('./dir/../././', ''),
            array('/something/deep/../../dirname', 'dirname'),
            array('00004869/files/other/10-75..stl', '00004869/files/other/10-75..stl'),
            array('/dirname//subdir///subsubdir', 'dirname/subdir/subsubdir'),
            array('\dirname\\\\subdir\\\\\\subsubdir', 'dirname/subdir/subsubdir'),
            array('\\\\some\shared\\\\drive', 'some/shared/drive'),
            array('C:\dirname\\\\subdir\\\\\\subsubdir', 'C:/dirname/subdir/subsubdir'),
            array('C:\\\\dirname\subdir\\\\subsubdir', 'C:/dirname/subdir/subsubdir'),
            array('example/path/..txt', 'example/path/..txt'),
            array('\\example\\path.txt', 'example/path.txt'),
            array('\\example\\..\\path.txt', 'path.txt'),
            array("some\0/path.txt", 'some/path.txt'),
        );
    }

    /**
     * @dataProvider  pathProvider
     */
    public function testNormalizePath($input, $expected)
    {
        $result = Util::normalizePath($input);
        $double = Util::normalizePath(Util::normalizePath($input));
        $this->assertEquals($expected, $result);
        $this->assertEquals($expected, $double);
    }

    public function pathAndContentProvider()
    {
        return array(
            array('/some/file.css', '.event { background: #000; } ', 'text/css'),
            array('/some/file.css', 'body { background: #000; } ', 'text/css'),
            array('/some/file.txt', 'body { background: #000; } ', 'text/plain'),
            array('/1x1', base64_decode('R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs='), 'image/gif'),
        );
    }

    /**
     * @dataProvider  pathAndContentProvider
     */
    public function testGuessMimeType($path, $content, $expected)
    {
        $mimeType = Util::guessMimeType($path, $content);
        $this->assertEquals($expected, $mimeType);
    }

    public function testStreamSize()
    {
        $stream = tmpfile();
        fwrite($stream, 'aaa');
        $size = Util::getStreamSize($stream);
        $this->assertEquals(3, $size);
        fclose($stream);
    }

    public function testRewindStream()
    {
        $stream = tmpfile();
        fwrite($stream, 'something');
        $this->assertNotEquals(0, ftell($stream));
        Util::rewindStream($stream);
        $this->assertEquals(0, ftell($stream));
        fclose($stream);
    }

    public function testNormalizePrefix()
    {
        $this->assertEquals('test/', Util::normalizePrefix('test', '/'));
        $this->assertEquals('test/', Util::normalizePrefix('test/', '/'));
    }

    public function pathinfoPathProvider()
    {
        return array(
            array(''),
            array('.'),
            array('..'),
            array('...'),
            array('/.'),
            array('//.'),
            array('///.'),

            array('foo'),
            array('/foo'),
            array('/foo/bar'),
            array('/foo/bar/'),

            array('file.txt'),
            array('foo/file.txt'),
            array('/foo/file.jpeg'),

            array('.txt'),
            array('dir/.txt'),
            array('/dir/.txt'),

            array('foo/bar.'),
            array('foo/bar..'),
            array('foo/bar/.'),

            array('c:'),
            array('c:\\'),
            array('c:/'),
            array('c:file'),
            array('c:f:ile'),
            array('c:f:'),
            array('c:d:e:'),
            array('AB:file'),
            array('AB:'),
            array('d:\foo\bar'),
            array('E:\foo\bar\\'),
            array('f:\foo\bar:baz'),
            array('G:\foo\bar:'),
            array('c:/foo/bar'),
            array('c:/foo/bar/'),
            array('Y:\foo\bar.txt'),
            array('z:\foo\bar.'),
            array('foo\bar'),
        );
    }

    /**
     * @dataProvider  pathinfoPathProvider
     */
    public function testPathinfo($path)
    {
        $expected = compact('path') + pathinfo($path);

        if (isset($expected['dirname'])) {
            $expected['dirname'] = Util::normalizeDirname($expected['dirname']);
        }

        $this->assertSame($expected, Util::pathinfo($path));
    }

    public function testPathinfoHandlesUtf8()
    {
        $path = 'files/繁體中文字/test.txt';
        $expected = array(
            'path' => 'files/繁體中文字/test.txt',
            'dirname' => 'files/繁體中文字',
            'basename' => 'test.txt',
            'extension' => 'txt',
            'filename' => 'test',
        );
        $this->assertSame($expected, Util::pathinfo($path));

        $path = 'files/繁體中文字.txt';
        $expected = array(
            'path' => 'files/繁體中文字.txt',
            'dirname' => 'files',
            'basename' => '繁體中文字.txt',
            'extension' => 'txt',
            'filename' => '繁體中文字',
        );
        $this->assertSame($expected, Util::pathinfo($path));

        $path = '👨‍👩‍👧‍👦👨‍👩‍👦‍👦👨‍👩‍👧‍👧/繁體中文字.txt';
        $expected = array(
            'path' => '👨‍👩‍👧‍👦👨‍👩‍👦‍👦👨‍👩‍👧‍👧/繁體中文字.txt',
            'dirname' => '👨‍👩‍👧‍👦👨‍👩‍👦‍👦👨‍👩‍👧‍👧',
            'basename' => '繁體中文字.txt',
            'extension' => 'txt',
            'filename' => '繁體中文字',
        );
        $this->assertSame($expected, Util::pathinfo($path));

        $path = 'foo/bar.baz.😀😬😁';
        $expected = array(
            'path' => 'foo/bar.baz.😀😬😁',
            'dirname' => 'foo',
            'basename' => 'bar.baz.😀😬😁',
            'extension' => '😀😬😁',
            'filename' => 'bar.baz',
        );
        $this->assertSame($expected, Util::pathinfo($path));

        $path = '繁體中文字/👨‍👩‍👧‍👦.😺😸😹😻.😀😬😁';
        $expected = array(
            'path' => '繁體中文字/👨‍👩‍👧‍👦.😺😸😹😻.😀😬😁',
            'dirname' => '繁體中文字',
            'basename' => '👨‍👩‍👧‍👦.😺😸😹😻.😀😬😁',
            'extension' => '😀😬😁',
            'filename' => '👨‍👩‍👧‍👦.😺😸😹😻',
        );
        $this->assertSame($expected, Util::pathinfo($path));
    }
}
