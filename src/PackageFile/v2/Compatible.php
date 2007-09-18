<?php
/**
 * Manage compatible packages with this one
 * 
 * To be used like:
 * <code>
 * // add a new compatible declaration or replace an existing one
 * $pf->compatible['pear.php.net/Archive_Tar']
 *         ->min('1.2')
 *         ->max('1.3.0')
 *         ->exclude('1.2.1', '1.2.2');
 * // remove a compatibility declaration
 * unset($pf->compatible['pear.php.net/Archive_Tar']);
 * // test for existence of compatible declaration
 * isset($pf->compatible['pear.php.net/Archive_Tar']);
 * // display info:
 * echo $pf->compatible['pear.php.net/Archive_Tar']->min;
 * </code>
 */
class PEAR2_Pyrus_PackageFile_v2_Compatible implements ArrayAccess
{
    private $_packageInfo;
    private $_package = false;
    private $_info = array('name' => '', 'channel' => '', 'min' => '', 'max' => '');
    function __construct(array &$parent, $package = false)
    {
        $this->_packageInfo = &$parent;
        if ($package) {
            $channel = explode('/', $package);
            $package = array_pop($channel);
            $channel = implode('/', $channel);
            $this->_info['name'] = $package;
            $this->_info['channel'] = $channel;
            $this->_package = true;
        }
    }

    function __get($var)
    {
        if (!$this->_package) {
            throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                'Cannnot access developer info for unknown developer');
        }
        if (isset($this->_info[$var])) {
            return $this->_info[$var];
        }
    }

    function __call($var, $args)
    {
        if (!$this->_package) {
            throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                'Cannnot access developer info for unknown developer');
        }
        if ($var == 'min') {
            if (count($args) != 1 || !is_string($args[0])) {
                throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                    'Invalid value for min');
            }
            $this->_info['min'] = $args[0];
        } elseif ($var == 'max') {
            if (count($args) != 1 || !is_string($args[0])) {
                throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                    'Invalid value for max');
            }
            $this->_info['max'] = $args[0];
        } elseif ($var == 'exclude') {
            foreach ($args as $arg) {
                if (!is_string($arg)) {
                    throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                        'Invalid value for exclude');
                }
            }
            $this->_info['exclude'] = (count($args) == 1) ? $args[0] : $args[1];
        } else {
            throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                        'Unknown value to set: ' . $var);
        }
        return $this;
    }

    function offsetGet($var)
    {
        return new PEAR2_Pyrus_PackageFile_v2_Compatible($this->_packageInfo, $var);
    }

    function offsetSet($var, $value)
    {
        $this->_package = $var;
        $channel = explode('/', $var);
        $this->_info['name'] = array_pop($channel);
        $this->_info['channel'] = implode('/', $channel);
        if ($var instanceof PEAR2_Pyrus_PackageFile_v2_Compatible) {
            $this->_info['min'] = $value->min;
            $this->_info['max'] = $value->max;
            $exclude = $value->exclude;
            if ($exclude) {
                $this->_info['exclude'] = $exclude;
            }
        }
        if (is_array($value) || $value instanceof ArrayObject) {
            if (!isset($value['min']) || !isset($value['max'])) {
                throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                    'Invalid array used to set ' . $var . ' compatibility');
            }
            $this->_info['min'] = $value['min'];
            $this->_info['max'] = $value['max'];
            if (isset($value['exclude'])) {
                $this->_info['exclude'] = $value['exclude'];
            }
        }
        $this->_save();
    }

    /**
     * Remove a compatible package from package.xml (by channel/package)
     * @param string $var
     */
    function offsetUnset($var)
    {
        if (!isset($this->_packageInfo['compatible'])) {
            return;
        }
        $channel = explode('/', $var);
        $package = array_pop($channel);
        $channel = implode('/', $channel);
        if (isset($this->_packageInfo['compatible'][0])) {
            foreach ($this->_packageInfo['compatible'] as $i => $stuff) {
                if ($stuff['name'] == $package && $stuff['channel'] == $channel) {
                    unset($this->_packageInfo['compatible'][$i]);
                    if (!count($this->_packageInfo['compatible'])) {
                        unset($this->_packageInfo['compatible']);
                    }
                    return;
                }
            }
        } else {
            if ($this->_packageInfo['compatible']['name'] == $package &&
                  $this->_packageInfo['compatible']['channel'] == $channel) {
                unset($this->_packageInfo['compatible']);
            }
        }
    }

    /**
     * Test whether compatible package exists in package.xml (by channel/package)
     * @param string $var
     * @return bool
     */
    function offsetExists($var)
    {
        if (!isset($this->_packageInfo['compatible'])) return false;
        $channel = explode('/', $var);
        $package = array_pop($channel);
        $channel = implode('/', $channel);
        $stuff = $this->_packageInfo['compatible'];
        if (!isset($stuff[0])) {
            $stuff = array($stuff);
        }
        foreach ($stuff as $compat) {
            if ($compat['name'] == $package && $compat['channel'] == $channel) {
                return true;
            }
        }
        return false;
    }

    /**
     * Save changes
     */
    private function _save()
    {
        if (!isset($this->_packageInfo['compatible'])) {
            $this->_packageInfo['compatible'] = $this->_info;
            return;
        }
        foreach ($this->_packageInfo['compatible'] as $i => $compat) {
            if ($compat['name'] == $package && $compat['channel'] == $channel) {
                // replace declaration
                $this->_packageInfo['compatible'][$i] = $this->_info;
                return;
            }
        }
        $this->_packageInfo['compatible'][] = $this->_info;
    }
}