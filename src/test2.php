<?php
function __autoload($class)
{
    if (substr($class, 0, 4) != 'PEAR') return false;
    $path = explode('_', substr($class, 11)); // strip PEAR2_Pyrus for CVS
    $path = dirname(__FILE__) . implode('\\', $path) . '.php';
    include $path;
}
include 'C:/development/PEAR2/HTTP/Request/src/HTTP/Request/allfiles.php';
include $a = 'C:/development/PEAR2/Exception/Exception.php';
include $b = 'C:/development/PEAR2/MultiErrors/MultiErrors.php';
include 'C:/development/PEAR2/MultiErrors/MultiErrors/Exception.php';
include 'C:/development/PEAR2/Pyrus_Developer/Creator/Zip.php';
include 'C:/development/PEAR2/Pyrus_Developer/Creator/Tar.php';
include 'C:/development/PEAR2/Pyrus_Developer/Creator/Xml.php';
include 'C:/development/PEAR2/Pyrus_Developer/Creator/Exception.php';
//$a = new PEAR2_Pyrus_Package_Creator(array(
//        new PEAR2_Pyrus_Developer_Creator_Zip('C:/development/PEAR2/blah.zip'),
//        new PEAR2_Pyrus_Developer_Creator_Tar('C:/development/PEAR2/blah.tgz'),
//        new PEAR2_Pyrus_Developer_Creator_Xml('C:/development/PEAR2/blah.xml'),
//    ), $a, 'C:/development/PEAR2/Autoload/Autoload.php', $b);
//$b = new PEAR2_Pyrus_Package('C:/development/pear-core/PEAR-1.6.0.tgz');
//$a->render($b);
//exit;
//$pf = new PEAR2_Pyrus_PackageFile_v2;
//$pf->name = 'test';
//$pf->channel = 'pear.php.net';
//$pf->summary = 'test';
//$pf->description = 'testing';
//$pf->maintainer['cellog']->name('Greg Beaver')->role('lead')->email('cellog@php.net')
//    ->active('yes');
//$a = new DateTime();
//$pf->date = $a->format('Y-m-d');
//$pf->version['release'] = '1.0.0';
//$pf->version['api'] = '1.0.0';
//$pf->stability['release'] = 'stable';
//$pf->stability['api'] = 'stable';
//$pf->license = 'PHP License';
//$pf->notes = 'test';
//$pf->dependencies->required->php = array('min' => '5.2.0');
//$pf->dependencies->required->pearinstaller = array('min' => '5.2.0', 'exclude' => '1.2.3');
//$pf->files['test/me.php'] = array('attribs' => array('role' => 'php'));
//$pf = new PEAR2_Pyrus_package(dirname(__FILE__) . '/test.xml');
//foreach ($pf->packagingcontents as $name => $file) {
//    var_dump($name, $file);
//}
//exit;
define('OS_WINDOWS', true);
define('OS_UNIX', false);
$g = new PEAR2_Pyrus_Config('C:/development/pear-core/testpear');
//$g = new PEAR2_Pyrus_Config('/home/cellog/testpear');
$g->saveConfig();
$a = new PEAR2_Pyrus_Package('http://pear.php.net/get/PEAR-1.6.2.tgz');
//$a = new PEAR2_Pyrus_Package('C:/development/pear-core/PEAR-1.6.2.tgz');
$b = new PEAR2_Pyrus_Installer;
$b->install($a);