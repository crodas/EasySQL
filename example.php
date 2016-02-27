<?php

require __DIR__ . "/vendor/autoload.php";

class UserObject
{
}

$easysql = new EasySQL\EasySQL("demo", "sqlite3:///foo.db");
$easysql = new EasySQL\EasySQL("demo", "mysql://root@localhost/demo");
$user = $easysql->getRepository('user');
try {
    $user->createTable();
} catch (Exception $e){}

$id   = $user->InsertData('cesar', 'roddas');
$res  = $user->Foobar($id);
var_dump($user, $res);exit;
