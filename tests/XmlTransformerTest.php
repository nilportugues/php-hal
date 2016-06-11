<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/18/15
 * Time: 11:27 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Tests\Api\Hal;

use DateTime;
use NilPortugues\Api\Hal\XmlTransformer;
use NilPortugues\Api\Mapping\Mapper;
use NilPortugues\Api\Mapping\Mapping;
use NilPortugues\Api\Transformer\TransformerException;
use NilPortugues\Api\Hal\HalSerializer;
use NilPortugues\Tests\Api\Hal\Dummy\ComplexObject\Comment;
use NilPortugues\Tests\Api\Hal\Dummy\ComplexObject\Post;
use NilPortugues\Tests\Api\Hal\Dummy\ComplexObject\User;
use NilPortugues\Tests\Api\Hal\Dummy\ComplexObject\ValueObject\CommentId;
use NilPortugues\Tests\Api\Hal\Dummy\ComplexObject\ValueObject\PostId;
use NilPortugues\Tests\Api\Hal\Dummy\ComplexObject\ValueObject\UserId;
use NilPortugues\Tests\Api\Hal\Dummy\SimpleObject\Post as SimplePost;

class XmlTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testItWillSerializeToHalXmlAnArrayOfObjects()
    {
        $postArray = [
            new SimplePost(1, 'post title 1', 'post body 1', 4),
            new SimplePost(2, 'post title 2', 'post body 2', 5),
        ];

        $postMapping = new Mapping(SimplePost::class, '/post/{postId}', ['postId']);
        $postMapping->setFilterKeys(['body', 'title']);

        $mapper = new Mapper();
        $mapper->setClassMap([$postMapping->getClassName() => $postMapping]);

        $transformer = new XmlTransformer($mapper);

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<resource>
  <total><![CDATA[2]]></total>
  <embedded>
    <resource href="/post/1">
      <post_id><![CDATA[1]]></post_id>
      <title><![CDATA[post title 1]]></title>
      <body><![CDATA[post body 1]]></body>
      <author_id><![CDATA[4]]></author_id>
      <comments/>
      <links>
        <link rel="self" href="/post/1"/>
      </links>
    </resource>
    <resource href="/post/2">
      <post_id><![CDATA[2]]></post_id>
      <title><![CDATA[post title 2]]></title>
      <body><![CDATA[post body 2]]></body>
      <author_id><![CDATA[5]]></author_id>
      <comments/>
      <links>
        <link rel="self" href="/post/2"/>
      </links>
    </resource>
  </embedded>
</resource>
XML;

        $this->assertEquals($expected, (new HalSerializer($transformer))->serialize($postArray));
    }

    /**
     *
     */
    public function testItWillThrowExceptionIfNoMappingsAreProvided()
    {
        $mapper = new Mapper();
        $mapper->setClassMap([]);

        $this->setExpectedException(TransformerException::class);
        (new HalSerializer(new XmlTransformer($mapper)))->serialize(new \stdClass());
    }

    /**
     *
     */
    public function testItWillSerializeToHalXmlAComplexObject()
    {
        $mappings = [
            [
                'class' => Post::class,
                'alias' => 'Message',
                'aliased_properties' => [
                    'author' => 'author',
                    'title' => 'headline',
                    'content' => 'body',
                ],
                'hide_properties' => [

                ],
                'id_properties' => [
                    'postId',
                ],
                'urls' => [
                    // Mandatory
                    'self' => 'http://example.com/posts/{postId}',
                    // Optional
                    'comments' => 'http://example.com/posts/{postId}/comments',
                ],
                // (Optional) Used by HAL+Xml
                'curies' => [
                    'name' => 'example',
                    'href' => 'http://example.com/docs/rels/{rel}',
                ],
            ],
            [
                'class' => User::class,
                'alias' => '',
                'aliased_properties' => [],
                'hide_properties' => [],
                'id_properties' => [
                    'userId',
                ],
                'urls' => [
                    'self' => 'http://example.com/users/{userId}',
                    'friends' => 'http://example.com/users/{userId}/friends',
                    'comments' => 'http://example.com/users/{userId}/comments',
                ],
                // (Optional) Used by HAL+Xml
                'curies' => [
                    'name' => 'example',
                    'href' => 'http://example.com/docs/rels/{rel}',
                ],
            ],

            [
                'class' => Comment::class,
                'alias' => '',
                'aliased_properties' => [],
                'hide_properties' => [],
                'id_properties' => [
                    'commentId',
                ],
                'urls' => [
                    'self' => 'http://example.com/comments/{commentId}',
                ],
                // (Optional) Used by HAL+Xml
                'curies' => [
                    'name' => 'example',
                    'href' => 'http://example.com/docs/rels/{rel}',
                ],
            ],
            [
                'class' => CommentId::class,
                'alias' => '',
                'aliased_properties' => [],
                'hide_properties' => [],
                'id_properties' => [
                    'commentId',
                ],
                'urls' => [
                    'self' => 'http://example.com/comments/{commentId}',
                ],
                // (Optional) Used by HAL+Xml
                'curies' => [
                    'name' => 'example',
                    'href' => 'http://example.com/docs/rels/{rel}',
                ],
            ],
        ];

        $mapper = new Mapper($mappings);

        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<resource href="http://example.com/posts/9">
  <post_id><![CDATA[9]]></post_id>
  <headline><![CDATA[Hello World]]></headline>
  <body><![CDATA[Your first post]]></body>
  <embedded>
    <resource href="http://example.com/users/1" rel="author">
      <user_id><![CDATA[1]]></user_id>
      <name><![CDATA[Post Author]]></name>
      <links>
        <link rel="self" href="http://example.com/users/1"/>
        <link rel="example:friends" href="http://example.com/users/1/friends"/>
        <link rel="example:comments" href="http://example.com/users/1/comments"/>
      </links>
    </resource>
    <comments>
      <resource href="http://example.com/comments/1000">
        <comment_id><![CDATA[1000]]></comment_id>
        <dates>
          <created_at><![CDATA[2015-07-18T12:13:00+00:00]]></created_at>
          <accepted_at><![CDATA[2015-07-19T00:00:00+00:00]]></accepted_at>
        </dates>
        <comment><![CDATA[Have no fear, sers, your king is safe.]]></comment>
        <embedded>
          <resource href="http://example.com/users/2" rel="user">
            <user_id><![CDATA[2]]></user_id>
            <name><![CDATA[Barristan Selmy]]></name>
            <links>
              <link rel="self" href="http://example.com/users/2"/>
              <link rel="example:friends" href="http://example.com/users/2/friends"/>
              <link rel="example:comments" href="http://example.com/users/2/comments"/>
            </links>
          </resource>
        </embedded>
        <links>
          <link rel="example:user" href="http://example.com/users/2"/>
          <link rel="self" href="http://example.com/comments/1000"/>
        </links>
      </resource>
    </comments>
  </embedded>
  <links>
    <curies>
      <link rel="resource" href="http://example.com/docs/rels/{rel}">
        <name><![CDATA[example]]></name>
        <templated><![CDATA[true]]></templated>
      </link>
    </curies>
    <link rel="first" href="http://example.com/posts/1"/>
    <link rel="next" href="http://example.com/posts/10"/>
    <link rel="example:author" href="http://example.com/users/1"/>
    <link rel="example:comments" href="http://example.com/posts/9/comments"/>
    <link rel="self" href="http://example.com/posts/9"/>
  </links>
  <meta>
    <author>
      <name><![CDATA[Nil Portugués Calderó]]></name>
      <email><![CDATA[contact@nilportugues.com]]></email>
    </author>
    <is_devel><![CDATA[true]]></is_devel>
  </meta>
</resource>
XML;
        $post = new Post(
            new PostId(9),
            'Hello World',
            'Your first post',
            new User(
                new UserId(1),
                'Post Author'
            ),
            [
                new Comment(
                    new CommentId(1000),
                    'Have no fear, sers, your king is safe.',
                    new User(new UserId(2), 'Barristan Selmy'),
                    [
                        'created_at' => (new DateTime('2015-07-18T12:13:00+00:00'))->format('c'),
                        'accepted_at' => (new DateTime('2015-07-19T00:00:00+00:00'))->format('c'),
                    ]
                ),
            ]
        );

        $transformer = new XmlTransformer($mapper);
        $transformer->setMeta(
            [
                'author' => [
                    'name' => 'Nil Portugués Calderó',
                    'email' => 'contact@nilportugues.com',
                ],
            ]
        );
        $transformer->addMeta('is_devel', true);
        $transformer->setFirstUrl('http://example.com/posts/1');
        $transformer->setNextUrl('http://example.com/posts/10');

        $this->assertEquals($expected, (new HalSerializer($transformer))->serialize($post));
    }
}
