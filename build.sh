php ./vendor/crodas/simple-view-engine/cli.php compile -N EasySQL\\Compiler src/EasySQL/Compiler/Template src/EasySQL/Compiler/Templates.php
phplemon src/EasySQL/Compiler/QueryParser.y
php vendor/crodas/autoloader/cli.php generate src/EasySQL/autoload.php src/EasySQL --library
plex src/EasySQL/Compiler/QueryLexer.lex
