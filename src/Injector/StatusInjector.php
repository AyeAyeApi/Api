<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 07/02/2016
 * Time: 22:47
 */

namespace AyeAye\Api\Injector;

use AyeAye\Api\Status;

trait StatusInjector
{
    /**
     * @var Status
     */
    private $status;

    /**
     * @return Status
     */
    public function getStatus()
    {
        if (!$this->status) {
            $this->status = new Status();
        }
        return $this->status;
    }

    /**
     * @param Status $status
     * @return $this
     */
    public function setStatus(Status $status)
    {
        $this->status = $status;
        return $this;
    }
}
