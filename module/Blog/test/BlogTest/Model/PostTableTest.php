<?php
namespace BlogTest\Model;

use Blog\Model\PostTable;
use Blog\Model\Post;
use Zend\Db\ResultSet\ResultSet;
use PHPUnit_Framework_TestCase;

class PostTableTest extends PHPUnit_Framework_TestCase
{
    public function testFetchAllReturnsAllPosts()
    {
        $resultSet        = new ResultSet();
        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway',
                                           array('select'), array(), '', false);
        $mockTableGateway->expects($this->once())
                         ->method('select')
                         ->with()
                         ->will($this->returnValue($resultSet));

        $postTable = new PostTable($mockTableGateway);

        $this->assertSame($resultSet, $postTable->fetchAll());
    }

    public function testCanRetrieveAPostByItsId()
    {
        $post = new Post();
        $post->exchangeArray(array('id'     => 123,
                                    'body' => 'The Military Wives',
                                    'title'  => 'In My Dreams'));

        $resultSet = new ResultSet();
        $resultSet->setArrayObjectPrototype(new Post());
        $resultSet->initialize(array($post));

        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('select'), array(), '', false);
        $mockTableGateway->expects($this->once())
                         ->method('select')
                         ->with(array('id' => 123))
                         ->will($this->returnValue($resultSet));

        $postTable = new PostTable($mockTableGateway);

        $this->assertSame($post, $postTable->getPost(123));
    }

    public function testCanDeleteAPostByItsId()
    {
        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('delete'), array(), '', false);
        $mockTableGateway->expects($this->once())
                         ->method('delete')
                         ->with(array('id' => 123));

        $postTable = new PostTable($mockTableGateway);
        $postTable->deletePost(123);
    }

    public function testSavePostWillInsertNewPostsIfTheyDontAlreadyHaveAnId()
    {
        $postData = array('body' => 'The Military Wives', 'title' => 'In My Dreams');
        $post     = new Post();
        $post->exchangeArray($postData);

        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('insert'), array(), '', false);
        $mockTableGateway->expects($this->once())
                         ->method('insert')
                         ->with($postData);

        $postTable = new PostTable($mockTableGateway);
        $postTable->savePost($post);
    }

    public function testSavePostWillUpdateExistingPostsIfTheyAlreadyHaveAnId()
    {
        $postData = array('id' => 123, 'body' => 'The Military Wives', 'title' => 'In My Dreams');
        $post     = new Post();
        $post->exchangeArray($postData);

        $resultSet = new ResultSet();
        $resultSet->setArrayObjectPrototype(new Post());
        $resultSet->initialize(array($post));

        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway',
                                           array('select', 'update'), array(), '', false);
        $mockTableGateway->expects($this->once())
                         ->method('select')
                         ->with(array('id' => 123))
                         ->will($this->returnValue($resultSet));
        $mockTableGateway->expects($this->once())
                         ->method('update')
                         ->with(array('body' => 'The Military Wives', 'title' => 'In My Dreams'),
                                array('id' => 123));

        $postTable = new PostTable($mockTableGateway);
        $postTable->savePost($post);
    }

    public function testExceptionIsThrownWhenGettingNonexistentPost()
    {
        $resultSet = new ResultSet();
        $resultSet->setArrayObjectPrototype(new Post());
        $resultSet->initialize(array());

        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('select'), array(), '', false);
        $mockTableGateway->expects($this->once())
                         ->method('select')
                         ->with(array('id' => 123))
                         ->will($this->returnValue($resultSet));

        $postTable = new PostTable($mockTableGateway);

        try
        {
            $postTable->getPost(123);
        }
        catch (\Exception $e)
        {
            $this->assertSame('Could not find row 123', $e->getMessage());
            return;
        }

        $this->fail('Expected exception was not thrown');
    }

}