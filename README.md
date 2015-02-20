# Aye Aye API

Development Build Status:

Travis CI: [![Build Status](https://travis-ci.org/AyeAyeApi/Api.svg?branch=master)](https://travis-ci.org/AyeAyeApi/Api)
[Report](https://travis-ci.org/AyeAyeApi/Api)

## Super Turbo Quick Start

Create a project and include Aye Aye

```bash
composer init --require="ayeaye/api 0.13.*" -n
composer install
mkdir public
mkdir src
```

Write your first controller

```php
<?php
// src/HelloWorldController.php

use AyeAye\Api\Controller;

class HelloWorldController extends Controller
{
    /**
     * Says hello
     * @param string $name Optional, defaults to 'World'
     * @returns string
     */
    public function getHelloEndpoint($name = 'World')
    {
        return "Hello $name";
    }
}
```

Write an entry point into the API

```php
<?php
// public/index.php

require_once '../vendor/autoload.php';
require_once '../src/HelloWorldController.php';

use AyeAye\Api\Api;

$initialController = new HelloWorldController();
$api = new Api($initialController);

$api->go()->respond();
```

Enjoy

```bash
$ php -S localhost:8000 public/index.php &
$ curl localhost:8000/hello
$ curl localhost:8000/hello?name=Aye%20Aye
```

Don't forget to close the server down when you're done

```bash
$ fg
^C
```

## What is it?

Aye Aye API is a micro framework for building API's, and we mean _micro_.

It's designed to be easy to use, fast to develop with and to scale from tiny projects to world devouring titans.

## Why should you use it?

Developing in Aye Aye is simple, clean and logical. Aye Aye processes requests and gives them to the appropriate
endpoint on the appropriate controller. You write the code to process the data and return the result. That's it.
Seriously.

There's no fluff. You don't need to learn new database tools, or logging interfaces (assuming you know [PSR-3]
(https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md), and you should) or
authentication methods.

If you follow [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), then
your API will look a lot like your directory structure, making maintainence a breeze.

Aye Aye is self aware... though not in the scary killer robot way. It knows about itself. It knows what endpoints
and what sub-controllers are available on any given controller, and by reading the documentation in the doc-block
comments, it can tell users what those end points do! You only need to write your documentation once, for developers,
and Aye Aye will read it and tell your users what those end points do, and what parameters they take.

## Installation

Currently, Aye Aye only supports installation via [Composer](https://github.com/composer/composer), and requires PHP 5.4
or above. Aye Aye should also work with [HHVM](https://github.com/facebook/hhvm) though this isn't actively supported.

To include Aye Aye in your Composer project we recommend using the following.

```bash
composer require --prefer-dist "AyeAye/Api 0.13.*"
```

__Important__: While Aye Aye has stable and usable releases, it's version 1 release has not yet been finalised. Minor
version increments (see [Semantic Versioning](http://semver.org)) may break backwards compatibility.

## Quick Start explained

The most important and powerful feature of Aye Aye is it's controllers. Controllers do two things. They provide
endpoints, and access to other controllers.

Controllers are classes that extend `AyeAye\Api\Controller'

Endpoints are methods on the controllers that are named in this way `[verb][name]Endpoint(...)`

- The `[verb]` is the http verb, the endpoint is waiting for.
- The `[name]` is the name of the endpoint.
- `Endpoint` is literally the word "Endpoint". It helps us know what we're dealing with.

You can put define any parameters you like for the method, and Aye Aye will automatically populate them for you.

### My First Controller


```php
<?php

use AyeAye\Api\Controller;

class HelloWorldController extends Controller
{
    /**
     * Says hello
     * @param string $name Optional, defaults to 'World'
     * @returns string
     */
    public function getHelloAction($name = 'World')
    {
        return "Hello $name";
    }
}
```

You'll notice that we used a PHP Doc Block to explain what the method does. This is _really_ important. Not only does
it tell other developers what this end point does... it tells your API's users too!

That's right, the API is truly self documenting!

### Starting the API

To use this controller we need to pass it a request. For this we can use the `AyeAye\Api\Api` class. This class is
really just wrapping together the other classes, so you don't have to use it but for most use cases this will suffice.

To use the `Api` class, we initialise it with our starting controller. This controller will be the entry point into the
rest of the api.

```php
<?php

use AyeAye\Api\Api;

$initialController = new HelloWorldController();
$api = new Api($initialController);

$api->go()->respond();
```

And that's it! We just made a RESTful Api in next to no time. Lets try it out.

### Seeing it work

As a quick test, we can test it out with the built in php server...