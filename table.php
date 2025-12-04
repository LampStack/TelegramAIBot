<?php

include ('./config.php');

$MySQLi = new mysqli('localhost',$DB['username'],$DB['password'],$DB['dbname']);
$MySQLi->query("SET NAMES 'utf8'");
$MySQLi->set_charset('utf8mb4');
if ($MySQLi->connect_error){
echo 'Connection failed: ' . $MySQLi->connect_error;
$MySQLi->close();
die;
}


//          user            //
$query = "CREATE TABLE `user` (
`id` BIGINT(20) PRIMARY KEY,
`step` TEXT DEFAULT NULL,
`model` VARCHAR(128) DEFAULT 'openai/gpt-4o',
`join_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) default charset = utf8mb4";
if($MySQLi->query($query) === false)
echo $MySQLi->error.'<br>';


$MySQLi->close();
echo 'database created succesfully';
exit();