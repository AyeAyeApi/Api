<?php
/**
 * Api.php
 * @author    Daniel Mason <daniel@ayeayeapi.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@ayeayeapi.com>
 * @license   MIT
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

use AyeAye\Api\Injector\LoggerInjector;
use AyeAye\Api\Injector\RequestInjector;
use AyeAye\Api\Injector\ResponseInjector;
use AyeAye\Api\Injector\RouterInjector;
use AyeAye\Api\Injector\WriterFactoryInjector;
use AyeAye\Formatter\Writer\Json;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

/**
 * Class Api
 * Quick start your API with this class
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Api implements LoggerAwareInterface
{
    use LoggerInjector;
    use RequestInjector;
    use ResponseInjector;
    use RouterInjector;
    use WriterFactoryInjector;

    /**
     * The starting controller for the Api
     * @var Controller
     */
    protected $controller;

    /**
     * Set up the API
     *
     * Initialise the API with a controller that forms the starting point of
     * routing information.
     *
     * @param Controller      $initialController The starting point for the Api
     */
    public function __construct(Controller $initialController)
    {
        $this->controller = $initialController;
    }

    /**
     * Write to the logger.
     *
     * Implementing the PSR LogAware interface.
     *
     * @param integer $level
     * @param string  $message
     * @param array   $context
     * @return $this
     */
    protected function log($level, $message, array $context = array())
    {
        $this->getLogger()->log($level, $message, $context);
        return $this;
    }

    /**
     * Process the request, get a response and return it.
     *
     * Exceptions thrown in most places will be handled here.
     * Currently there's no nice way to handle exceptions in Response::respond
     *
     * @return Response
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function go()
    {
        $response = $this->getResponse();

        try {
            $request = $this->getRequest();
            $response->setWriterFactory(
                $this->getWriterFactory()
            );
            $response->setRequest(
                $request
            );
            $response->setBodyData(
                $this->getRouter()->processRequest(
                    $this->getRequest(),
                    $this->controller
                )
            );
            $response->setStatus(
                $this->controller->getStatus()
            );
        } catch (Exception $e) {
            $this->log(LogLevel::INFO, $e->getPublicMessage());
            $this->log(LogLevel::ERROR, $e->getMessage(), ['exception' => $e]);
            $response->setBodyData($e->getPublicMessage());
            $response->setStatus(new Status($e->getCode()));
        } catch (\Exception $e) {
            $status = new Status(500);
            $this->log(LogLevel::CRITICAL, $e->getMessage(), ['exception' => $e]);
            $response->setBodyData($status->getMessage());
            $response->setStatus($status);
        }

        // Ultimate fail safe
        try {
            $response->prepareResponse();
        } catch (\Exception $e) {
            $this->log(LogLevel::CRITICAL, $e->getMessage(), ['exception' => $e]);
            return $this->createFailSafeResponse();
        }

        return $response;
    }

    /**
     * Returns a standardised 500 error.
     *
     * To be used in the event of a catastrophic failure, this method creates
     * all new objects, ignoring dependency injection and returns in JSON.
     *
     * This will be problematic for users expecting a response in a format
     * other than JSON and should only be called if the format they are
     * actually expecting can not be provided when using
     * Response::prepareResponse.
     *
     * @return Response
     */
    protected function createFailSafeResponse()
    {
        $status = new Status(500);
        $response = new Response();
        $response->setRequest(new Request());
        $response->setWriter(new Json());
        $response->setStatus($status);
        $response->setBodyData($status->getMessage());
        return $response;
    }
}
