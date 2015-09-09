<?php

class User {
    use EasySQL\Updatable;
}

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
        $conn->commit();
    }

    public function testCalls()
    {
        global $conn;

        $user = $conn->getRepository('user');
        $id = $user->create('foo@gmail.com', 'xxx') ?: 1; 
        $this->assertTrue(is_numeric($id));
        $this->assertTrue($user->byId($id) instanceof User);

        $this->assertTrue($user->all() instanceof PDOStatement);
        $i = 0;
        foreach ($user->all('y') as $u) {
            $this->assertEquals(40, strlen($u->password));
            $this->assertTrue($u instanceof User);
            $this->assertFalse($u->save());
            $u->email = 'foo@bar.com';
            $this->assertTrue($u->save());
            $this->assertFalse($u->save());

            $y = $user->byId($u->user_id);
            $this->assertEquals($u, $y);


            ++$i;
        }
        $this->assertTrue($i > 0);
    }
    
    public function testDefaultResponseType()
    {
        global $conn;
        foreach ($conn->getRepository('user')->asArray() as $row) {
            $this->assertEquals($row->user_id, $row['user_id']);
        }
    }

    public function testPluck()
    {
        global $conn;
        $this->assertEquals(array(1), $conn->getRepository('user')->pluck1());
        $this->assertEquals(array([1, 'foo@bar.com']), $conn->getRepository('user')->pluck2());
    }

}
