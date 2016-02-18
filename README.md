# Aye Aye API

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.4-8892BF.svg)]
(https://php.net/)
[![License](https://img.shields.io/packagist/l/ayeaye/api.svg)]
(https://raw.githubusercontent.com/AyeAyeApi/Api/master/LICENSE.txt)
[![Version](https://img.shields.io/packagist/v/ayeaye/api.svg)]
(https://packagist.org/packages/ayeaye/api)
[![Build Status](https://img.shields.io/travis/AyeAyeApi/Api/master.svg)]
(https://travis-ci.org/AyeAyeApi/Api/branches)

Aye Aye API is a micro framework for building API's written in PHP. It's designed to be easy to use, fast to
develop with and to scale from tiny micro-services to gargantuan behemoths.

## Installation

Create a project and include Aye Aye

```bash
composer init --require="ayeaye/api ^1.0.0" -n
composer install
```

## Quick Start Guide

When working with Aye Aye, you will do almost all of your work in Controller classes.

Here's our ubiquitous Hello World controller:

```php
<?php
// HelloWorldController.php

use AyeAye\Api\Controller;

class HelloWorldController extends Controller
{
    /**
     * Says hello
     * @param string $name Optional, defaults to 'Captain'
     * @returns string
     */
    public function getHelloEndpoint($name = 'Captain')
    {
        return "Aye Aye $name";
    }
}
```

Controllers contain endpoints and child controllers. The above controller has a single endpoint `hello` that will 
respond to HTTP GET requests. This is reflected in the name which takes the form `[verb][Name]Endpoint`.

The endpoint takes one parameter, `name`, which will default to `'Captain'` if not otherwise provided. The return is
a string.

The API needs an entry point, which will put in index.php

```php
<?php
// index.php

require_once '../vendor/autoload.php';
require_once 'HelloWorldController.php';

use AyeAye\Api\Api;

$initialController = new HelloWorldController();
$api = new Api($initialController);

$api->go()->respond();
```

First we grab composer's autoloader, and our controller (which we haven't added to the autoloader). We instantiate our
HelloWorldController, and pass it into the constructor of our Api object. This becomes our initialController, and it's
the only one Aye Aye needs to know about, we'll come onto why later.

Finally the `->go()` method produces a Response object, with which we can `->respond()`.

We can test this using PHP's build in server:

```bash
$ php -S localhost:8000 index.php &

$ curl localhost:8000/hello                 // {"data":"Aye Aye Captain"}
$ curl localhost:8000/hello?name=Sandwiches // {"data":"Aye Aye Sandwiches"}
```

Notice how the string has been converted into a serialised object (JSON by default but the format can be selected with
an `Accept` header or a file suffix).

That tests our endpoint, but what happens if you just query the root of the Api.

```
$ curl localhost:8000 // {"data":{"controllers":[],"endpoints":{"get":{"hello":{"summary":"Says hello","parameters":{"name":{"type":"string","description":"Optional, defaults to 'Captain'"}},"returnType":["s string"]}}}}}
```

We'll explain this later on.

Don't forget to close the server down when you're done

```bash
$ fg
^C
```

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

By default it can read and format data as json (the default) and xml. It also reads GET, POST and HEADER. Data from
these sources is passed into your methods for you. In the traditional computational model `input -> process -> output`
Aye Aye takes care of the in and out, you just have to worry about the process!

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

Controllers are classes that extend `AyeAye\Api\Controller`

Endpoints are methods on the controllers that are named in this way `[verb][name]Endpoint(...)`

- The `[verb]` is the http verb, the endpoint is waiting for.
- The `[name]` is the name of the endpoint.
- `Endpoint` is literally the word "Endpoint". It helps us know what we're dealing with.

You can put define any parameters you like for the method, and Aye Aye will automatically populate them for you.

Controllers can also reference other controllers with methods named like this `[name]Controller()`

These should return a controller object, and it's how Aye Aye navigates the Api.

### The hello world controller

Above we wrote a controller to say hello.

```php
<?php
// HelloWorldController.php

use AyeAye\Api\Controller;

class HelloWorldController extends Controller
{
    /**
     * Says hello
     * @param string $name Optional, defaults to 'Captain'
     * @returns string
     */
    public function getHelloEndpoint($name = 'Captain')
    {
        return "Aye Aye $name";
    }
}
```

The one method in this controller tells you everything you need to know.
 * It will respond to a GET request send to the hello endpoint. 
 * It takes one parameter, 'name', which will default to Captain
 * It returns a string
 
So how did we go from that, to sending and receiving the data with curl?

When we created the Api object, we passed it HelloWorldController as a parameter, this tells the Api this is our
starting point. The Aye Aye identifies getHelloEndpoint as an endpoint called "hello" that is triggered with a GET
request.

You'll notice that we used a PHP Doc Block to explain what the method does. This is _really_ important. Not only does
it tell other developers what this end point does... it tells your API's users too!

Going back to the Quick Start guide, you might have tried querying "/", and you will have seen that the Api tells you
it has one GET endpoint, called 'hello, that it takes one parameter, as string called name, and it described all
of these things with the documentation you made for the method!

That's right, the API is truly self documenting!

### Child Controllers

Obviously just having one controller is pretty useless. To go from one controller to the next, we use the 
`[name]Controller()` method. This method should return another object that extends Controller. To demonstrate that in
our application quick start application, we can just return `$this`.
 
```php
<?php
// HelloWorldController.php

use AyeAye\Api\Controller;
 
class HelloWorldController extends Controller
{
    /**
     * Says hello
     * @param string $name Optional, defaults to 'Captain'
     * @returns string
     */
    public function getHelloEndpoint($name = 'Captain')
    {
        return "Aye Aye $name";
    }
    
    /**
     * lol...
     * @returns $this
     */
    public function ayeController()
    {
        return $this;
    }
}
```
 
Now when we start our application and the fun begins!

```bash
$ php -S localhost:8000 public/index.php &
curl localhost:8000/aye/aye/aye/aye/hello?name=Aye%20Aye
```

## Contributing

Aye Aye is an Open Source project and contributions are very welcome.

### Issues

To report problems, please open an Issue on the [GitHub Issue Tracker](https://github.com/AyeAyeApi/Api/issues).

### Changes

To make changes, clone the repository and use `composer install` with the developer dependencies. 

Branch from `develop`. We (now) use the Git Flow naming convention with date of creation (YYYY-MM-DD). See [this guide]
(http://nvie.com/posts/a-successful-git-branching-model/) for how Git Flow works.

We follow the [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) and
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding standards.
PHPMD and PHPCS, and their rule files will help guide you in this.
