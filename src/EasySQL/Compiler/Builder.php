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

use crodas\FileUtil\File;
use Symfony\Component\Finder\Finder;

/**
 *  @Task(easysql)
 */
function builder($output, Array $files, Array $args, $builder)
{
    $finder = new Finder();
    $iterator = $finder
        ->name("*.sql")
        ->files();
    foreach ($files as $dir) {
        $iterator->in($dir);
    }
    $files = array();
    foreach ($iterator as $file) {
        $query = new Query($file, $file->getRelativePathname(), $args[0]);
        $files[$query->getName()] = $query;
        $builder->watch($file);
    }
    $builder->watch(__DIR__ . '/templates.php');

    File::write($output, Templates::get('repository')->render(compact('files'), true));
}
