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

    protected function getMethodComment(\ReflectionMethod $method)
    {
        $lines = preg_split("/((\r?\n)|(\r\n?))/", $method->getDocComment());
        $count = count($lines);
        foreach($lines as $i => $line) {
            $line = preg_replace('/^\s*(\/\*\*|\*\/?)\s*/', '', $line);
            $line = trim($line);
            $lines[$i] = $line;
            if(!$line && (!$i || $i == $count-1)) { // If first or last lines are blank
                unset($lines[$i]);
            }
        }

        return array_values($lines);

    }

    /**
     * Gets the summary of a method.
     * Looks at the main comment of a docblock, and returns the string up to the first full stop at a line ending or
     * double line break.
     * @param string[] $lines
     * @return string
     */
    protected function getSummary(array $lines)
    {
        $summary = '';
        foreach($lines as $i => $line) {
            // Check for blank line
            if(!$line) {
                // If summary exists break out
                if($summary) {
                    break;
                }
                continue;
            }

            // Check for tag
            if($line[0] == '@') {
                break;
            }

            // Otherwise we're good for summary
            $summary .= $line."\n";
            if(substr($line, -1) == '.') {
                break;
            }
        }

        return trim($summary);
    }

    /**
     * Gets a methods description.
     * This is an example of a description. In PSR-5 it follows the summary.
     * @param string[] $lines
     * @return string
     */
    protected function getDescription(array $lines)
    {
        $description = '';
        $summaryFound = false;
        $summaryPassed = false;

        foreach($lines as $line) {
            if($line && !$summaryPassed) {
                $summaryFound = true;
                if(substr(trim($line), -1) == '.') {
                    $summaryPassed = true;
                }
                continue;
            }
            if(!$line && $summaryFound && !$summaryPassed) {
                $summaryPassed = true;
                continue;
            }
            if($line && $line[0] == '@') {
                break;
            }
            if($line && $summaryPassed) {
                $description .= $line."\n";
            }
        }
        return trim($description);
    }

    /**
     * Gets the parameters of a method.
     * Returns a keyed array with parameter name as the key, containing another array with 'type' and 'description'.
     * @param string[] $lines
     * @return array
     */
    protected function getParameters(array $lines)
    {
        $comment = implode("\n", $lines);
        preg_match_all('/@param\s([\s\S]+?(?=@))/', $comment, $paramsDoc);

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