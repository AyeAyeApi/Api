<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27/07/15
 * Time: 08:17
 */

namespace AyeAye\Api;


class Documentor
{

    /**
     * @param \ReflectionMethod $method The method you wish to
     * @return array
     */
    protected function getParameters(\ReflectionMethod $method)
    {
        $comment = $method->getDocComment();
        preg_match_all('/@param\s([\s\S]+?(?=\* @|\*\/))/', $comment, $paramsDoc);

        $params = [];
        if(isset($paramsDoc[1])) {
            foreach($paramsDoc[1] as $paramDoc) {
                // Break up the documentation into 3 parts:
                // @param type $name description
                // 1. The type
                // 2. The name
                // 3. The description
                $documentation = [];
                preg_match('/([^$]+)?\$(\w+)(.+)?/s', $paramDoc, $documentation);
                list(/*ignore*/, $type, $name, $description) = $documentation;


                // Clean up description
                // ToDo: Got to be a better way than this
                $lines = preg_split("/((\r?\n)|(\r\n?))/", $description);
                foreach($lines as $key => $value) {
                    $value = preg_replace('/\r/', '', $value);
                    $value = preg_replace('/^\s+\*/', '', $value);
                    $value = trim($value);
                    $lines[$key] = $value;
                }
                $description = implode("\n", $lines);

                // Sort out the values
                $params[$name] = [
                    'type'        => trim($type),
                    'description' => trim($description),
                ];
            }
        }

        return $params;
    }

}