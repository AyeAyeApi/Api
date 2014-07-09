<?php
/**
 *
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Api;


class Controller {

    /**
     * Controllers that this API links to
     * @var Controller[]
     */
    protected $children = array();

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string[]
     */
    protected $requestChain;

    public function __construct(Request $request, array $requestChain = array()) {
        $this->request = $request;
        $this->requestChain = $requestChain;
    }

    public function process() {



    }

    public function getIndexAction() {

    }

}