<?php
/*
   The MIT License (MIT)

   Copyright (c) 2015 César Rodas

   Permission is hereby granted, free of charge, to any person obtaining a copy
   of this software and associated documentation files (the "Software"), to deal
   in the Software without restriction, including without limitation the rights
   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   copies of the Software, and to permit persons to whom the Software is
   furnished to do so, subject to the following conditions:

   The above copyright notice and this permission notice shall be included in
   all copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   THE SOFTWARE.
*/
namespace EasySQL;

use crodas\Build;
use RuntimeException;
use PDO;

require __DIR__ . "/autoload.php";

class EasySQL
{
    protected $dir;
    protected $pdo;
    protected $repos;
    protected $engine;

    public function __construct($dir, PDO $pdo)
    {
        if (!is_dir($dir)) {
            throw new RuntimeException("$dir is not a valid directory");
        }
        $engine = 'EasySQL\Engine\\' . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if (class_exists($engine)) {
            $engine = new $engine;
        } else {
            $engine = new Engine\Base;
        }
        $build  = new Build(__DIR__ . '/Compiler/Builder.php');
        $file   = $build->easysql([$dir], [$engine]);
        $loader = require $file;
        $this->pdo    = $pdo;
        $this->repos  = $loader($pdo);
        $this->dir    = $dir;
        $this->engine = $engine;
    }

    public function begin()
    {
        $this->engine->begin($this->pdo);
    }

    public function rollback()
    {
        $this->engine->rollback($this->pdo);
    }

    public function commit()
    {
        $this->engine->commit($this->pdo);
    }

    public function getRepositories()
    {
        return array_keys($this->repos);
    }

    public function getRepository($name)
    {
        $name = strtolower($name);
        if (empty($this->repos[$name])) {
            throw new RuntimeException("Cannot find repository $name");
        }

        return $this->repos[$name];
    }

}
