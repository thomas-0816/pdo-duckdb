--TEST--
PDO_duckdb: Test connection
--EXTENSIONS--
pdo_duckdb
--FILE--
<?php

$db = new PDO('duckdb::memory:');
$db->exec("CREATE TABLE t (i INTEGER, b BIGINT, d DECIMAL(10, 2), v VARCHAR)");
$statement = $db->prepare("INSERT INTO t VALUES (?, ?, ?, ?)");
$statement->execute([1, 9223372036854775807, 3.141511313212312312, 'hello']);

var_dump($db->lastInsertId());

$statement = $db->query("SELECT * FROM t", PDO::FETCH_ASSOC);
while ($row = $statement->fetch()) { var_dump($row); }

$statement = $db->query("SELECT * FROM t", PDO::FETCH_ASSOC);
foreach ($statement->getIterator() as $row) {
    var_dump($row);
}

$statement = $db->query('SELECT * FROM t');
var_dump($statement->getColumnMeta(0));
var_dump($statement->getColumnMeta(1));
var_dump($statement->getColumnMeta(2));
var_dump($statement->getColumnMeta(3));
var_dump($statement->getColumnMeta(4));

$db = new PDO('duckdb::memory:');
$db->exec("CREATE TABLE t (n INTEGER NULL, i INTEGER NULL, b BIGINT NULL, d DECIMAL(10, 2) NULL, v VARCHAR NULL)");
$statement = $db->prepare("INSERT INTO t VALUES (?, ?, ?, ?, ?)");
$statement->execute([1, 2, 9223372036854775807, 3.141511313212312312, 'hello']);
$statement = $db->prepare("UPDATE t SET n = ?, i = ?, b = ?, d = ?, v = ?");
$statement->execute([2, 3, 4, 5.67, 'world']);
var_dump($db->query('SELECT * FROM t')->fetchAll(PDO::FETCH_ASSOC));


$db = new PDO('duckdb::memory:');
$db->exec("CREATE TABLE t (n INTEGER NULL, i INTEGER NULL, b BIGINT NULL, d DECIMAL(10, 2) NULL, v VARCHAR NULL)");
$statement = $db->prepare('INSERT INTO t VALUES ($2, $1, $3, $5, $4)');
$statement->execute([2, 1, 9223372036854775807, 'hello', 3.141511313212312312]);
$statement->execute([null, null, null, null, null]);
var_dump($db->query('SELECT * FROM t')->fetchAll(PDO::FETCH_ASSOC));

$db = new PDO('duckdb::memory:');
$db->exec("CREATE TABLE t (n INTEGER NULL, i INTEGER NULL, b BIGINT NULL, d DECIMAL(10, 2) NULL, v VARCHAR NULL)");
$statement = $db->prepare('INSERT INTO t VALUES ($aa, $bb, $cc, $dd, $ee)');
$statement->execute(['bb' => 2, 'aa' => 1, 'cc' => 9223372036854775807, 'ee' => 'hello', 'dd' => 3.141511313212312312]);
var_dump($db->query('SELECT * FROM t')->fetchAll(PDO::FETCH_ASSOC));

$db = new PDO('duckdb::memory:');
$db->exec("CREATE TABLE t (n INTEGER NULL, i INTEGER NULL, b BIGINT NULL, d DECIMAL(10, 2) NULL, v VARCHAR NULL)");
$statement = $db->prepare('INSERT INTO t VALUES ($aa, $bb, $cc, $dd, $ee)');
try {
    $statement->execute(['bb' => 2, 'aa' => 1]);
} catch (Exception $e) {
    echo "Caught: " . $e->getMessage() . "\n";
}

$db = new PDO('duckdb::memory:');
$db->exec("CREATE TABLE t (n INTEGER NULL, i INTEGER NULL, b BIGINT NULL, d DECIMAL(10, 2) NULL, v VARCHAR NULL)");
$statement = $db->prepare('INSERT INTO t VALUES ($aa, $bb, $cc, $dd, $ee)');
$statement->bindValue('aa', null, PDO::PARAM_NULL);
$statement->bindValue('bb', 200, PDO::PARAM_INT);
$statement->bindValue('cc', 300, PDO::PARAM_INT);
$statement->bindValue('dd', 42.21, PDO::PARAM_STR);
$statement->bindValue('ee', 'test', PDO::PARAM_STR);
$statement->execute();
$statement = $db->prepare('INSERT INTO t VALUES ($aa, $bb, $cc, $dd, $ee)');
$statement->bindValue('aa', null);
$statement->bindValue('bb', 200);
$statement->bindValue('cc', 300);
$statement->bindValue('dd', 42.21);
$statement->bindValue('ee', 'test');
$statement->execute();
$statement = $db->prepare('INSERT INTO t VALUES (?, ?, ?, ?, ?)');
$statement->bindValue('aa', null);
$statement->bindValue('bb', 200);
$statement->bindValue('cc', 300);
$statement->bindValue('dd', 42.21);
$statement->bindValue('ee', 'test');
$statement->execute();
var_dump($db->query('SELECT * FROM t')->fetchAll(PDO::FETCH_ASSOC));

?>
--EXPECTF--
