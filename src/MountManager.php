<?php

namespace League\Flysystem;

use InvalidArgumentException;
use League\Flysystem\FilesystemNotFoundException;
use League\Flysystem\Plugin\PluginNotFoundException;
use LogicException;

/**
 * Class MountManager.
 *
 * Proxies methods to Filesystem (@see __call):
 *
 * @method AdapterInterface getAdapter($prefix)
 * @method Config getConfig($prefix)
 * @method bool has($path)
 * @method bool write($path, $contents, array $config = array())
 * @method bool writeStream($path, $resource, array $config = array())
 * @method bool put($path, $contents, $config = array())
 * @method bool putStream($path, $contents, $config = array())
 * @method string readAndDelete($path)
 * @method bool update($path, $contents, $config = array())
 * @method bool updateStream($path, $resource, $config = array())
 * @method string|false read($path)
 * @method resource|false readStream($path)
 * @method bool rename($path, $newpath)
 * @method bool delete($path)
 * @method bool deleteDir($dirname)
 * @method bool createDir($dirname, $config = array())
 * @method array listFiles($directory = '', $recursive = false)
 * @method array listPaths($directory = '', $recursive = false)
 * @method array getWithMetadata($path, array $metadata)
 * @method string|false getMimetype($path)
 * @method string|false getTimestamp($path)
 * @method string|false getVisibility($path)
 * @method int|false getSize($path);
 * @method bool setVisibility($path, $visibility)
 * @method array|false getMetadata($path)
 * @method Handler get($path, Handler $handler = null)
 * @method Filesystem flushCache()
 * @method void assertPresent($path)
 * @method void assertAbsent($path)
 * @method Filesystem addPlugin(PluginInterface $plugin)
 */
class MountManager
{
    /**
     * @var array
     */
    protected $plugins = array();

    /**
     * Register a plugin.
     *
     * @param PluginInterface $plugin
     *
     * @throws LogicException
     *
     * @return $this
     */
    public function addPlugin(PluginInterface $plugin)
    {
        if ( ! method_exists($plugin, 'handle')) {
            throw new LogicException(get_class($plugin) . ' does not have a handle method.');
        }

        $this->plugins[$plugin->getMethod()] = $plugin;

        return $this;
    }

    /**
     * Find a specific plugin.
     *
     * @param string $method
     *
     * @throws PluginNotFoundException
     *
     * @return PluginInterface
     */
    protected function findPlugin($method)
    {
        if ( ! isset($this->plugins[$method])) {
            throw new PluginNotFoundException('Plugin not found for method: ' . $method);
        }

        return $this->plugins[$method];
    }

    /**
     * Invoke a plugin by method name.
     *
     * @param string              $method
     * @param array               $arguments
     * @param FilesystemInterface $filesystem
     *
     * @throws PluginNotFoundException
     *
     * @return mixed
     */
    protected function invokePlugin($method, array $arguments, FilesystemInterface $filesystem)
    {
        $plugin = $this->findPlugin($method);
        $plugin->setFilesystem($filesystem);
        $callback = array($plugin, 'handle');

        return call_user_func_array($callback, $arguments);
    }

    /**
     * @var FilesystemInterface[]
     */
    protected $filesystems = array();

    /**
     * Constructor.
     *
     * @param FilesystemInterface[] $filesystems [:prefix => Filesystem,]
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $filesystems = array())
    {
        $this->mountFilesystems($filesystems);
    }

    /**
     * Mount filesystems.
     *
     * @param FilesystemInterface[] $filesystems [:prefix => Filesystem,]
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function mountFilesystems(array $filesystems)
    {
        foreach ($filesystems as $prefix => $filesystem) {
            $this->mountFilesystem($prefix, $filesystem);
        }

        return $this;
    }

    /**
     * Mount filesystems.
     *
     * @param string              $prefix
     * @param FilesystemInterface $filesystem
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function mountFilesystem($prefix, FilesystemInterface $filesystem)
    {
        if ( ! is_string($prefix)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects argument #1 to be a string.');
        }

        $this->filesystems[$prefix] = $filesystem;

        return $this;
    }

    /**
     * Get the filesystem with the corresponding prefix.
     *
     * @param string $prefix
     *
     * @throws FilesystemNotFoundException
     *
     * @return FilesystemInterface
     */
    public function getFilesystem($prefix)
    {
        if ( ! isset($this->filesystems[$prefix])) {
            throw new FilesystemNotFoundException('No filesystem mounted with prefix ' . $prefix);
        }

        return $this->filesystems[$prefix];
    }

