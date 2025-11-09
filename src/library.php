<?php

/**
 * explode_enclosed
 */
if(!function_exists('explode_enclosed'))
{
    function explode_enclosed(string $open, string $close, string $string, int $offset = 0, bool $startPosAsIndex = false, bool $includeOpenClose = false, bool $multiByteSafe = false)
    {
        // Set functions
        $strpos = $multiByteSafe ? 'mb_strpos' : 'strpos';
        $substr = $multiByteSafe ? 'mb_substr' : 'substr';
        $strlen = $multiByteSafe ? 'mb_strlen' : 'strlen';

        // Get open and close length
        $openLength = $strlen($open);
        $closeLength = $strlen($close);

        // Get open pos
        $openPos = $strpos($string, $open, $offset);

        // Check open pos
        if($openPos === false)
            return [];

        // Increment openPos
        $searchPos = $openPos + $openLength;

        // Do while balance is not found
        do
        {
            // Get close pos
            $closePos = $strpos($string, $close, $searchPos);
            
            // Check close pos
            if($closePos === false)
                return [];
            
            // Increment closePos to include close delimiter
            $closePos += $closeLength;
            
            // Get content 
            $content = $substr($string, $openPos, $closePos - $openPos);

            // Set searchPos
            $searchPos = $closePos;
        }
        while((substr_count($content, $open) - substr_count($content, $close)) !== 0);

        // Return as is
        if($includeOpenClose)
            $finalContent = $content;

        // Extract content wrapped in open/close
        else
        {
            $finalContent = $substr($content, $openLength, $strlen($content) - $openLength - $closeLength);
        }

        // Return result
        if($startPosAsIndex)
        {
            $index = $includeOpenClose ? $openPos - $strlen($open) : $openPos;

            // Set array
            return array_replace(array($index => $finalContent), explode_enclosed($open, $close, $string, $searchPos, $startPosAsIndex, $includeOpenClose));
        }
        else
            return array_merge([$finalContent], explode_enclosed($open, $close, $string, $searchPos, $startPosAsIndex, $includeOpenClose));
    }
}

/**
 * replace_enclosed_function
 */
if(!function_exists('replace_enclosed_function'))
{
    function replace_enclosed_function(string $open, string $close, string $string, callable $function, bool $includeOpenClose = false, bool $multiByteSafe = false) : string
    {
        // Set function
        $strlen = $multiByteSafe ? 'mb_strlen' : 'strlen';

        // Get open length size
        $openLength = $strlen($open);    

        // Explode
        $explode = explode_enclosed($open, $close, $string, 0, true, $includeOpenClose, $multiByteSafe);

        // Check count
        if(!count($explode))
            return $string;

        // Iterate over results
        $offsetDelta = 0;
        foreach($explode as $pos => $match)
        {
            // Extract details
            $matchLength = $strlen($match);
            $offset = $pos + $offsetDelta;
            $stringLength = $strlen($string);

            // Create replacements
            $replacement = $function($match, $offset);
            
            // Perform replace
            $string = \substr_replace($string, $replacement, $offset + $openLength, $matchLength);

            // Add to delta
            $offsetDelta += ($strlen($string) - $stringLength);
        }

        // Return result
        return $string;
    }
}

/**
 * replace_enclosed_quotes
 * 
 *  Safely replaces 'search' with 'replace' in 'string' in order of enclosed quote appearance for quotes ",' and `.
 * 
 * Rationale:
 *  Function replace_enclosed cannot be used to safely replace content enclosed in quotes
 *  because the order of execution would create different output.
 * 
 *      Example string: "this string uses 'quote 1 `' as an example with 'quote 2 `' to illustrate the problem"
 *  
 *  Replacing content with ' as start and close delimiter will target 'quote 1 `' and 'quote 2 `'
 *  Replacing content with ` as start and close delimiter will target => `' as an example with 'quote 2 `
 * 
 *  Changing the execution order or replace_enclosed for both will create inconsistent results.
 *  Using replace_enclosed_quotes will always produce the same output
 * 
 *  Replacing quote content will always target 'quote 1 `' and 'quote 2 `'.
 * 
 */
