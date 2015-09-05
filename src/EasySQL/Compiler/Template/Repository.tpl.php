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
        @if ($method->mapAsObject()) 
            $stmt->setFetchMode(PDO::FETCH_CLASS, {{ @$method->mapAsObject() }});
        @end
        @if ($method->isInsert())
            return $this->dbh->lastInsertId();
        @elif ($method->changeSchema()) 
            return true;
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
