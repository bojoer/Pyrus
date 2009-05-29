<?php
/**
 * Base class for all installation roles.
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * Base class for all installation roles.
 *
 * This class allows extensibility of file roles.  Packages with complex
 * customization can now provide custom file roles along with the possibility of
 * adding configuration values to match.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Installer_Role_Common
{
    /**
     * @var PEAR_Config
     * @access protected
     */
    protected $config;

    /**
     * @param PEAR2_Pyrus_Config
     */
    function __construct(PEAR2_Pyrus_Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve configuration information about a file role from its XML info
     *
     * @param string $role Role Classname, as in "PEAR2_Pyrus_Installer_Role_Data"
     * @return array
     */
    static function getInfo($role)
    {
        return PEAR2_Pyrus_Installer_Role::getInfo($role);
    }

    /**
     * Retrieve the location a packaged file should be placed in a package
     *
     * @param PEAR2_Pyrus_Package $pkg
     * @param array $atts
     * @return string
     */
    function getPackagingLocation(PEAR2_Pyrus_IPackageFile $pkg, $atts)
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role::getInfo('PEAR2_Pyrus_Installer_Role_' .
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        $role = str_replace('pear2_pyrus_installer_role_', '',
                strtolower(get_class($this)));

        $file = $atts['name'];
        // strip role from file path
        // so src/Path/To/File.php becomes Path/To/File.php,
        // data/package.xsd becomes package.xsd
        $newpath = $file;
        if ($role === 'php') {
            if (strpos($newpath, 'src') === 0) {
                $newpath = substr($newpath, 4);
            }
        } elseif (strpos($newpath, $role) === 0) {
            $newpath = substr($newpath, strlen($role) + 1);
        }
        if ($newpath === false) {
            $newpath = $file;
        }

        if ($newpath) {
            if ($newpath[0] == '/') {
                $newpath = substr($newpath, 1);
            }
            $file = $newpath;
        }

        if ($roleInfo['honorsbaseinstall']) {
            $dest_dir = $role;
            if (array_key_exists('baseinstalldir', $atts)) {
                if ($atts['baseinstalldir'] != '/') {
                    $dest_dir .= '/' . $atts['baseinstalldir'];
                }

                if (strlen($atts['baseinstalldir'])) {
                    $dest_dir .= '/';
                }
            } else {
                $dest_dir .= '/';
            }
        } elseif ($roleInfo['unusualbaseinstall']) {
            $dest_dir = $role . '/' . $pkg->channel . '/' . $pkg->name . '/';
            if (array_key_exists('baseinstalldir', $atts)) {
                if (strlen($atts['baseinstalldir']) && $atts['baseinstalldir'] != '/') {
                    $dest_dir .= $atts['baseinstalldir'];
                    if (strlen($atts['baseinstalldir'])) {
                        $dest_dir .= '/';
                    }
                }
            } else {
                if (dirname($file) != '.') {
                    $dest_dir .= dirname($file) . '/';
                }
            }
        } else {
            $dest_dir = $role . '/' . $pkg->channel . '/' . $pkg->name . '/';
        }

        return $dest_dir . $file;
    }

    function getRelativeLocation(PEAR2_Pyrus_IPackageFile $pkg, PEAR2_Pyrus_PackageFile_v2Iterator_FileTag $file,
                                 $retDir = false)
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role::getInfo('PEAR2_Pyrus_Installer_Role_' .
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        if (!$roleInfo['locationconfig']) {
            return false;
        }

        if ($roleInfo['honorsbaseinstall']) {
            $dest_dir = $save_destdir = '';
            if ($file->baseinstalldir) {
                $dest_dir .= $file->baseinstalldir;
            }
        } elseif ($roleInfo['unusualbaseinstall']) {
        	if (!$pkg->isNewPackage()) {
        		// Place files using the old doc dir structure
        		$dest_dir = $save_destdir = $pkg->name;
        	} else {
	            $dest_dir = $save_destdir =
	                $pkg->channel . DIRECTORY_SEPARATOR . $pkg->name;
        	}
            if ($file->baseinstalldir) {
                $dest_dir .= DIRECTORY_SEPARATOR . $file->baseinstalldir;
            }
        } else {
            $dest_dir = $save_destdir =
                $pkg->channel . DIRECTORY_SEPARATOR . $pkg->name;
        }

        if (dirname($file->name) != '.' && empty($file['install-as'])) {
            $newpath = dirname($file->name);
            if ($pkg->isNewPackage()) {
                // strip role from file path
                // so php/Path/To/File.php becomes Path/To/File.php,
                // data/package.xsd becomes package.xsd
                $r = strtolower(str_replace('PEAR2_Pyrus_Installer_Role_', '',
                      get_class($this)));
                if ($r === 'php') {
                    if (strpos($newpath, 'src') === 0) {
                        $newpath = substr($newpath, 4);
                        if ($newpath === false) {
                            $newpath = '';
                        }
                    }
                }
                if (strpos($newpath, $r) === 0) {
                    $newpath = substr($newpath, strlen($r) + 1);
                    if ($newpath === false) {
                        $newpath = '';
                    }
                }
            }
            if ($dest_dir && $newpath) {
                $dest_dir .= DIRECTORY_SEPARATOR;
            }
            $dest_dir .= $newpath;
        }

        if ($dest_dir) {
            $dest_dir .= DIRECTORY_SEPARATOR;
        }
        $dest_file = $dest_dir;
        if (empty($file['install-as'])) {
            $dest_file .= basename($file->name);
        } else {
            $dest_file .= $file['install-as'];
        }
        if ($retDir) {
            // Clean up the DIRECTORY_SEPARATOR mess
            $ds2 = DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;

            list($dest_dir, $dest_file) = preg_replace(array('!\\\\+!', '!/!', "!$ds2+!"),
                                                        array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR,
                                                              DIRECTORY_SEPARATOR),
                                                        array($dest_dir, $dest_file));
            return array($dest_dir, $dest_file);
        }
        return $dest_file;
    }

    /**
     * Get the name of the configuration variable that specifies the location of this file
     * @return string|false
     */
    function getLocationConfig()
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role::getInfo('PEAR2_Pyrus_Installer_Role_' .
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        return $roleInfo['locationconfig'];
    }

    /**
     * Do any unusual setup here
     * @param PEAR_Installer
     * @param PEAR_PackageFile_v2
     * @param array file attributes
     * @param string file name
     */
    function setup($installer, $pkg, $atts, $file)
    {
    }

    function isExecutable()
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role::getInfo('PEAR2_Pyrus_Installer_Role_' .
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        return $roleInfo['executable'];
    }

    function isInstallable()
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role_Common::getInfo('PEAR2_Pyrus_Installer_Role_' .
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        return $roleInfo['installable'];
    }

    function isExtension()
    {
        $roleInfo = PEAR2_Pyrus_Installer_Role::getInfo('PEAR2_Pyrus_Installer_Role_' .
            ucfirst(str_replace('pear2_pyrus_installer_role_', '', strtolower(get_class($this)))));
        return $roleInfo['phpextension'];
    }
}