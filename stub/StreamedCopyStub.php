<?php

namespace League\Flysystem\Stub;

use League\Flysystem\Config;

class StreamedCopyStub
{
    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $response = $this->readStream($path);

        if ($response === false || ! is_resource($response['stream'])) {
            return false;
        }

        $result = $this->writeStream($newpath, $response['stream'], new Config());

        if ($result !== false && is_resource($response['stream'])) {
            fclose($response['stream']);
        }

        return $result !== false;
    }

    /**
     * @var resource
     */
    private $readResponse;

    /**
     * @var resource|null
     */
    private $writeResponse;

    public function __construct($readResponse, $writeResponse = null)
    {
        $this->readResponse = $readResponse;
        $this->writeResponse = $writeResponse;
    }

    /**
     * @param  string   $path
     * @return resource
     */
    public function readStream($path)
    {
        return $this->readResponse;
    }

    /**
     * @param  string   $path
     * @param  resource $resource
     * @param  Config   $config
     * @return resource
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->writeResponse;
    }
}
