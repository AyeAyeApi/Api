<?php
// index.php

require_once '../vendor/autoload.php';
require_once 'HelloWorldController.php';

use AyeAye\Api\Api;
use Psr\Log\AbstractLogger;

class EchoLogger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        echo $message.PHP_EOL;
        $this->logArray($context);
    }

    public function logArray($array, $indent = '  ')
    {
        foreach($array as $key => $value) {
            if(!is_scalar($value)) {
                echo $indent.$key.':'.PHP_EOL;
                $this->logArray($value, $indent.'  ');
                continue;
            }
            echo $indent.$key.': '.$value;
        }
    }
}

$initialController = new HelloWorldController();
$api = new Api($initialController);
$api->setLogger(new EchoLogger);

$api->go()->respond();