if(!function_exists('replace_enclosed_quotes'))
{
    function replace_enclosed_quotes(string $string, string $search, string $replace, bool $multiByteSafe = false) : string
    {
        // Set offset in string
        $offset = 0;

        // Set functions
        $strpos = $multiByteSafe ? 'mb_strpos' : 'strpos';
        $substr = $multiByteSafe ? 'mb_substr' : 'substr';
        $strlen = $multiByteSafe ? 'mb_strlen' : 'strlen';

        // Escape escaped quotes with UTF-8 encoding
        $string = preg_replace("/(?<!\\\)\\\[\"]/", "0x5C0x22", $string);
        $string = preg_replace("/(?<!\\\)\\\[\']/", "0x5C0x27", $string);
        $string = preg_replace("/(?<!\\\)\\\[\`]/", "0x5C0x60", $string);

        // Replace
        do
        {
            // Set start and end of quotes
            $start = $strlen($string);
            $end = $strlen($string);

            // Get double quote starting pos
            $startDQuote = $strpos($string, '"', $offset);
            $endDQuote = $startDQuote === false ? false : $strpos($string, '"', $startDQuote + 1);

            // Check
            if($startDQuote !== false && $endDQuote !== false)
            {
                $start = $startDQuote;
                $end = $endDQuote;
            }
            
            // Get single quote starting pos
            $startSQuote = $strpos($string, "'", $offset);
            $endSQuote = $startSQuote === false ? false : $strpos($string, "'", $startSQuote + 1);

            // Check
            if($startSQuote !== false && $endSQuote !== false && $startSQuote < $start)
            {
                $start = $startSQuote;
                $end = $endSQuote;
            }

            // Get single quote starting pos
            $startGQuote = $strpos($string, "`", $offset);
            $endGQuote = $startGQuote === false ? false : $strpos($string, "`", $startGQuote + 1);

            // Check
            if($startGQuote !== false && $endGQuote !== false && $startGQuote < $start)
            {
                $start = $startGQuote;
                $end = $endGQuote;
            }

            // Check if start has been set
            if($start === null)
                return str_replace("0x5C0x22", '\"', str_replace("0x5C0x27", "\'", str_replace("0x5C0x60", "\`", $string)));

            // Increment start to avoid including the delimiter
            $start += 1;

            // Get current string length
            $currentLength = $strlen($string);

            // Replace
            $replacement = str_replace($search, $replace, $substr($string, $start, $end-$start));

            // Replace in string
            $string = substr_replace($string, $replacement, $start, $end - $start);

            // Get new string length
            $newStringLength = $strlen($string);

            // Set offset by using end with delta
            $offset = $end + ($newStringLength - $currentLength) + 1;
        } while($offset < $strlen($string));

        // Return
        return str_replace("0x5C0x22", '\"', str_replace("0x5C0x27", "\'", str_replace("0x5C0x60", "\`", $string)));
    }
}

/**
 * replace_enclosed
 *  Replaces search with replace in enclosed delimiters.
 *  When using quotes as delimiters, use replace_enclosed_quotes.
 */
if(!function_exists('replace_enclosed'))
{
    function replace_enclosed(string $open, string $close, string $string, string $search, string $replace, bool $multiByteSafe = false) : string
    {
        return replace_enclosed_function($open, $close, $string, function($match) use ($search, $replace) {
            return str_replace($search, $replace, $match);
        }, $multiByteSafe);
    }
}

/**
 * replace_placeholder_array
 */
if(!function_exists("replace_placeholder_array"))
{
    function replace_placeholder_array(array $keyValueArray, string $text, string $openSymbol = "%{", string $closeSymbol = "}") : string
    {
        foreach($keyValueArray as $key => $value)
            $text = replace_placeholder($key, $value, $text, $openSymbol, $closeSymbol);

        return $text;
    }
}

/**
 * replace_placeholder
 */
if(!function_exists("replace_placeholder"))
{
    function replace_placeholder(string $key, string $value, string $text, string $openSymbol = "%{", string $closeSymbol = "}") : string
    {
        return str_replace($openSymbol.$key.$closeSymbol, $value, $text);
    }
}

/**
 * placeholder_restore
 */
