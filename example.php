<?php

require __DIR__ . "/vendor/autoload.php";

class UserObject {
}

$pdo = new PDO("sqlite:foo.db");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$easysql = new EasySQL\EasySQL("demo", $pdo);
$user = $easysql->getRepository('user');
$id   = $user->InsertData('cesar', 'roddas');
$res  = $user->Foobar($id);
var_dump($user, $res);exit;
