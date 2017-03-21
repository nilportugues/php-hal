# HAL+JSON & HAL+XML API Transformer

[![Build Status](https://travis-ci.org/nilportugues/php-hal.svg)](https://travis-ci.org/nilportugues/php-hal) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nilportugues/hal-transformer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nilportugues/hal-transformer/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e2a5a3b2-7097-4783-9912-0cbaadd0ed0e/mini.png)](https://insight.sensiolabs.com/projects/e2a5a3b2-7097-4783-9912-0cbaadd0ed0e)
[![Latest Stable Version](https://poser.pugx.org/nilportugues/hal/v/stable)](https://packagist.org/packages/nilportugues/hal) 
[![Total Downloads](https://poser.pugx.org/nilportugues/hal/downloads)](https://packagist.org/packages/nilportugues/hal) 
[![License](https://poser.pugx.org/nilportugues/hal/license)](https://packagist.org/packages/nilportugues/hal) 
[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif)](https://paypal.me/nilportugues)

1. [Installation](#1-installation)
2. [Mapping](#2-mapping)
    - 2.1 [Mapping with arrays](#21-mapping-with-arrays)
    - 2.2 [Mapping with Mapping class](#22-mapping-with-mapping-class)
3. [HAL Serialization](#3-hal-serialization)
    - 3.1 [HAL+JSON](#31-haljson)
    - 3.2 [HAL+XML](#32-halxml)
4. [HAL Paginated Resource](#4-hal-paginated-resource)
5. [PSR-7 Response objects](#5-response-objects)
 
## 1. Installation

Use [Composer](https://getcomposer.org) to install the package:

```json
$ composer require nilportugues/hal
```

## 2. Mapping

Given a PHP Object, and a series of mappings, the HAL+JSON and HAL+XML API transformer will represent the given data following the `https://tools.ietf.org/html/draft-kelly-json-hal-07` specification draft.

For instance, given the following piece of code, defining a Blog Post and some comments:

```php
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
              'created_at' => (new DateTime('2015/07/18 12:13:00'))->format('c'),
              'accepted_at' => (new DateTime('2015/07/19 00:00:00'))->format('c'),
          ]
      ),
  ]
);
```

We will have to map all the involved classes. This can be done as one single array, or a series of Mapping classes.

### 2.1 Mapping with arrays

Mapping involved classes using arrays is done as follows:


```php
use NilPortugues\Api\Mapping\Mapper;

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
            'comments' => 'http://example.com/posts/{postId}/comments'
        ],
        'curies' => [
            'name' => 'example',
            'href' => "http://example.com/docs/rels/{rel}",
        ]
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
        ],
        'curies' => [
            'name' => 'example',
            'href' => "http://example.com/docs/rels/{rel}",
        ]
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
        'curies' => [
            'name' => 'example',
            'href' => "http://example.com/docs/rels/{rel}",
        ]
    ]
];

$mapper = new Mapper($mappings);
```

### 2.2 Mapping with Mapping class

In order to map with Mapping class, you need to create a new class for each involved class.

This mapping fashion scales way better than using an array.

All Mapping classes will extend the `\NilPortugues\Api\Mappings\HalMapping` interface. 

```php

// PostMapping.php

class PostMapping implements \NilPortugues\Api\Mappings\HalMapping 
{
    public function getClass()
    {
        return Post::class;
    }

    public function getAlias()
    {
        return 'Message';
    }

    public function getAliasedProperties()
    {
        return [
           'author' => 'author',
           'title' => 'headline',
           'content' => 'body',
       ];
    }

    public function getHideProperties()
    {
        return [];
    }

    public function getIdProperties()
    {
        return ['postId'];
    }
   
    public function getUrls()
    {
        return [            
            'self' => 'http://example.com/posts/{postId}', // Mandatory            
            'comments' => 'http://example.com/posts/{postId}/comments' // Optional
        ];
    }

    public function getCuries()
    {
        return [
           'name' => 'example',
           'href' => "http://example.com/docs/rels/{rel}",
        ];
    }
}


// UserMapping.php

class UserMapping implements \NilPortugues\Api\Mappings\HalMapping 
{
    public function getClass()
    {
        return User::class;
    }

    public function getAlias()
    {
        return '';
    }

    public function getAliasedProperties()
    {
        return [];
    }

    public function getHideProperties()
    {
        return [];
    }

    public function getIdProperties()
    {
        return ['postId'];
    }
   
    public function getUrls()
    {
        return [            
            'self' => 'http://example.com/users/{userId}'
        ];
    }

    public function getCuries()
    {
        return [
           'name' => 'example',
           'href' => "http://example.com/docs/rels/{rel}",
        ];
    }
}


// CommentMapping.php

class CommentMapping implements \NilPortugues\Api\Mappings\HalMapping 
{
    public function getClass()
    {
        return Comment::class;
    }

    public function getAlias()
    {
        return '';
    }

    public function getAliasedProperties()
    {
        return [];
    }

    public function getHideProperties()
    {
        return [];
    }

    public function getIdProperties()
    {
        return ['commentId'];
    }
   
    public function getUrls()
    {
        return [            
            'self' => 'http://example.com/comments/{commentId}',
        ];
    }

    public function getCuries()
    {
        return [
           'name' => 'example',
           'href' => "http://example.com/docs/rels/{rel}",
        ];
    }
} 

$mappings = [
    PostMapping::class,
    UserMapping::class,
    CommentMapping::class,
];
$mapper = new Mapper($mappings);

```

## 3. HAL Serialization

Calling the transformer will output a **valid HAL response** using the correct formatting:

```php
use NilPortugues\Api\Hal\JsonTransformer; 
use NilPortugues\Api\Hal\HalSerializer;
use NilPortugues\Api\Hal\Http\Message\Response;

$transformer = new JsonTransformer($mapper);
//For XML: $transformer = new XmlTransformer($mapper);

//Output transformation
$serializer = new HalSerializer($transformer);
$output = $serializer->serialize($post);

//PSR7 Response with headers and content.
$response = new Response($output);

header(
    sprintf(
        'HTTP/%s %s %s',
        $response->getProtocolVersion(),
        $response->getStatusCode(),
        $response->getReasonPhrase()
    )
);

foreach($response->getHeaders() as $header => $values) {
    header(sprintf("%s:%s\n", $header, implode(', ', $values)));
}

echo $response->getBody();
```

### 3.1 HAL+JSON

```
HTTP/1.1 200 OK
Cache-Control: private, max-age=0, must-revalidate
Content-type: application/hal+json
```
Output: 
```json
{
    "post_id": 9,
    "headline": "Hello World",
    "body": "Your first post",
    "_embedded": {
        "author": {
            "user_id": 1,
            "name": "Post Author",
            "_links": {
                "self": {
                    "href": "http://example.com/users/1"
                },
                "example:friends": {
                    "href": "http://example.com/users/1/friends"
                },
                "example:comments": {
                    "href": "http://example.com/users/1/comments"
                }
            }
        },
        "comments": [
            {
                "comment_id": 1000,
                "dates": {
                    "created_at": "2015-08-13T22:47:45+02:00",
                    "accepted_at": "2015-08-13T23:22:45+02:00"
                },
                "comment": "Have no fear, sers, your king is safe.",
                "_embedded": {
                    "user": {
                        "user_id": 2,
                        "name": "Barristan Selmy",
                        "_links": {
                            "self": {
                                "href": "http://example.com/users/2"
                            },
                            "example:friends": {
                                "href": "http://example.com/users/2/friends"
                            },
                            "example:comments": {
                                "href": "http://example.com/users/2/comments"
                            }
                        }
                    }
                },
                "_links": {
                    "example:user": {
                        "href": "http://example.com/users/2"
                    },
                    "self": {
                        "href": "http://example.com/comments/1000"
                    }
                }
            }
        ]
    },
    "_links": {
        "curies": [
            {
                "name": "example",
                "href": "http://example.com/docs/rels/{rel}",
                "templated": true
            }
        ],
        "self": {
            "href": "http://example.com/posts/9"
        },
        "example:author": {
            "href": "http://example.com/users/1"
        },
        "example:comments": {
            "href": "http://example.com/posts/9/comments"
        }
    }
}
```


### 3.2 HAL+XML

For XML output use the sample code but using the XML transformer instead: 

```
$transformer = new XmlTransformer($mapper);
```

Output:

```
HTTP/1.1 200 OK
Cache-Control: private, max-age=0, must-revalidate
Content-type: application/hal+xml
```

```xml
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
    <link rel="self" href="http://example.com/posts/9"/>
    <link rel="example:author" href="http://example.com/users/1"/>
    <link rel="example:comments" href="http://example.com/posts/9/comments"/>
  </links>
</resource>
```

## 4. HAL Paginated Resource

A pagination object to easy the usage of this package is provided. 

For both XML and JSON output, use the `HalPagination` object to build your paginated representation of the current resource.
 
Methods provided by `HalPagination` are as follows: 

 - `setSelf($self)`
 - `setFirst($first)`
 - `setPrev($prev)`
 - `setNext($next)`
 - `setLast($last)`
 - `setCount($count)`
 - `setTotal($total)`
 - `setEmbedded(array $embedded)`
 
In order to use it, create a new HalPagination instance, use the setters and pass the instance to the `serialize($value)` method of the serializer. 

Everything else will be handled by serializer itself. Easy as that!
 
```php
use NilPortugues\Api\Hal\HalPagination; 
use NilPortugues\Api\Hal\HalSerializer; 
use NilPortugues\Api\Hal\JsonTransformer; 

// ...
//$objects is an array of objects, such as Post::class.
// ...
 
$page = new HalPagination();

//set the amounts
$page->setTotal(20);
$page->setCount(10);

//set the objects
$page->setEmbedded($objects);

//set up the pagination links
$page->setSelf('/post?page=1');
$page->setPrev('/post?page=1');
$page->setFirst('/post?page=1');
$page->setLast('/post?page=1');

$output = $serializer->serialize($page);

``` 

## 5. Response objects

The following PSR-7 Response objects providing the right headers and HTTP status codes are available.

Its use is optional and are provided as a starting point.

- `NilPortugues\Api\Hal\Http\Message\ErrorResponse($body)`
- `NilPortugues\Api\Hal\Http\Message\ResourceCreatedResponse($body)`
- `NilPortugues\Api\Hal\Http\Message\ResourceDeletedResponse($body)`
- `NilPortugues\Api\Hal\Http\Message\ResourceNotFoundResponse($body)`
- `NilPortugues\Api\Hal\Http\Message\ResourcePatchErrorResponse($body)`
- `NilPortugues\Api\Hal\Http\Message\ResourcePostErrorResponse($body)`
- `NilPortugues\Api\Hal\Http\Message\ResourceProcessingResponse($body)`
- `NilPortugues\Api\Hal\Http\Message\ResourceUpdatedResponse($body)`
- `NilPortugues\Api\Hal\Http\Message\Response($body)`
- `NilPortugues\Api\Hal\Http\Message\UnsupportedActionResponse($body)`


## Quality

To run the PHPUnit tests at the command line, go to the tests directory and issue phpunit.

This library attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/).

If you notice compliance oversights, please send a patch via [Pull Request](https://github.com/nilportugues/hal-transformer/pulls).


## Contribute

Contributions to the package are always welcome!

* Report any bugs or issues you find on the [issue tracker](https://github.com/nilportugues/php-hal/issues/new).
* You can grab the source code at the package's [Git repository](https://github.com/nilportugues/php-hal).

## Support

Get in touch with me using one of the following means:

 - Emailing me at <contact@nilportugues.com>
 - Opening an [Issue](https://github.com/nilportugues/php-hal/issues/new)

## Authors

* [Nil Portugués Calderó](http://nilportugues.com)
* [The Community Contributors](https://github.com/nilportugues/php-hal/graphs/contributors)


## License
The code base is licensed under the [MIT license](LICENSE).
