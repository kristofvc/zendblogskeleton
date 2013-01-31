<?php
namespace BlogTest\Model;

use Blog\Model\Post;
use PHPUnit_Framework_TestCase;

class PostTest extends PHPUnit_Framework_TestCase
{
    public function testPostInitialState()
    {
        $post = new Post();

        $this->assertNull($post->body, '"body" should initially be null');
        $this->assertNull($post->id, '"id" should initially be null');
        $this->assertNull($post->title, '"title" should initially be null');
    }

    public function testExchangeArraySetsPropertiesCorrectly()
    {
        $post = new Post();
        $data  = array('body' => 'some body for the post',
                       'id'     => 123,
                       'title'  => 'some title');

        $post->exchangeArray($data);

        $this->assertSame($data['body'], $post->body, '"body" was not set correctly');
        $this->assertSame($data['id'], $post->id, '"id" was not set correctly');
        $this->assertSame($data['title'], $post->title, '"title" was not set correctly');
    }

    public function testExchangeArraySetsPropertiesToNullIfKeysAreNotPresent()
    {
        $post = new Post();

        $post->exchangeArray(array('body' => 'some body for the post',
                                    'id'     => 123,
                                    'title'  => 'some title'));
        $post->exchangeArray(array());

        $this->assertNull($post->body, '"body" should have defaulted to null');
        $this->assertNull($post->id, '"id" should have defaulted to null');
        $this->assertNull($post->title, '"title" should have defaulted to null');
    }
}