<?php

namespace EasySQL\Compiler\Repository;

use Notoj\Annotation\Annotations;
use PHPSQLParser\PHPSQLCreator;
use EasySQL\Engine;
use SQLParser\Stmt;
use SQLParser\Writer;

class Method
{
    protected $query;
    protected $ann;
    protected $args;
    protected $engine;

    public function __construct(Annotations $ann, Stmt $query, Engine\Base $engine)
    {
        $this->engine = $engine;
        $this->query  = $query;
        $this->ann    = $ann;
        $this->args   = $query->getVariables();
    }

    protected function parseVariables($str, &$var)
    {
        return preg_match_all("/[\\$:]([a-z_][0-9_a-z]*)/i", $str, $var);
    }

    protected function parseArgs()
    {
    }

    public function isInsert()
    {
        reset($this->query);
        return key($this->query) === 'INSERT';
    }

    public function singleResult()
    {
        if ($this->ann->has('single,singleresult,one')) {
            return true;
        }

        $limit = $this->query->getLimit();

        if ($limit) {
            var_dump($limit);exit;
            return $this->query['LIMIT']['rowcount'] == 1;
        }

        return false;
    }

    public function getArguments()
    {
        return $this->args;
    }

    public function getFunctionSignature()
    {
        if (empty($this->args)) {
            return '';
        }
        return '$' . implode(", $", $this->args);
    }

    public function changeSchema()
    {
        return $this->query instanceof \SQLParser\Table;
    }

    public function mapAsObject()
    {
        $ann = $this->ann->getOne('mapWith,MapClass,ResultClass');
        if (!$ann) { 
            return false;
        }

        return $ann->getArg();
    }

    public function getCompact()
    {
        $args = $this->args;
        if (empty($args)) {
            return '';
        }
        return 'compact("' . implode('","', $this->args) . '")';
    }

    public function getSQL()
    {
        Writer\SQL::setInstance(new Writer\MySQL);
        return Writer\SQL::create($this->query);
    }
}
