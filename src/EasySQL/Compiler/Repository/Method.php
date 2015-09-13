<?php

namespace EasySQL\Compiler\Repository;

use Notoj\Annotation\Annotations;
use EasySQL\Engine;
use SQL\Statement;
use SQL\Writer;
use SQL\Insert;
use SQL\Delete;
use SQL\Select;
use SQL\Update;
use SQLParser\Stmt\Expr;
use SQLParser\Stmt\VariablePlaceholder;
use RuntimeException;

class Method
{
    protected $query;
    protected $ann;
    protected $args;
    protected $engine;
    protected $lines;
    protected $iargs;

    public function isScalar()
    {
        return $this->ann->has('singlecolumn,get_var,scalar')
            && $this->query instanceof Select;
    }

    public function getArrayVariables()
    {
        $vars = array();
        foreach ($this->ann->get('array,is_array,isarray') as $var) {
            $vars[] = trim($var->getArg(0), ':$ \r\t\n');
        }
        return array_unique($vars);
    }

    public function hasArrayVariable()
    {
        return $this->ann->has('array,is_array,isarray');
    }

    public function isPluck()
    {
        return $this->ann->has('pluck') && $this->query instanceof Select;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function __construct(Annotations $ann, Statement $query, Engine\Base $engine)
    {
        $this->engine = $engine;
        $this->query  = $query;
        $this->ann    = $ann;
        $this->args   = array_unique($query->getVariables());

        $args   = $this->args;
        $lines  = array();
        $ignore = array(
            'count', 'avg', 'max', 'min',
            'concat', 'distinct', 'isnull',
        );

        $innerSelect = array();
        $query->iterate(function($expr) use ($ignore, &$lines, &$innerSelect) {
            if ($expr instanceof Select) {
                $innerSelect[] = $expr;
            }
            if ($expr instanceof Expr && $expr->is('call')) {
                $method = strtolower($expr->getMember(0));
                if (!in_array($method, $ignore) && is_callable($method)) {
                    $call = array();
                    foreach ($expr->getMember(1)->getExprs() as $expr) {
                        if (!$expr instanceof VariablePlaceholder) {
                            return;
                        }
                        $call[] = '$' . $expr->getName();
                    }
                    $var  = 't' . uniqid(true);
                    $lines[] = "\$$var = $method(" . implode(",", $call) . ");";

                    return new VariablePlaceholder($var);
                }
            }
        });

        $innerSelect[] = $query;
        $hasVarsLimit  = false;
        $limit = array();

        foreach ($innerSelect as $q) {
            if ($q->getVariables('limit')) {
                $hasVarsLimit  = true;
                $limit = array_merge($limit, $q->getVariables('limit'));
            }
        }

        if ($hasVarsLimit) {
            foreach ($query->getVariables() as $var) {
                if (in_array($var, $limit)) {
                    $lines[] = '$stmt->bindParam(":'. $var .'", $' . $var . ', PDO::PARAM_INT);';
                } else {
                    $lines[] = '$stmt->bindParam(":'. $var .'", $' . $var . ');';
                }
            }
            $this->iargs = array();
            if ($this->hasArrayVariable()) {
                throw new RuntimeException("Cannot use array variables with variables in LIMIT");
            }
        } else {
            $this->iargs = array_unique($query->getVariables());
        }
        $this->lines = $lines;
    }

    public function getTables()
    {
        $tables = $this->query->getTables();
        if (count($tables) > 1 || $this->query->hasJoins()) {
            return false;
        }
        return current($tables);
    }

    public function isVoid()
    {
        return $this->ann->has('void,noreturn');
    }

    public function isSelect()
    {
        return $this->query instanceof Select;
    }

    public function isUpdate()
    {
        return $this->query instanceof Update;
    }

    public function isDelete()
    {
        return $this->query instanceof Delete;
    }

    public function isInsert()
    {
        return $this->query instanceof Insert;
    }

    public function singleResult()
    {
        if ($this->ann->has('single,singleresult,one')) {
            return true;
        }

        $limit = $this->query->getLimit();

        if ($limit) {
            return is_int($limit) && $limit == 1;
        }

        return false;
    }

    public function getPHPCode()
    {
        return $this->lines;
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
        
        $args     = array();
        $defaults = $this->ann->get('default');
        foreach ($this->args as $arg) {
            $found  = false;
            foreach ($defaults as $default) {
                if (in_array($default->getArg(0), ['$' . $arg, ':' . $arg, $arg])) {
                    $default = $default->getArg(1);
                    $found = true;
                    break;
                }
            }

            $arg = '$' . $arg;
            if ($found) {
                $arg .= '=' . var_export($default, true);
            }

            $args[] = $arg;
        }

        return implode(", ", $args);
    }

    public function changeSchema()
    {
        return $this->query instanceof Table;
    }

    public function mapAsObject()
    {
        $ann = $this->ann->getOne('mapWith,MapClass,ResultClass,mapAs');
        if (!$ann) { 
            return false;
        }

        return $ann->getArg();
    }

    public function getCompact()
    {
        if (empty($this->iargs)) {
            return '';
        }
        $args = $this->iargs;
        $exclude = $this->getArrayVariables();
        foreach ($args as $id => $var) {
            if (in_array($var, $exclude)) {
                unset($args[$id]);
            }
        }
        if (empty($args)) {
            return 'array()';
        } 
        return 'compact("' . implode('","', $args) . '")';
    }

    public function getSQL()
    {
        return Writer::create($this->query);
    }
}
