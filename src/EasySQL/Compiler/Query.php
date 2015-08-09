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
use PHPSQLParser\PHPSQLParser;
use EasySQL_Compiler_QueryParser as Parser;
use RuntimeException;

class Query
{
    protected $file;
    protected $name;
    protected $queries;

    public function __construct($file)
    {
        if (!is_readable($file)) {
            throw new RuntimeException("{$file} is not a valid file");
        }
        $this->file = $file;
        $this->name = preg_replace("/\..+$/", "", basename($file));
        $this->parse(file_get_contents($file));
    }

    protected function parse($content)
    {
        $parser    = new Parser;
        $lexer     = new QueryLexer($content);
        $queries   = array();
        $sqlparser = new PHPSQLParser;
        while ($lexer->yylex()) {
            $value = $lexer->value;
            if (in_array($lexer->token, [Parser::RAW_SQL, Parser::COMMENT]) && empty($value)) {
                continue;
            }
            $parser->doParse($lexer->token, $value);
        }
        $parser->doParse(0, 0);
        foreach ($parser->body as $parts) {
            $query   = "";
            $comment = "";
            foreach ($parts as $part) {
                if ($part[0] == 'comment') {
                    $comment .= trim(trim($part[1], '-*/')) . "\n";
                } else {
                    $query .= $part[1];
                }
            }
            if (empty($query)) continue;
            
            $comment = "/**\n$comment\n*/";

            $annotations = Notoj::parseDocComment($comment);
            $name = $annotations->getOne('name');
            $name = $name ? current($name->getArgs()) : false;
            if (!$name) {
                throw new RuntimeException("Query doesn't have a `@name`. $query");
            }

            $queries[$name] = new Repository\Method($annotations, $sqlparser->parse($query));
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
