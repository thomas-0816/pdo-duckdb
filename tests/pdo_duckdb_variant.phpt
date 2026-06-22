--TEST--
PDO_duckdb: Test variant
--EXTENSIONS--
pdo_duckdb
--FILE--
<?php

$db = new PDO('duckdb::memory:');
$db->exec("create table t1 (v VARIANT)");
$statement = $db->prepare("INSERT INTO t1 VALUES (?), (?), (?), (?), (?), (?), (?)");
$statement->execute(['hello', 42, 42.21, null, [1, 2], ['foo', 'bar'], ['foo' => 'bar']]);
$statement = $db->query("SELECT * FROM t1");
var_dump($statement->fetchAll(PDO::FETCH_ASSOC));

?>
--EXPECTF--
array(7) {
  [0]=>
  array(1) {
    ["v"]=>
    string(5) "hello"
  }
  [1]=>
  array(1) {
    ["v"]=>
    int(42)
  }
  [2]=>
  array(1) {
    ["v"]=>
    float(42.21)
  }
  [3]=>
  array(1) {
    ["v"]=>
    NULL
  }
  [4]=>
  array(1) {
    ["v"]=>
    array(2) {
      [0]=>
      int(1)
      [1]=>
      int(2)
    }
  }
  [5]=>
  array(1) {
    ["v"]=>
    array(2) {
      [0]=>
      string(3) "foo"
      [1]=>
      string(3) "bar"
    }
  }
  [6]=>
  array(1) {
    ["v"]=>
    array(1) {
      ["foo"]=>
      string(3) "bar"
    }
  }
}
