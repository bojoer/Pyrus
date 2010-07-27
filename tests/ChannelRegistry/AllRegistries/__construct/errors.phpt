--TEST--
\PEAR2\Pyrus\ChannelRegistry::__construct() errors test
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$killit = new Sqlite3(__DIR__ . '/testit/.pear2registry');
$query = '
  CREATE TABLE files (
   packages_name TEXT(80) NOT NULL,
   packages_channel TEXT(255) NOT NULL,
   configpath TEXT(255) NOT NULL,
   packagepath TEXT(255) NOT NULL,
   role TEXT(30) NOT NULL,
   relativepath TEXT(255) NOT NULL,
   origpath TEXT(255) NOT NULL,
   baseinstalldir TEXT(255),
   tasks TEXT NOT NULL,
   md5sum TEXT NOT NULL,
   PRIMARY KEY (packagepath, role),
   UNIQUE (packages_name, packages_channel, origpath)
  );';
$worked = $killit->exec($query);
// this will kill the sqlite3 registry
$killit->exec('BEGIN');
try {
    $c = new \PEAR2\Pyrus\ChannelRegistry(__DIR__ . '/testit', array('Sqlite3', 'Fubar'));
    throw new Exception('worked and should not');
} catch (\PEAR2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Unable to initialize registry for path "' . __DIR__ .
                        DIRECTORY_SEPARATOR . 'testit"', $e->getMessage(), 'message');
    $causes = array();
    $e->getCauseMessage($causes);
    $test->assertEquals('Database initialization failed',
                        $causes[1]['message'], 'message 1');
    $test->assertEquals('Unknown channel registry type: PEAR2\Pyrus\ChannelRegistry\Fubar', $causes[2]['message'], 'message 2');
}

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===