if(!function_exists("replace_placeholder"))
{
    function placeholder_restore(string $content, array $placeholders) : string
    {
        // Restore placeholder
        preg_match_all("/\{([0-9]+)\}/", $content, $matches);

        // Replace placeholders
        foreach($matches[1] as $placeholderIndex)
            $content = str_replace("{{$placeholderIndex}}", $placeholders[(int) $placeholderIndex], $content);

        // Return content
        return $content;
    }
}

/**
 * placeholder_replace
 *  Note: escape brackets before replacing placeholders
 */
if(!function_exists("placeholder_replace"))
{
    function placeholder_replace(string $open, string $close, string &$content, int $startIndex = 0, bool $multiByteSafe = false)
    {
        // Create temp content
        $tempContent = $content;

        // Explode parentheses, replace inner contents with placeholders
        $explode = \explode_enclosed($open, $close, $tempContent, 0, true, $multiByteSafe);

        // Set functions
        $substr = $multiByteSafe ? 'mb_substr' : 'substr';
        $strlen = $multiByteSafe ? 'mb_strlen' : 'strlen';

        // Correction
        $resultArray = array();
        $correction = 0;

        // Iterate over results
        foreach($explode as $pos => $result)
        {
            // Increase pos
            $pos += 1;
            
            // Determine position, correction accounts for pos shifts due to placeholde replacements
            $pos = $pos + $correction;

            // Get leading fragment
            $start = $substr($tempContent, 0, $pos);

            // Get trailing fragment
            $end = $substr($tempContent, $pos + $strlen($result));

            // Create placeholder
            $placeholder = "{{$startIndex}}";

            // Calculate difference
            $correction += $strlen($placeholder) - $strlen($result);

            // Set new content
            $tempContent = $start.$placeholder.$end;

            // Add result
            $resultArray[$startIndex] = $result;

            // Increment
            $startIndex++;
        }

        // Set content to temp content
        $content = $tempContent;

        // Return 
        return $resultArray;
    }
}

/**
 * typecast
 */
if(!function_exists("typecast"))
{
    function typecast($input)
    {
        return is_numeric($input) ? (((float) ((int) $input) === (float) $input) ? (int) $input : (float) $input) : $input;
    }
}

/**
 * unwrap
 */
if(!function_exists('unwrap'))
{
    function unwrap(string $input, string $start, string $end) : string
    {
        if(str_starts_with($input, $start))
            $input = substr($input, strlen($start));

        if(str_ends_with($input, $end))
            $input = substr($input, 0, -strlen($end));

        return $input;
    }
}

/**
 * wrap
 */
if(!function_exists('wrap'))
{
    function wrap(string $input, string $start, string $end) : string
    {
        if(!str_starts_with($input, $start))
            $input = "$start$input";

        if(!str_ends_with($input, $end))
            $input = "$input$end";

        return $input;
    }
}

/**
 * odd
 */
if(!function_exists("odd"))
{
    function odd(int $input)
    {
        return $input%2 == 1;
    }
}

/**
 * even
 */
if(!function_exists("even"))
{
    function even(int $input)
    {
        return !odd($input);
    }
}

/**
 * get_type
 * 
 *  @return string gettype() or object class name
 */
if(!function_exists("get_type"))
{
    function get_type($input) : string
    {
        return ($type = gettype($input)) === "object" ? get_class($input) : $type;
    }
}

/**
 * unwrap_quotes
 */
if(!function_exists('unwrap_quotes'))
{
    function unwrap_quotes(string $input) : string
    {
        $input = trim($input);
        return substr($input, 1, strlen($input) - 2);
    }
}

/**
 * is_wrapped_in_quotes
 */
if(!function_exists('is_wrapped_in_quotes'))
{
    function is_wrapped_in_quotes(string $input) : bool
    {
        // String cannot contain quotes
        if(strlen($input) < 2)
            return false;

        // Get first and last char
        $firstChar = $input[0];
        $lastChar = $input[strlen($input) - 1];

        // Not the same, false
        if($firstChar != $lastChar)
            return false;

        return in_array($firstChar, ["'",'"',"`"]);
    }
}

/**
 * str_must_start_with
 */
if(!function_exists('str_must_start_with'))
{
    function str_must_start_with(string $input, string $prefix)
    {
        if(str_starts_with($input, $prefix))
            return $input;

        return "$prefix$input";
    }
}


/**
 * str_must_not_start_with
 */
