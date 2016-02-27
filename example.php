<?php

require __DIR__ . "/vendor/autoload.php";

class UserObject
{
}

$easysql = new EasySQL\EasySQL("demo", "sqlite3:///foo.db");
$easysql = new EasySQL\EasySQL("demo", "mysql://root@localhost/demo");
$user = $easysql->getRepository('user');
$table = uniqid(true);
try {
    $user->createTable($table);
} catch (Exception $e){}

$id   = $user->InsertData($table, 'cesar', 'roddas');
$res  = $user->Foobar($table, $id);
var_dump($table, $user, $res);exit;
