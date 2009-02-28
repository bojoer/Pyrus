--TEST--
PackageFile v2: test package.xml dependsOn(), uri-based packages
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$pf1 = new PEAR2_Pyrus_PackageFile_v2;
$pf1->name = 'P1';
$pf1->channel = 'pear2.php.net';

$pf2 = new PEAR2_Pyrus_PackageFile_v2;
$pf2->name = 'P2';
$pf2->uri = 'http://pear.example.com';

$test->assertEquals(false, $pf1->dependsOn('P2', '__uri', 'http://pear.example.com'), 'no relation');
$pf1->dependencies['group']->group1->package['__uri/P2']->uri('http://pear.example.com');
$test->assertEquals(true, $pf1->dependsOn('P2', '__uri', 'http://pear.example.com'), 'group relation');

unset($pf1->dependencies['group']);

$test->assertEquals(false, $pf1->dependsOn('P2', '__uri', 'http://pear.example.com'), 'no relation');
$pf1->dependencies['optional']->package['__uri/P2']->uri('http://pear.example.com');
$test->assertEquals(true, $pf1->dependsOn('P2', '__uri', 'http://pear.example.com'), 'optional relation');

unset($pf1->dependencies['optional']);

$test->assertEquals(false, $pf1->dependsOn('P2', '__uri', 'http://pear.example.com'), 'no relation');
$pf1->dependencies['required']->package['__uri/P2']->uri('http://pear.example.com');
$test->assertEquals(true, $pf1->dependsOn('P2', '__uri', 'http://pear.example.com'), 'required relation');

?>
===DONE===
--EXPECT--
===DONE===