if(!function_exists('str_must_not_start_with'))
{
    function str_must_not_start_with(string $input, string $prefix)
    {
        if(!str_starts_with($input, $prefix))
            return $input;

        return substr($input, strlen($prefix));
    }
}

/**
 * str_must_end_with
 */
if(!function_exists('str_must_end_with'))
{
    function str_must_end_with(string $input, string $suffix)
    {
        if(str_ends_with($input, $suffix))
            return $input;

        return "$input$suffix";
    }
}

/**
 * str_must_not_end_with
 */
if(!function_exists('str_must_not_end_with'))
{
    function str_must_not_end_with(string $input, string $suffix)
    {
        if(!str_ends_with($input, $suffix))
            return $input;

        return substr($input, 0, strlen($input) - strlen($suffix));
    }
}

/**
 * str_must_start_end_with
 */
if(!function_exists('str_must_start_end_with'))
{
    function str_must_start_end_with(string $input, string $fix)
    {
        return str_must_start_with(str_must_end_with($input, $fix), $fix);
    }
}

/**
 * str_equals
 */
if(!function_exists('str_equals'))
{
    function str_equals($str1, $str2) : bool
    {
        return strcmp($str1, $str2) == 0;
    }
}

/**
 * str_starts_ends_with
 */
if(!function_exists('str_starts_ends_with'))
{
    function str_starts_ends_with(string $haystack, string $needle)
    {
        return str_starts_with($haystack, $needle) && str_ends_with($haystack, $needle);
    }
}

/**
 * cli_color
 *  Adds color to cli text
 */
if(!function_exists("cli_color"))
{
    function cli_color(string $text, string $color, null|string $background = null) : string
    {
        switch($color)
        {
            case 'black': $color = "\033[0;30m"; break;
            case 'dark_gray': $color = "\033[1;30m"; break;
            case 'blue': $color = "\033[0;34m"; break;
            case 'light_blue': $color = "\033[1;34m"; break;
            case 'green': $color = "\033[0;32m"; break;
            case 'light_green': $color = "\033[1;32m"; break;
            case 'cyan': $color = "\033[0;36m"; break;
            case 'light_cyan': $color = "\033[1;36m"; break;
            case 'red': $color = "\033[0;31m"; break;
            case 'light_red': $color = "\033[1;31m"; break;
            case 'purple': $color = "\033[0;35m"; break;
            case 'light_purple': $color = "\033[1;35m"; break;
            case 'yellow': $color = "\033[0;33m"; break;
            case 'light_yellow': $color = "\033[1;33m"; break;
            case 'light_gray': $color = "\033[0;37m"; break;
            case 'white': $color = "\033[1;37m"; break;
        }
        if($background !== null)
        {
            switch($background)
            {
                case 'black': $background = "\033[40m"; break;
                case 'red': $background = "\033[41m"; break;
                case 'green': $background = "\033[42m"; break;
                case 'yellow': $background = "\033[43m"; break;
                case 'blue': $background = "\033[44m"; break;
                case 'magenta': $background = "\033[45m"; break;
                case 'cyan': $background = "\033[46m"; break;
                case 'light_gray': $background = "\033[47m"; break;
                default: $background = "";
            }
            $color .= $background;
        }
        return $color . $text . "\033[0m";
    }
}

/**
 * cli_color_padded
 *  Adds padded color to cli text
 */
if(!function_exists("cli_color_padded"))
{
    function cli_color_padded(string $text, string $color, null|string $background = null, int $minWidth = 60, int $padding = 10) : int
    {
        // Fetch text length
        $textLength = strlen($text);
    
        // Determine width
        $width = $textLength + ($padding * 2);
    
        // Determine final width
        $width = $width <= $minWidth ? $minWidth : $width;
    
        // Recalculate padding
        $padding = ceil(($width - $textLength) / 2);
        
        // Print left padding
        $result = "";
        for($i = 0; $i < $padding; $i++) $result .= " ";
        print(cli_color($result, $color, $background));
    
        // Print text
        print(cli_color($text, $color, $background));
    
        // Print right padding
        $result = "";
        for($i = 0; $i < ($width - $padding - $textLength); $i++) $result .= " ";
        print(cli_color($result, $color, $background));
    
        // Print newline
        print("\n");
    
        return $width;
    }
}