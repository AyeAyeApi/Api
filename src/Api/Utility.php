<?php
/**
 * Utilities that don't really belong in the other classes
 * @author Daniel Mason
 * @copyright Daniel Mason, 2014
 */

namespace Gisleburt\Api;


class Utility {

    /**
     * Looks at the PHPDoc for the given method and returns an array of information about the parameters it takes
     * @param $class
     * @param $method
     * @return array
     */
    public static function getParametersFromDocumentation($class, $method) {
        $parameters = array();
        $reflationMethod = new \ReflectionMethod($class, $method);
        $doc = $reflationMethod->getDocComment();
        $nMatches = preg_match_all('/@param (\S+) \$?(\S+) ?([\S ]+)?/', $doc, $results);
        for($i = 0; $i < $nMatches; $i++) {
            $parameter = new \stdClass();
            $parameter->parameter = $results[2][$i];
            $parameter->type = $results[1][$i];
            if($results[3][$i])
                $parameter->description = $results[3][$i];
            $parameters[] = $parameter;
        }
        return $parameters;
    }

} 