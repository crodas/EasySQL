<?php

namespace EasySQL\Compiler;

use EasySQL_Compiler_QueryParser as Parser;
use Exception;

class QueryLexer {
    private $data;
    private $N;
    public $token;
    public $value;
    private $line;

    function __construct($data) {
        $this->data = trim($data);
        $this->N    = 0;
        $this->line = 1;
    }

/*!lex2php

%input $this->data
%counter $this->N
%token $this->token
%value $this->value
%line $this->line

comment = /\-\-[^\n]+/
semi    = ";"
sql     = /./
newline = /\n/

*/
/*!lex2php
  %statename YYINITIAL
  comment   { $this->token = Parser::COMMENT; }
  semi      { $this->token = Parser::SEMICOLON; }
  sql       { $this->token = Parser::RAW_SQL; }
  newline   { $this->token = Parser::RAW_SQL; }
*/

}
