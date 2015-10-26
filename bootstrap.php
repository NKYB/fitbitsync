<?php
define('FITBIT_KEY','c8a49c194xxxxe165a123de40c8xxxx');
define('FITBIT_SECRET','8003d21xxxxa4e3a448d39401fbbxxxx');

$sql_details = array(
    "type" => "Mysql",          // Database type: "Mysql", "Postgres", "Sqlite" or "Sqlserver"
    "user" => "root",           // User name
    "pass" => "",               // Password
    "host" => "localhost",      // Database server
    "port" => "3306",           // Database port (can be left empty for default)
    "db"   => "fitbitsync",     // Database name
    "dsn"  => "charset=utf8"    // PHP DSN extra information. Set as `charset=utf8` if you are using MySQL
);

$db_conn = mysql_connect($sql_details['host'], $sql_details['user'], $sql_details['pass']);
mysql_query("SET character_set_results=utf8", $db_conn);
mb_language('uni'); 
mb_internal_encoding('UTF-8');
mysql_select_db( $sql_details['db'], $db_conn);
mysql_query("set names 'utf8'",$db_conn);

