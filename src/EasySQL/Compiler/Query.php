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
namespace EasySQL\Compiler;

use Notoj\Notoj;
use SQLParser;
use EasySQL_Compiler_QueryParser as Parser;
use EasySQL\Engine;
use SQL\Writer;
use RuntimeException;

class Query
{
    protected $file;
    protected $name;
    protected $engine;
    protected $queries;

    public function __construct($file, $relative, Engine\Base $engine)
    {
        if (!is_readable($file)) {
            throw new RuntimeException("{$file} is not a valid file");
        }
        $this->file   = $file;
        $this->engine = $engine;
        $this->name   = preg_replace("/\..+$/", "", basename($file));
        $this->parse(file_get_contents($file));
        Writer::setInstance($engine->getName());
    }

    protected function parse($content)
    {
        $sqlparser = new SQLParser;
        $queries = array();
        foreach ($sqlparser->parse($content) as $query) {
            if (empty($query)) continue;
            $comment = implode("\n", $query->getComments());
            $comment = "/**\n$comment\n*/";

            $annotations = Notoj::parseDocComment($comment);
            $name = $annotations->getOne('name');
            $name = $name ? current($name->getArgs()) : false;
            if (!$name) {
                throw new RuntimeException("Query doesn't have a `@name`. $query");
            }

            $queries[$name] = new Repository\Method($annotations, $query, $this->engine);
        }

        $this->name    = ucfirst($this->name);
        $this->queries = $queries;
    }

    public function getMethods()
    {
        return $this->queries;
    }

    public function getName()
    {
        return $this->name;
    }
}
