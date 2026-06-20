--TEST--
PDO_duckdb: Test connection
--EXTENSIONS--
pdo_duckdb
--FILE--
<?php

$db = new PDO('duckdb::memory:');
$db->exec("CREATE TABLE t (i INTEGER, b BIGINT, d DECIMAL(10, 2), v VARCHAR)");
$stmt = $db->prepare("INSERT INTO t VALUES (?, ?, ?, ?)");
$stmt->execute([1, 9223372036854775807, 3.141511313212312312, 'hello']);
$stmt = $db->query("SELECT * FROM t", PDO::FETCH_ASSOC);
while ($row = $stmt->fetch()) { print_r($row); }

?>
--EXPECTF--
Array
(
    [i] => 1
    [b] => 9223372036854775807
    [d] => 3.14
    [v] => hello
)
