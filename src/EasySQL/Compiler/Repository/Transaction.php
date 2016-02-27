<?php

namespace EasySQL\Compiler\Repository;

use Notoj\Annotation\Annotations;
use EasySQL\Engine;

class Transaction extends Method
{
    protected $members;

    public function __construct(Annotations $ann, Engine\Base $engine)
    {
        $this->engine = $engine;
        $this->ann    = $ann;
    }

    public function add(Method $member)
    {
        $this->members['trans_' . uniqid(true)] = $member;
    }

    public function getMembers()
    {
        return $this->members;
    }

    public function getMembersCalling()
    {
        $calls = array();
        foreach ($this->members as $name => $member) {
            $variables = array_unique($member->query->getVariables());
            if (empty($variables)) {
                $calls[] = "$name();";
            } else {
                $calls[] = "$name($" . implode(', $', $member->query->getVariables()) . ");";
            }
        }

        return $calls;
    }

    public function getFunctionSignature()
    {
        $args = array();
        foreach ($this->members as $member) {
            $args = array_merge($args, $member->query->getVariables());
        }
        $this->args = array_unique($args);
        return parent::getFunctionSignature();
    }
}
