<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 01/03/2015
 * Time: 14:44
 */

namespace AyeAye\Api\Tests\TestData;

use Psr\Log\AbstractLogger;

class TestLogger extends AbstractLogger
{
    protected $logStorage= [];

    public function countLogs()
    {
        return count($this->logStorage);
    }

    public function log($level, $message, array $context = array())
    {
        $this->logStorage[] = [
            'level' => $level,
            'message' => $message,
//            'context' => $context
        ];
    }

    public function wasLogged($message, $level = null)
    {
        foreach ($this->logStorage as $log) {
            if ($log['message'] == $message) {
                if (is_null($level) || $log['level'] == $level) {
                    return true;
                }
            }
        }
        return false;
    }
}
