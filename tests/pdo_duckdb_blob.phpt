--TEST--
PDO_duckdb: Test blob
--EXTENSIONS--
pdo_duckdb
--FILE--
<?php

$db = new PDO('duckdb::memory:');
$statement = $db->query('SELECT \'\xAA\'::BLOB as a');
echo bin2hex($statement->fetchAll(PDO::FETCH_ASSOC)[0]['a']), PHP_EOL;

$statement = $db->query('SELECT \'\xAA\xAB\xAC\'::BLOB as a');
echo bin2hex($statement->fetchAll(PDO::FETCH_ASSOC)[0]['a']), PHP_EOL;

?>
--EXPECTF--
aa
aaabac
