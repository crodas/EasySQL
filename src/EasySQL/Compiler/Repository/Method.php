<?php

namespace EasySQL\Compiler\Repository;

use Notoj\Annotation\Annotations;
use EasySQL\Engine;
use SQL\Statement;
use SQL\Writer;
use SQL\Insert;
use SQL\Select;
use SQL\Update;
use SQLParser\Stmt\Expr;
use SQLParser\Stmt\VariablePlaceholder;

class Method
{
    protected $query;
    protected $ann;
    protected $args;
    protected $engine;
    protected $lines;
    protected $iargs;

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

        $query->iterate(function($expr) use ($ignore, &$lines) {
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
        
        if ($query->getVariables('limit')) {
            $limit = $query->getVariables('limit');
            foreach ($query->getVariables() as $var) {
                if (in_array($var, $limit)) {
                    $lines[] = '$stmt->bindParam(":'. $var .'", $' . $var . ', PDO::PARAM_INT);';
                } else {
                    $lines[] = '$stmt->bindParam(":'. $var .'", $' . $var . ');';
                }
            }
            $this->iargs = array();
        } else {
            $this->iargs = array_unique($query->getVariables());
        }
        $this->lines = $lines;
    }

    public function isUpdate()
    {
        return $this->query instanceof Update;
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
        return 'compact("' . implode('","', $this->iargs) . '")';
    }

    public function getSQL()
    {
        return Writer::create($this->query);
    }
}
