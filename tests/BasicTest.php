<?php

class BasicTest extends PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        global $conn;
        foreach ($conn->getRepositories() as $repo) {
            $this->assertTrue(is_string($repo));
            $this->assertTrue(is_object($conn->getRepository($repo)));
        }
        $this->assertTrue(count($conn->getRepositories()) > 1);
    }

    public function testCreate()
    {
        global $conn;

        $create = $conn->getRepository('create');

        $conn->begin();
        $create->users();
        $conn->rollback();

        $conn->begin();
        $create->users();
        $conn->commit();
    }

    public function testCalls()
    {
        global $conn;

        $user = $conn->getRepository('user');
        $id = $user->create('foo@gmail.com', 'xxx');
        $this->assertTrue(is_numeric($id));
        $user = $user->byId($id);
        $this->assertTrue(is_array($user));
    }

}
