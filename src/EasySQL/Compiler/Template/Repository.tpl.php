<?php

namespace EasyRepository\t{{$mt=uniqid(true)}};

use PDO;
use ReflectionClass;
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
        @if ($method instanceof EasySQL\Compiler\Repository\Transaction) 
            $return = [];
            @foreach ($method->getMembersCalling() as $member)
            $return[] = $this->{{ $member }}
            @end
            return $return;
        @else
        @if ($method->hasArrayVariable())
            $sql = {{@$method->getSQL()}};
            @foreach ($method->getPHPCode() as $line)
                {{$line}}
            @end
            $replace   = array();
            $variables = {{$method->getCompact()}};
            @foreach ($method->getArrayVariables() as $var)
                if (!is_array(${{$var}})) {
                    throw new \RuntimeException({{@$var . " must e an array"}});
                }
                foreach (${{$var}} as $key => $value) {
                    $variables[{{@$var.'_'}} . $key] = $value;
                }
                $replace[{{@':' . $var}}] = {{@":{$var}_"}} . implode({{@", :{$var}_"}}, array_keys(${{$var}}));
            @end
            $stmt = $this->dbh->prepare(str_replace(array_keys($replace), array_values($replace), $sql));
            $result = $stmt->execute($variables);
        @else
            $stmt = $this->dbh->prepare({{@$method->getSQL()}});
            @foreach ($method->getPHPCode() as $line)
                {{$line}}
            @end
            $result = $stmt->execute({{$method->getCompact()}});
        @end
        @if ($method->isVoid())
            // void 
        @elif (!$method->isPluck() && $method->mapAsObject()) 
            $class = new ReflectionClass({{@$method->mapAsObject()}});
            if ($class->getConstructor()) {
                $stmt->setFetchMode(PDO::FETCH_CLASS, {{ @$method->mapAsObject() }}, array($this->dbh, {{ @$method->getTables() }}));
            } else {
                $stmt->setFetchMode(PDO::FETCH_CLASS, {{ @$method->mapAsObject() }});
            }
        @elif ($method->isSelect())
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'EasySQL\Result', array($this->dbh, {{ @$method->getTables() }}));
        @end
        @if ($method->isInsert())
            return $this->dbh->lastInsertId();
        @elif ($method->isScalar())
            $stmt->setFetchMode(PDO::FETCH_NUM);
            $result = $stmt->fetch();
            return $result[0];
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
