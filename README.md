# Aye Aye API

[![PHP >= 5.5](https://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg)]
(https://php.net/)
[![License: MIT](https://img.shields.io/packagist/l/ayeaye/api.svg)]
(https://raw.githubusercontent.com/AyeAyeApi/Api/master/LICENSE.txt)
[![Version](https://img.shields.io/packagist/v/ayeaye/api.svg)]
(https://packagist.org/packages/ayeaye/api)
[![Build Status](https://img.shields.io/travis/AyeAyeApi/Api/master.svg)]
(https://travis-ci.org/AyeAyeApi/Api/branches)

Aye Aye API is a micro framework for building API's written in PHP. It's designed to be easy to use, fast to
develop with and to scale from tiny micro-services to gargantuan behemoths.

## Installation

Create a project and include Aye Aye.

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
     * Yo ho ho
     * @param string $name Optional, defaults to 'Captain'
     * @return string
     */
    public function getAyeAyeEndpoint($name = 'Captain')
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

$ curl localhost:8000/aye-aye                 # {"data":"Aye Aye Captain"}
$ curl localhost:8000/aye-aye?name=Sandwiches # {"data":"Aye Aye Sandwiches"}
```

Notice how the string has been converted into a serialised object (JSON by default but the format can be selected with
an `Accept` header or a file suffix).

That tests our endpoint, but what happens if you just query the root of the Api.

```bash
$ curl localhost:8000 # {"data":{"controllers":[],"endpoints":{"get":{"aye-aye":{"summary":"Yo ho ho","parameters":{"name":{"type":"string","description":"Optional, defaults to 'Captain'"}},"returnType":["string"]}}}}} 
```

Lets take take a closer look at that returned string. 

```json
{
  "data": {
    "controllers": [],
    "endpoints": {
      "get": {
        "aye-aye": {
          "summary": "Yo ho ho",
          "parameters": {
            "name": {
              "type": "string",
              "description": "Optional, defaults to 'Captain'"
            }
          },
          "returnType": [
            "string"
          ]
        }
      }
    }
  }
}
```
As you can see, it is an explanation of how our controller is structured. We didn't write anything more than what was
expected of us, and it makes sense to both the back end developers, and the consumers of your Api.

Don't forget to close the server down when you're done.

```bash
$ fg
^C
```

## Why should you use it?

Developing in Aye Aye is simple, clean and logical. Aye Aye processes requests and gives them to the appropriate
endpoint on the appropriate controller. That endpoint is simply a method, that takes a set of parameters and returns
some data. Aye Aye will work out where to find those parameters in the request, and it will format the data on return. 
It even supports multiple data formats and will automatically switch based on what the user requests.

There's no fluff. You don't need to learn new database tools, or logging interfaces (assuming you know [PSR-3]
(https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md), and you should) or
authentication methods. Aye Aye only provides routing, request parsing and response handling. You can use whatever
you like for the rest.

If you follow [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md), then
your API will look a lot like your directory structure, making maintenance a breeze.

Aye Aye knows about itself. It knows what endpoints and what sub-controllers are available on any given controller, and
by reading the documentation in the doc-block comments, it can tell users what those end points do. You only need to 
write your documentation once, and Aye Aye will read it and tell your users what those end points do, and what
parameters they take.

By default it can read and write data as json (the default) and xml, though more formats can easily be added. It also
reads GET, POST and HEADER, and parametrises url slugs. Data from these sources is passed into your methods for you.

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
     * Yo ho ho
     * @param string $name Optional, defaults to 'Captain'
     * @return string
     */
    public function getAyeAyeEndpoint($name = 'Captain')
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

When we created the Api object, we passed it a HelloWorldController object as a parameter, this tells the Api this is 
our starting point. The Aye Aye identifies getAyeEndpoint as an endpoint called "aye" that is triggered with a GET
request.

You'll notice that we used a PHP Doc Block to explain what the method does. This is _really_ important. Not only does
it tell other developers what this end point does... it tells your API's users too, and they'll be using it in just the
same way.

In the quick start guide we queryied "/", and you will have seen that the Api tells you it has one GET endpoint,
called 'aye, that it takes one parameter, as string called name, and it described all of these things with the 
documentation you made for the method!

### Child Controllers

Obviously just having one controller is pretty useless. To go from one controller to the next, we create a
`[name]Controller()` method. This method should return another object that extends Controller. To demonstrate that in
our application quick start application, we can just return `$this`.
 
```php
<?php
// HelloWorldController.php

use AyeAye\Api\Controller;
 
class HelloWorldController extends Controller
{
    /**
     * Yo ho ho
     * @param string $name Optional, defaults to 'Captain'
     * @returns string
     */
    public function getAyeAyeEndpoint($name = 'Captain')
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
curl localhost:8000/aye/aye/aye/aye/aye-aye?name=Aye%20Aye # {"data":"Aye Aye Aye Aye"}
```

## Contributing

Aye Aye is an Open Source project and contributions are very welcome.

### Issues

To report problems, please open an Issue on the [GitHub Issue Tracker](https://github.com/AyeAyeApi/Api/issues).

### Changes

Send me pull requests. Send me lots of them.

We follow the [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) and
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding standards.
PHPMD and PHPCS, and their rule files will help guide you in this.
