<?php
/**
 * \Pyrus\ChannelRegistry\Xml
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * An implementation of a Pyrus channel registry within XML files.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\ChannelRegistry;
use \Pyrus\Main as Main;
class Xml extends Base
{
    protected $channelpath;
    /**
     * Initialize the registry
     *
     * @param string $path
     */
    function __construct($path, $readonly = false)
    {
        $this->readonly = $readonly;
        if (isset(Main::$options['packagingroot'])) {
            $path = Main::prepend(Main::$options['packagingroot'], $path);
        }

        $this->path = $path;
        $this->channelpath = $path . DIRECTORY_SEPARATOR . '.xmlregistry' . DIRECTORY_SEPARATOR . 'channels';
        if (1 === $this->exists('pear.php.net')) {
            $this->initialized = false;
        } else {
            $this->initialized = true;
        }
    }

    /**
     * Convert a name into a path-friendly name
     *
     * @param string $name
     */
    private function _mung($name)
    {
        return str_replace(array('/', '\\'), array('##', '###'), $name);
    }

    private function _unmung($name)
    {
        return str_replace(array('##', '###'), array('/', '\\'), $name);
    }

    /**
     * Get the filename to store a channel
     *
     * @param \Pyrus\ChannelInterface|string $channel Channel to save
     *
     * @return string
     */
    protected function getChannelFile($channel)
    {
        if ($channel instanceof \Pyrus\ChannelInterface) {
            $channel = $channel->name;
        }

        return $this->channelpath . DIRECTORY_SEPARATOR . 'channel-' .
            $this->_mung($channel) . '.xml';
    }

    /**
     * Get the filename for a channel alias.
     *
     * @param string $alias Alias to save
     *
     * @return string
     */
    protected function getAliasFile($alias)
    {
        return $this->channelpath . DIRECTORY_SEPARATOR . 'channelalias-' .
            $this->_mung($alias) . '.txt';
    }

    function channelFromAlias($alias)
    {
        if (!$this->initialized) {
            return parent::channelFromAlias($alias);
        }

        if (file_exists($this->getAliasFile($alias))) {
            return file_get_contents($this->getAliasFile($alias));
        }

        if (file_exists($this->getChannelFile($alias))) {
            return $alias;
        }

        throw new Exception('Unknown channel/alias: ' . $alias);
    }

    /**
     * Check if the channel has been discovered.
     *
     * @param string $channel Name of the channel
     * @param bool   $strict  Allow aliases or not
     *
     * @return bool
     */
    function exists($channel, $strict = true)
    {
        if (file_exists($this->getChannelFile($channel))) {
            return true;
        }

        if ($strict) {
            return parent::exists($channel, $strict);
        }

        if (file_exists($this->getAliasFile($channel))) {
            return true;
        }

        return parent::exists($channel, $strict);
    }

    function add(\Pyrus\ChannelInterface $channel, $update = false, $lastmodified = false)
    {
        if ($this->readonly) {
            throw new Exception('Cannot add channel, registry is read-only');
        }

        $this->lazyInit();

        $file = $this->getChannelFile($channel);
        if (@file_exists($file)) {
            throw new Exception('Error: channel ' .$channel->name . ' has already been discovered');
        }

        if (!@is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        file_put_contents($file, (string) $channel);
        file_put_contents($this->getAliasFile($channel->alias), $channel->name);
    }

    function update(\Pyrus\ChannelInterface $channel)
    {
        if ($this->readonly) {
            throw new Exception('Cannot update channel, registry is read-only');
        }

        $this->lazyInit();

        $file = $this->getChannelFile($channel);
        if (!@file_exists($file)) {
            throw new Exception('Error: channel ' . $channel->name . ' is unknown');
        }

        file_put_contents($file, (string) $channel);
        file_put_contents($this->getAliasFile($channel->alias), $channel->name);
    }

    function delete(\Pyrus\ChannelInterface $channel)
    {
        if ($this->readonly) {
            throw new Exception('Cannot delete channel, registry is read-only');
        }

        $name = $channel->name;
        if (in_array($name, $this->getDefaultChannels())) {
            throw new Exception('Cannot delete default channel ' . $channel->name);
        }

        $this->lazyInit();

        if (!$this->exists($name)) {
            return true;
        }

        if ($this->packageCount($name)) {
            throw new Exception('Cannot delete channel ' . $name . ', packages are installed');
        }

        @unlink($this->getChannelFile($channel));
        @unlink($this->getAliasFile($channel->alias));
    }

    function get($channel, $strict = true)
    {
        $exists = $this->exists($channel, $strict);
        if ($exists === false) {
            throw new Exception('Unknown channel: ' . $channel);
        }

        $channel = $this->channelFromAlias($channel);
        if (1 === $exists) {
            return $this->getDefaultChannel($channel);
        }

        $chan = new \Pyrus\ChannelFile($this->getChannelFile($channel));
        return new Channel($this, $chan->getArray());
    }

    /**
     * List all discovered channels
     *
     * @return array
     */
    function listChannels()
    {
        if (!$this->initialized) {
            return $this->getDefaultChannels();
        }

        $ret = array();
        $dir = new \DirectoryIterator($this->channelpath);
        foreach (new \RegexIterator($dir, '/channel-(.+?)\.xml/', \RegexIterator::GET_MATCH) as $file) {
            $ret[] = $this->get($this->_unmung($file[1]))->name;
        }
        unset($dir);

        return $ret;
    }
}
