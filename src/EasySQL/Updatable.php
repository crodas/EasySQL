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

use RuntimeException;
use PDO;

function properties($object)
{
    return get_object_vars($object);
}

trait Updatable
{
    private $_pdo;
    private $_table;
    private $_values;

    public function __construct(PDO $pdo, $table)
    {
        $this->_values = properties($this);
        $this->_pdo    = $pdo;
        $this->_table  = $table;
    }

    public function save()
    {
        if (empty($this->_table)) {
            throw new RuntimeException("This result cannot be updated");
        }

        $changes = array();
        foreach ($this->_values as $key => $value) {
            if ($this->$key !== $value) {
                $changes[$key] = $this->$key;
            }
        }

        if (empty($changes)) {
            return false;
        }

        if (!empty($this->_values['table_pk'])) {
            $pk    = $this->_values['table_pk'];
            $where = array($pk => $this->_values[$pk]);
        } else {
            $where = $this->_values;
        }

        $sql = $this->_pdo->prepare("UPDATE {$this->_table} 
                SET " . implode("=?,", array_keys($changes)) . "=? 
                WHERE " . implode("=? AND ", array_keys($where)) . "=?");

        $sql->execute(array_merge(array_values($changes), array_values($where)));

        foreach ($changes as $key => $value) {
            $this->_values[$key] = $value;
        }

        return true;
    }
}
