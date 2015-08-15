# HAL+JSON API Transformer

[![Build Status]
(https://travis-ci.org/nilportugues/hal-json-transformer.svg)]
(https://travis-ci.org/nilportugues/hal-json-transformer) [![Coverage Status](https://coveralls.io/repos/nilportugues/hal-json-transformer/badge.svg?branch=master&service=github?)](https://coveralls.io/github/nilportugues/hal-json-transformer?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nilportugues/hal-json-transformer/badges/quality-score.png?b=master)]
(https://scrutinizer-ci.com/g/nilportugues/hal-json-transformer/?branch=master) [![SensioLabsInsight]
(https://insight.sensiolabs.com/projects/e2a5a3b2-7097-4783-9912-0cbaadd0ed0e/mini.png)]
(https://insight.sensiolabs.com/projects/e2a5a3b2-7097-4783-9912-0cbaadd0ed0e) [![Latest Stable Version]
(https://poser.pugx.org/nilportugues/haljson/v/stable)]
(https://packagist.org/packages/nilportugues/haljson) [![Total Downloads]
(https://poser.pugx.org/nilportugues/haljson/downloads)]
(https://packagist.org/packages/nilportugues/haljson) [![License]
(https://poser.pugx.org/nilportugues/haljson/license)]
(https://packagist.org/packages/nilportugues/haljson) 

## Installation

Use [Composer](https://getcomposer.org) to install the package:

```json
$ composer require nilportugues/haljson
```

## Usage
Given a PHP Object, and a series of mappings, the HAL+JSON API transformer will represent the given data following the `https://tools.ietf.org/html/draft-kelly-json-hal-07` specification draft.

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

And a Mapping array for all the involved classes:

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
        'class' => PostId::class,
        'alias' => '',
        'aliased_properties' => [],
        'hide_properties' => [],
        'id_properties' => [
            'postId',
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
        'curies' => [
            'name' => 'example',
            'href' => "http://example.com/docs/rels/{rel}",
        ]
    ],
    [
        'class' => UserId::class,
        'alias' => '',
        'aliased_properties' => [],
        'hide_properties' => [],
        'id_properties' => [
            'userId',
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
        'curies' => [
            'name' => 'example',
            'href' => "http://example.com/docs/rels/{rel}",
        ]
    ],
];

$mapper = new Mapper($mappings);
```

Calling the transformer will output a **valid HAL+JSON response** using the correct formatting:

```php
use NilPortugues\Api\HalJson\HalJsonTransformer; 
use NilPortugues\Api\HalJson\Http\Message\Response;
use NilPortugues\Serializer\Serializer;

$transformer = new HalJsonTransformer($mapper);

//Output transformation
$serializer = new Serializer($transformer);
$serializer->setSelfUrl('http://example.com/posts/9');
$serializer->setNextUrl('http://example.com/posts/10');
$serializer->addMeta('author',[['name' => 'Nil Portugués Calderó', 'email' => 'contact@nilportugues.com']]);

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

**Output:**

```
HTTP/1.1 200 OK
Cache-Control: private, max-age=0, must-revalidate
Content-type: application/hal+json
```

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
        "next": {
            "href": "http://example.com/posts/10"
        },
        "example:author": {
            "href": "http://example.com/users/1"
        },
        "example:comments": {
            "href": "http://example.com/posts/9/comments"
        }
    },
    "_meta": {
        "author": [
            {
                "name": "Nil Portugués Calderó",
                "email": "contact@nilportugues.com"
            }
        ]
    }
}
```


#### Response objects

The following PSR-7 Response objects providing the right headers and HTTP status codes are available:

- `NilPortugues\Api\HalJson\Http\Message\ErrorResponse($json)`
- `NilPortugues\Api\HalJson\Http\Message\ResourceCreatedResponse($json)`
- `NilPortugues\Api\HalJson\Http\Message\ResourceDeletedResponse($json)`
- `NilPortugues\Api\HalJson\Http\Message\ResourceNotFoundResponse($json)`
- `NilPortugues\Api\HalJson\Http\Message\ResourcePatchErrorResponse($json)`
- `NilPortugues\Api\HalJson\Http\Message\ResourcePostErrorResponse($json)`
- `NilPortugues\Api\HalJson\Http\Message\ResourceProcessingResponse($json)`
- `NilPortugues\Api\HalJson\Http\Message\ResourceUpdatedResponse($json)`
- `NilPortugues\Api\HalJson\Http\Message\Response($json)`
- `NilPortugues\Api\HalJson\Http\Message\UnsupportedActionResponse($json)`


## Quality

To run the PHPUnit tests at the command line, go to the tests directory and issue phpunit.

This library attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/).

If you notice compliance oversights, please send a patch via [Pull Request](https://github.com/nilportugues/hal-json-transformer/pulls).



## Contribute

Contributions to the package are always welcome!

* Report any bugs or issues you find on the [issue tracker](https://github.com/nilportugues/hal-json-transformer/issues/new).
* You can grab the source code at the package's [Git repository](https://github.com/nilportugues/hal-json-transformer).



## Support

Get in touch with me using one of the following means:

 - Emailing me at <contact@nilportugues.com>
 - Opening an [Issue](https://github.com/nilportugues/hal-json-transformer/issues/new)
 - Using Gitter: [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/nilportugues/hal-json-transformer?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)



## Authors

* [Nil Portugués Calderó](http://nilportugues.com)
* [The Community Contributors](https://github.com/nilportugues/hal-json-transformer/graphs/contributors)


## License
The code base is licensed under the [MIT license](LICENSE).
