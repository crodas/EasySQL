<?php

namespace EasyRepository\t{{$mt=uniqid(true)}};

use PDO;
use EasySQL\Cursor;

@foreach ($files as $query)
class {{$query->getName()}}Repository
{
    protected $dbh;

    public function __construct(PDO $pdo)
    {
        $this->dbh = $pdo;
    }

    @foreach ($query->getMethods() as $name => $method)
    public function {{$name}}({{$method->getFunctionSignature()}})
    {
        $stmt = $this->dbh->prepare({{@$method->getSQL()}});
        @foreach ($method->getPHPCode() as $line)
            {{$line}}
        @end
        $result = $stmt->execute({{$method->getCompact()}});
        @if ($method->isVoid())
            // void 
        @elif (!$method->isPluck() && $method->mapAsObject()) 
            $stmt->setFetchMode(PDO::FETCH_CLASS, {{ @$method->mapAsObject() }}, array($this->dbh, {{ @$method->getTables() }}));
        @elif ($method->isSelect())
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'EasySQL\Result', array($this->dbh, {{ @$method->getTables() }}));
        @end
        @if ($method->isInsert())
            return $this->dbh->lastInsertId();
        @elif ($method->isVoid() || $method->changeSchema() || $method->isUpdate() || $method->isDelete()) 
            return true;
        @elif ($method->isPluck())
            $rows = array();
            $stmt->setFetchMode(PDO::FETCH_NUM);
            foreach ($stmt as $row) {
                @if (count($method->getQuery()->getColumns()) == 1)
                    $rows[] = $row[0];
                @else
                    $rows[] = $row;
                @end
            }
            return $rows;
        @elif ($method->singleResult()) 
            return $stmt->fetch();
        @else 
            return $stmt;
        @end
    }

    @end
}
@end

return function(PDO $pdo) {
    return [
        @foreach ($files as $query)
        {{@strtolower($query->getName())}} => new {{$query->getName()}}Repository($pdo),
        @end
    ];
};
