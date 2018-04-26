<?php

namespace League\Flysystem\Stub;

class StreamedReadingStub
{
    /**
     * Reads a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        if ( ! $data = $this->read($path)) {
            return false;
        }

        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $data['contents']);
        rewind($stream);
        $data['stream'] = $stream;
        unset($data['contents']);

        return $data;
    }

    public function read($path)
    {
        if ($path === 'true.ext') {
            return array('contents' => $path);
        }

        return false;
    }
}
