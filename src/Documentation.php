<?php
/**
 * Documentation.php
 * @author    Daniel Mason <daniel@danielmason.com>
 * @copyright (c) 2015 - 2016 Daniel Mason <daniel@danielmason.com>
 * @license   GPL 3
 * @see       https://github.com/AyeAyeApi/Api
 */

namespace AyeAye\Api;

/**
 * Class Documentation
 *
 * Parses PHP DocBlocks of methods based on PSR-5.
 *
 * @package AyeAye/Api
 * @see     https://github.com/AyeAyeApi/Api
 */
class Documentation
{
    /**
     * Breaks the doc block of the given method into its component parts
     *
     * Takes a Reflection method and returns an array containing the summary,
     * and description (if they exist), as well as parameters and return type.
     *
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
     * Get a cleaned up version of the method comment.
     *
     * Reflection methods return the doc block with the surrounding stars still
     * in the string. This method breaks the continuous string into individual
     * lines, removes the starting asterisks, and trims the line. Finally the
     * first and last lines of the comment are removed if they are empty.
     *
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
            if (!$line && ($i == 0 || $i == $count - 1)) { // If first or last lines are blank
                unset($lines[$i]);
            }
        }

        return array_values($lines);

    }

    /**
     * Gets the summary of a method.
     *
     * Starting from the beginning of the doc block, this method extracts text
     * until it reaches a full stop at the end of a line, a blank line, or a
     * line beginning with an @ symbol.
     *
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
            $summary .= $line . "\n";
            if (substr($line, -1) == '.') {
                break;
            }
        }

        return trim($summary);
    }

    /**
     * Gets a methods description.
     *
     * Skips over the summary (as described above), then if there are further
     * lines before the first line that begins with an @, then this will
     * extract them as the description.
     *
     * This line and the paragraph above would all be extracted as description.
     *
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
                $description .= $line . "\n";
            }
        }
        return trim($description);
    }

    /**
     * Gets the parameters of a method.
     *
     * Returns a keyed array with parameter name as the key, containing
     * another array with 'type' and 'description'.
     *
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
                    'type' => trim($type),
                    'description' => trim($description),
                ];
            }
        }

        return $params;
    }

    /**
     * Gets the return types from a method or function's comment.
     *
     * This method will always return an array to allow for the possibility of
     * multiple return types split with | pipe. It will also replace '$this'
     * with 'self' to (somewhat) hide internals.
     *
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
