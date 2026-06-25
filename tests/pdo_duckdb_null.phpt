--TEST--
PDO_duckdb: Test list
--EXTENSIONS--
pdo_duckdb
--FILE--
<?php

$db = new PDO('duckdb::memory:');

$statement = $db->query("SELECT
null::boolean,
null::tinyint,
null::smallint,
null::integer,
null::bigint,
null::utinyint,
null::usmallint,
null::uinteger,
null::ubigint,
null::float,
null::double,
null::timestamp,
null::date,
null::time,
null::interval,
null::hugeint,
null::uhugeint,
null::varchar,
null::blob,
null::decimal,
null::timestamp_s,
null::timestamp_ms,
null::timestamp_ns,
null::enum('a'),
null::struct(duck integer),
null::integer[],
null::integer[1],
map_from_entries(null),
null::union(str varchar),
null::uuid,
null::bit,
null::timetz,
null::timestamptz,
null::time_ns,
null::geometry,
null::variant,
null::bignum
");
var_dump($statement->fetchAll(PDO::FETCH_ASSOC));

?>
--EXPECTF--
array(1) {
  [0]=>
  array(37) {
    ["CAST(NULL AS BOOLEAN)"]=>
    NULL
    ["CAST(NULL AS "TINYINT")"]=>
    NULL
    ["CAST(NULL AS SMALLINT)"]=>
    NULL
    ["CAST(NULL AS INTEGER)"]=>
    NULL
    ["CAST(NULL AS BIGINT)"]=>
    NULL
    ["CAST(NULL AS "UTINYINT")"]=>
    NULL
    ["CAST(NULL AS "USMALLINT")"]=>
    NULL
    ["CAST(NULL AS "UINTEGER")"]=>
    NULL
    ["CAST(NULL AS "UBIGINT")"]=>
    NULL
    ["CAST(NULL AS FLOAT)"]=>
    NULL
    ["CAST(NULL AS "DOUBLE")"]=>
    NULL
    ["CAST(NULL AS TIMESTAMP)"]=>
    NULL
    ["CAST(NULL AS "DATE")"]=>
    NULL
    ["CAST(NULL AS TIME)"]=>
    NULL
    ["CAST(NULL AS INTERVAL)"]=>
    NULL
    ["CAST(NULL AS "HUGEINT")"]=>
    NULL
    ["CAST(NULL AS "UHUGEINT")"]=>
    NULL
    ["CAST(NULL AS VARCHAR)"]=>
    NULL
    ["CAST(NULL AS "BLOB")"]=>
    NULL
    ["CAST(NULL AS DECIMAL)"]=>
    NULL
    ["CAST(NULL AS "TIMESTAMP_S")"]=>
    NULL
    ["CAST(NULL AS "TIMESTAMP_MS")"]=>
    NULL
    ["CAST(NULL AS "TIMESTAMP_NS")"]=>
    NULL
    ["CAST(NULL AS "ENUM"('a'))"]=>
    NULL
    ["CAST(NULL AS STRUCT(duck INTEGER))"]=>
    NULL
    ["CAST(NULL AS INTEGER[])"]=>
    NULL
    ["CAST(NULL AS INTEGER[1])"]=>
    NULL
    ["map_from_entries(NULL)"]=>
    NULL
    ["CAST(NULL AS UNION(str VARCHAR))"]=>
    NULL
    ["CAST(NULL AS "UUID")"]=>
    NULL
    ["CAST(NULL AS BIT(1))"]=>
    NULL
    ["CAST(NULL AS "TIME WITH TIME ZONE")"]=>
    NULL
    ["CAST(NULL AS "TIMESTAMP WITH TIME ZONE")"]=>
    NULL
    ["CAST(NULL AS "TIME_NS")"]=>
    NULL
    ["CAST(NULL AS "GEOMETRY")"]=>
    NULL
    ["CAST(NULL AS "VARIANT")"]=>
    NULL
    ["CAST(NULL AS "BIGNUM")"]=>
    NULL
  }
}
