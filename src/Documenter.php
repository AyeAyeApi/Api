<?php
/**
 * Documenter.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright 2015 Daniel Mason
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

/**
 * Class Documenter
 * Parses PHP doc blocks
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Documenter
{

    /**
     * Breaks the doc block of the given method into its component parts
     * @param \ReflectionMethod $method
     * @return array
     */
    public function getMethodDocumentation(\ReflectionMethod $method)
    {
        $comment = $this->getMethodComment($method);

        $summary     = $this->getSummary($comment);
        $parameters  = $this->getParameters($comment);
        $returnType  = $this->getReturnType($comment);

        $documentation = [];
        if ($summary) {
            $documentation['summary'] = $summary;
            // If there is no Summary, there CAN NOT be a Description
            $description = $this->getDescription($comment);
            if ($description) {
                $documentation['description'] = $description;
            }
        }

        // These should always show even if they're empty
        $documentation['parameters'] = $parameters;
        $documentation['returnType'] = $returnType;

        return $documentation;
    }

    /**
     * Get the doc block comment in front of a method and remove the surrounding asterisks
     * @param \ReflectionMethod $method
     * @return string[]
     */
    protected function getMethodComment(\ReflectionMethod $method)
    {
        $lines = preg_split("/((\r?\n)|(\r\n?))/", $method->getDocComment());
        $count = count($lines);
        foreach ($lines as $i => $line) {
            $line = preg_replace('/^\s*(\/\*\*|\*\/?)\s*/', '', $line);
            $line = trim($line);
            $lines[$i] = $line;
            if (!$line && (!$i || $i == $count-1)) { // If first or last lines are blank
                unset($lines[$i]);
            }
        }

        return array_values($lines);

    }

    /**
     * Gets the summary of a method.
     * Looks at the main comment of a doc block, and returns the string up to the first full stop at a line ending or
     * double line break.
     * @param string[] $lines
     * @return string
     */
    protected function getSummary(array $lines)
    {
        $summary = '';
        foreach ($lines as $line) {
            // Check for blank line
            if (!$line) {
                // If summary exists break out
                if ($summary) {
                    break;
                }
                continue;
            }

            // Check for tag
            if ($line[0] == '@') {
                break;
            }

            // Otherwise we're good for summary
            $summary .= $line."\n";
            if (substr($line, -1) == '.') {
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

        foreach ($lines as $line) {
            if ($line && !$summaryPassed) {
                $summaryFound = true;
                if (substr(trim($line), -1) == '.') {
                    $summaryPassed = true;
                }
                continue;
            }
            if (!$line && $summaryFound && !$summaryPassed) {
                $summaryPassed = true;
                continue;
            }
            if ($line && $line[0] == '@') {
                break;
            }
            if ($line && $summaryPassed) {
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
        if (isset($paramsDoc[1])) {
            foreach ($paramsDoc[1] as $paramDoc) {
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
                foreach ($lines as $key => $value) {
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

    /**
     * Gets the return types from a method or function's comment.
     * This method assumes multiple possible return types split with | so returns an array.
     * It will also replace '$this' with 'self' to (somewhat) hide internals
     * @param array $lines
     * @return string[]
     */
    protected function getReturnType(array $lines)
    {
        foreach ($lines as $line) {
            if (strpos($line, '@return') === 0) {
                $type = trim(str_replace('@return', '', $line));
                $type = str_replace('$this', 'self', $type);
                $type = explode('|', $type);
                return $type;
            }
        }
        return [];
    }
}
