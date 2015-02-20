# Aye Aye API

Development Build Status:

Travis CI: [![Build Status](https://travis-ci.org/AyeAyeApi/Api.svg?branch=master)](https://travis-ci.org/AyeAyeApi/Api)
[Report](https://travis-ci.org/AyeAyeApi/Api)

## What is it?

Aye Aye API is a light weight framework for building API's, and we mean _light_. 

While we describe it as a framework, we haven't included any fluff. Aye Aye is focused on sending and receiving data,
and leaves processing that data entirely up to you.

It's designed to be easy to use, fast to develop with and to scale from tiny projects to world devouring titans.

## Installation

Currently, Aye Aye only supports installation via [Composer](https://github.com/composer/composer), and requires PHP 5.4
or above. Aye Aye should also work with [HHVM](https://github.com/facebook/hhvm) though this isn't actively supported.

To include Aye Aye in your Composer project we recommend using the following.

```
composer require --prefer-dist "AyeAye/Api 0.13.*"
```

__Important__: While Aye Aye has stable and usable releases, it's version 1 release has not yet been finalised. Minor
version increments (see [Semantic Versioning](http://semver.org)) may break backwards compatibility.

## Quick Start

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

class HelloWorldController extend Controller
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