    /**
     * Retrieve the prefix from an arguments array.
     *
     * @param array $arguments
     *
     * @throws InvalidArgumentException
     *
     * @return array [:prefix, :arguments]
     */
    public function filterPrefix(array $arguments)
    {
        if (empty($arguments)) {
            throw new InvalidArgumentException('At least one argument needed');
        }

        $path = array_shift($arguments);

        if ( ! is_string($path)) {
            throw new InvalidArgumentException('First argument should be a string');
        }

        list($prefix, $path) = $this->getPrefixAndPath($path);
        array_unshift($arguments, $path);

        return array($prefix, $arguments);
    }

    /**
     * @param string $directory
     * @param bool   $recursive
     *
     * @throws InvalidArgumentException
     * @throws FilesystemNotFoundException
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        list($prefix, $directory) = $this->getPrefixAndPath($directory);
        $filesystem = $this->getFilesystem($prefix);
        $result = $filesystem->listContents($directory, $recursive);

        foreach ($result as &$file) {
            $file['filesystem'] = $prefix;
        }

        return $result;
    }

    /**
     * Call forwarder.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws InvalidArgumentException
     * @throws FilesystemNotFoundException
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        list($prefix, $arguments) = $this->filterPrefix($arguments);

        return $this->invokePluginOnFilesystem($method, $arguments, $prefix);
    }

    /**
     * @param string $from
     * @param string $to
     * @param array  $config
     *
     * @throws InvalidArgumentException
     * @throws FilesystemNotFoundException
     *
     * @return bool
     */
    public function copy($from, $to, array $config = array())
    {
        list($prefixFrom, $from) = $this->getPrefixAndPath($from);

        $buffer = $this->getFilesystem($prefixFrom)->readStream($from);

        if ($buffer === false) {
            return false;
        }

        list($prefixTo, $to) = $this->getPrefixAndPath($to);

        $result = $this->getFilesystem($prefixTo)->writeStream($to, $buffer, $config);

        if (is_resource($buffer)) {
            fclose($buffer);
        }

        return $result;
    }

    /**
     * List with plugin adapter.
     *
     * @param array  $keys
     * @param string $directory
     * @param bool   $recursive
     *
     * @throws InvalidArgumentException
     * @throws FilesystemNotFoundException
     *
     * @return array
     */
    public function listWith(array $keys = array(), $directory = '', $recursive = false)
    {
        list($prefix, $directory) = $this->getPrefixAndPath($directory);
        $arguments = array($keys, $directory, $recursive);

        return $this->invokePluginOnFilesystem('listWith', $arguments, $prefix);
    }

    /**
     * Move a file.
     *
     * @param string $from
     * @param string $to
     * @param array  $config
     *
     * @throws InvalidArgumentException
     * @throws FilesystemNotFoundException
     *
     * @return bool
     */
    public function move($from, $to, array $config = array())
    {
        list($prefixFrom, $pathFrom) = $this->getPrefixAndPath($from);
        list($prefixTo, $pathTo) = $this->getPrefixAndPath($to);

        if ($prefixFrom === $prefixTo) {
            $filesystem = $this->getFilesystem($prefixFrom);
            $renamed = $filesystem->rename($pathFrom, $pathTo);

            if ($renamed && isset($config['visibility'])) {
                return $filesystem->setVisibility($pathTo, $config['visibility']);
            }

            return $renamed;
        }

        $copied = $this->copy($from, $to, $config);

        if ($copied) {
            return $this->delete($from);
        }

        return false;
    }

    /**
     * Invoke a plugin on a filesystem mounted on a given prefix.
     *
     * @param string $method
     * @param array  $arguments
     * @param string $prefix
     *
     * @throws FilesystemNotFoundException
     *
     * @return mixed
     */
    public function invokePluginOnFilesystem($method, $arguments, $prefix)
    {
        $filesystem = $this->getFilesystem($prefix);

        try {
            return $this->invokePlugin($method, $arguments, $filesystem);
        } catch (PluginNotFoundException $e) {
            // Let it pass, it's ok, don't panic.
        }

        $callback = array($filesystem, $method);

        return call_user_func_array($callback, $arguments);
    }

    /**
     * @param string $path
     *
     * @throws InvalidArgumentException
     *
     * @return string[] [:prefix, :path]
     */
    protected function getPrefixAndPath($path)
    {
        if (strpos($path, '://') < 1) {
            throw new InvalidArgumentException('No prefix detected in path: ' . $path);
        }

        return explode('://', $path, 2);
    }
}
