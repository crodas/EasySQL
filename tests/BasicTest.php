<?php

class User {}

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
        $this->assertTrue($user->byId($id) instanceof User);

        $this->assertTrue($user->all() instanceof PDOStatement);
        foreach ($user->all() as $u) {
            $this->assertEquals(40, strlen($u->password));
            $this->assertTrue($u instanceof User);
        }
    }

}
