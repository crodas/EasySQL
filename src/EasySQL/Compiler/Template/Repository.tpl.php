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
        $stmt->execute({{$method->getCompact()}});
        @if ($method->isInsert())
            return $this->dbh->lastInsertId();
        @elif ($method->singleResult()) 
            @if ($method->mapAsObject())
                return $stmt->fetchObject({{ @$method->mapAsObject() }});
            @else
                return $stmt->fetch(PDO::FETCH_ASSOC);
            @end
        @else 
            return new Cursor($stmt, {{ @$method->mapAsObject() }});
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
