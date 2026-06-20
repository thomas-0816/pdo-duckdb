<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = new PDO('duckdb::memory:');
$db->exec("CREATE TABLE t (i INTEGER, b BIGINT, d DECIMAL(10, 2), v VARCHAR)");
$stmt = $db->prepare("INSERT INTO t VALUES (?, ?, ?, ?)");
$stmt->execute([1, 9223372036854775807, 3.141511313212312312, 'hello']);
$stmt = $db->query("SELECT * FROM t", PDO::FETCH_ASSOC);
while ($row = $stmt->fetch()) { print_r($row); }

if (file_exists('/tmp/pdo_duckdb_test.db')) {
    unlink('/tmp/pdo_duckdb_test.db');
}

$db = new PDO('duckdb:/tmp/pdo_duckdb_test.db');
$db->exec("CREATE TABLE t (i INTEGER, v VARCHAR)");
$stmt = $db->prepare("INSERT INTO t VALUES (?, ?)");
$stmt->execute([1, 'hello']);
$stmt = $db->query("SELECT * FROM t");
while ($row = $stmt->fetch()) { print_r($row); }

$db = new PDO('duckdb::memory:');
$statement = $db->query("SELECT {'birds': ['duck', 'goose', 'heron'], 'aliens': NULL, 'amphibians': ['frog', 'toad']} as struct");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT {'test': [MAP([1, 5], [42.1, 45]), MAP([1, 5], [42.1, 45])]} as struct");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT [union_value(num := 2), union_value(str := 'ABC')::UNION(str VARCHAR, num INTEGER)] as union");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT array_value(1, 2, 3) as aa");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT array_value(1, 2, 3)[2] as aa");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT [3, 2, 1]::INTEGER[3] as aa");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT array_value(array_value(1, 2), array_value(3, 4), array_value(5, 6)) as aa");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT array_value({'a': 1, 'b': 2}, {'a': 3, 'b': 4}) as aa");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT null, true, false, NULL::BOOLEAN, DATE '1992-09-20'");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT uuidv4(), uuidv7(), CAST(3.1 AS INTEGER)");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT '-infinity'::DATE AS negative, 'epoch'::DATE AS epoch, 'infinity'::DATE AS positive");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT 0/0, 0//0"); // TODO fix 0/0, inf, nan, -inf
var_export($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT 0/0, 0//0");
var_export($statement->fetchAll(PDO::FETCH_ASSOC));

$db->exec("CREATE TABLE array_table (id INTEGER, arr INTEGER[3])");
$db->exec("INSERT INTO array_table VALUES (10, [1, 2, 3]), (20, [4, 5, 6])");
$statement = $db->query("SELECT id, arr[1] AS element FROM array_table");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));
$statement = $db->query("SELECT id, list_extract(arr, 1) AS element FROM array_table");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));
$statement = $db->query("SELECT id, array_extract(arr, 1) AS element FROM array_table");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));
$statement = $db->query("SELECT id, arr[1:2] AS elements FROM array_table");
print_r($statement->fetchAll(PDO::FETCH_ASSOC));

$db->exec("CREATE TABLE x (i INTEGER, v FLOAT[3])");
$db->exec("CREATE TABLE y (i INTEGER, v FLOAT[3])");
$db->exec("INSERT INTO x VALUES (1, array_value(1.0::FLOAT, 2.0::FLOAT, 3.0::FLOAT))");
$db->exec("INSERT INTO y VALUES (1, array_value(2.1::FLOAT, 3.2::FLOAT, 4.3::FLOAT))"); // TODO fix?

$statement = $db->query("SELECT * FROM x");
var_export($statement->fetchAll(PDO::FETCH_ASSOC));
$statement = $db->query("SELECT * FROM y");
var_export($statement->fetchAll(PDO::FETCH_ASSOC));

$statement = $db->query("SELECT CAST(999 AS TINYINT)");
var_export($statement);
// var_export($statement->fetchAll(PDO::FETCH_ASSOC));
