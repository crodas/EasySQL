<?php

namespace EasySQL\Compiler\Repository;

use Notoj\Annotation\Annotations;
use PHPSQLParser\PHPSQLCreator;

class Method
{
    protected $query;
    protected $ann;
    protected $args;

    public function __construct(Annotations $ann, Array $query)
    {
        $this->query = $query;
        $this->ann   = $ann;
        $this->parseArgs();
    }

    protected function parseVariables($str, &$var)
    {
        return preg_match_all("/[\\$:]([a-z_][0-9_a-z]*)/i", $str, $var);
    }

    protected function getVariables(Array & $stmt, Array &$vars)
    {
        switch ($stmt['expr_type']) {
        case 'limit':
            foreach (['offset', 'rowcount'] as $type) {
                if ($this->parseVariables($stmt[$type], $var)) {
                    $vars = array_merge($vars, $var[1]);
                    $stmt[$type] = ':' . $var[1][0];
                }
            }
            break;
        case 'table':
        case 'colref':
            if (!$this->parseVariables($stmt['base_expr'], $var)) {
                return;
            }
            $vars = array_merge($vars, $var[1]);
            $stmt['base_expr'] = str_replace($var[0], array_map(function($m) {
                return ":$m";
            }, $var[1]), $stmt['base_expr']);
            break;
            return;
        case 'record':
            foreach ($stmt['data'] as &$sub) {
                $this->getVariables($sub, $vars);
            }
        }
    }

    protected function parseArgs()
    {
        $vars = [];
        foreach ($this->query as &$stmts) {
            foreach ($stmts as &$stmt) {
                if (is_array($stmt)) {
                    $this->getVariables($stmt, $vars);
                }
            }
        }

        if (!empty($this->query['LIMIT'])) {
            $limit = $this->query['LIMIT'];
            $limit['expr_type'] = 'limit';
            $this->getVariables($limit, $vars);
        }

        $this->args = array_unique($vars);
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

        if (!empty($this->query['LIMIT'])) {
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
        $creator = new PHPSQLCreator;
        reset($this->query);
        return $creator->create($this->query);
    }
}
