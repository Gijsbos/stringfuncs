<?php

/**
 * enclosed_ranges
 *  Returns the byte ranges [start, end) of balanced open/close pairs, start is the position of open, end is the position after close.
 *  Byte based searching is safe for UTF-8 input because a valid UTF-8 needle can never match inside another character.
 */
if(!function_exists('enclosed_ranges'))
{
    function enclosed_ranges(string $open, string $close, string $string, int $offset = 0) : array
    {
        // Get open and close length
        $openLength = strlen($open);
        $closeLength = strlen($close);

        // Collect ranges
        $ranges = [];

        // Iterate over open positions
        while(($openPos = strpos($string, $open, $offset)) !== false)
        {
            // Balance is counted per searched segment so every byte is only counted once
            $searchPos = $openPos + $openLength;
            $segmentStart = $openPos;
            $balance = 0;

            // Do while balance is not found
            do
            {
                // Get close pos
                $closePos = strpos($string, $close, $searchPos);

                // Unbalanced, stop searching
                if($closePos === false)
                    return $ranges;

                // Increment closePos to include close delimiter
                $closePos += $closeLength;

                // Update balance with the new segment
                $balance += substr_count($string, $open, $segmentStart, $closePos - $segmentStart) - substr_count($string, $close, $segmentStart, $closePos - $segmentStart);

                // Set next segment
                $searchPos = $segmentStart = $closePos;
            }
            while($balance !== 0);

            // Add range
            $ranges[] = [$openPos, $closePos];

            // Continue after close
            $offset = $closePos;
        }

        // Return result
        return $ranges;
    }
}

/**
 * explode_enclosed
 */
if(!function_exists('explode_enclosed'))
{
    function explode_enclosed(string $open, string $close, string $string, int $offset = 0, bool $startPosAsIndex = false, bool $includeOpenClose = false, bool $multiByteSafe = false)
    {
        // Get open and close length
        $openLength = strlen($open);
        $closeLength = strlen($close);

        // Convert character offset to byte offset
        if($multiByteSafe && $offset !== 0)
            $offset = strlen(mb_substr($string, 0, $offset));

        // Track byte/character positions to convert indexes in a single pass
        $bytePos = 0;
        $charPos = 0;

        // Iterate over ranges
        $result = [];
        foreach(enclosed_ranges($open, $close, $string, $offset) as [$start, $end])
        {
            // Get content
            $content = $includeOpenClose ? substr($string, $start, $end - $start) : substr($string, $start + $openLength, $end - $start - $openLength - $closeLength);

            // Add without index
            if(!$startPosAsIndex)
            {
                $result[] = $content;
                continue;
            }

            // Determine index
            if($multiByteSafe)
            {
                $charPos += mb_strlen(substr($string, $bytePos, $start - $bytePos));
                $bytePos = $start;
                $index = $includeOpenClose ? $charPos - mb_strlen($open) : $charPos;
            }
            else
                $index = $includeOpenClose ? $start - $openLength : $start;

            // Add result
            $result[$index] = $content;
        }

        // Return result
        return $result;
    }
}

/**
 * replace_enclosed_function
 */
if(!function_exists('replace_enclosed_function'))
{
    function replace_enclosed_function(string $open, string $close, string $string, callable $function, bool $includeOpenClose = false, bool $multiByteSafe = false) : string
    {
        // Get open and close length
        $openLength = strlen($open);
        $closeLength = strlen($close);

        // Build result in one pass
        $result = "";
        $resultChars = 0;
        $copiedPos = 0;
        foreach(enclosed_ranges($open, $close, $string) as [$start, $end])
        {
            // Determine match range
            $matchStart = $includeOpenClose ? $start : $start + $openLength;
            $matchEnd = $includeOpenClose ? $end : $end - $closeLength;

            // Copy leading fragment up to match
            $leading = substr($string, $copiedPos, $matchStart - $copiedPos);
            $result .= $leading;

            // Offset passed to function is the match position in the result minus the open length (kept for backwards compatibility)
            if($multiByteSafe)
            {
                $resultChars += mb_strlen($leading);
                $offset = $resultChars - mb_strlen($open);
            }
            else
                $offset = strlen($result) - $openLength;

            // Add replacement
            $replacement = (string) $function(substr($string, $matchStart, $matchEnd - $matchStart), $offset);
            $result .= $replacement;

            // Track characters of added content
            if($multiByteSafe)
                $resultChars += mb_strlen($replacement);

            // Set copied pos, close delimiter is copied with next fragment
            $copiedPos = $matchEnd;
        }

        // Return result
        return $result . substr($string, $copiedPos);
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
        // Quotes and backslashes are single byte ASCII, so byte scanning is UTF-8 safe; $multiByteSafe is kept for backwards compatibility
        $length = strlen($string);
        $result = "";
        $copiedPos = 0;
        $pos = 0;

        // Quote chars that have no unescaped closing quote after the current position
        $unclosed = [];

        // Scan for backslashes and quotes
        while($pos < $length && ($pos += strcspn($string, "\\\"'`", $pos)) < $length)
        {
            $char = $string[$pos];

            // Skip escaped char
            if($char === "\\")
            {
                $pos += 2;
                continue;
            }

            // Find closing quote, skipping escaped quotes
            $end = isset($unclosed[$char]) ? false : $pos;
            while($end !== false && ($end = strpos($string, $char, $end + 1)) !== false)
            {
                // Count preceding backslashes, an odd number means the quote is escaped
                $backslashes = 0;
                while($string[$end - $backslashes - 1] === "\\")
                    $backslashes++;

                if($backslashes % 2 === 0)
                    break;
            }

            // Unclosed quote is treated as a regular char
            if($end === false)
            {
                $unclosed[$char] = true;
                $pos++;
                continue;
            }

            // Replace content between quotes
            $result .= substr($string, $copiedPos, $pos + 1 - $copiedPos) . str_replace($search, $replace, substr($string, $pos + 1, $end - $pos - 1));

            // Continue at closing quote
            $pos = $copiedPos = $end;
            $pos++;
        }

        // Return
        return $result . substr($string, $copiedPos);
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
        }, false, $multiByteSafe);
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
if(!function_exists("placeholder_restore"))
{
    function placeholder_restore(string $content, array $placeholders) : string
    {
        // Restore placeholder
        preg_match_all("/\{([0-9]+)\}/", $content, $matches);

        // Replace placeholders
        foreach($matches[1] as $placeholderIndex)
            if(array_key_exists((int) $placeholderIndex, $placeholders))
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
    function placeholder_replace(string $open, string $close, string &$content, int &$startIndex = 0, bool $multiByteSafe = false)
    {
        // Byte ranges are UTF-8 safe, $multiByteSafe is kept for backwards compatibility
        $openLength = strlen($open);
        $closeLength = strlen($close);

        // Replace inner contents of enclosed parts with placeholders
        $resultArray = array();
        $newContent = "";
        $copiedPos = 0;
        foreach(enclosed_ranges($open, $close, $content) as [$start, $end])
        {
            // Get inner content range
            $innerStart = $start + $openLength;
            $innerEnd = $end - $closeLength;

            // Add leading fragment and placeholder
            $newContent .= substr($content, $copiedPos, $innerStart - $copiedPos) . "{{$startIndex}}";

            // Add result
            $resultArray[$startIndex] = substr($content, $innerStart, $innerEnd - $innerStart);

            // Set copied pos, close delimiter is copied with next fragment
            $copiedPos = $innerEnd;

            // Increment
            $startIndex++;
        }

        // Set content
        $content = $newContent . substr($content, $copiedPos);

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
        if(!is_numeric($input))
            return $input;

        // Exact integers, range checked so values beyond PHP_INT_MAX are not clamped
        if(($int = filter_var($input, FILTER_VALIDATE_INT)) !== false)
            return $int;

        // Integral floats within int range (e.g. "1.0", "1e3") become int
        $float = (float) $input;
        return (floor($float) === $float && $float >= PHP_INT_MIN && $float < PHP_INT_MAX) ? (int) $float : $float;
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

        // Length check prevents start and end overlapping, e.g. wrapping '"' in quotes
        if(!str_ends_with($input, $end) || strlen($input) < strlen($start) + strlen($end))
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
        return $input % 2 !== 0;
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
        return is_wrapped_in_quotes($input) ? substr($input, 1, -1) : $input;
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
    function str_must_start_end_with(string $input, string $start, ?string $end = null)
    {
        return wrap($input, $start, $end ?? $start);
    }
}

/**
 * str_must_not_start_end_with
 */
if(!function_exists('str_must_not_start_end_with'))
{
    function str_must_not_start_end_with(string $input, string $start, ?string $end = null)
    {
        return str_must_not_start_with(str_must_not_end_with($input, $end ?? $start), $start);
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
    function str_starts_ends_with(string $haystack, string $needle, ?string $ends = null)
    {
        return str_starts_with($haystack, $needle) && str_ends_with($haystack, $ends ?? $needle);
    }
}

/**
 * url_strip_slashes
 * 
 * @param string url url to strip slashes off
 * @param bool front strips slash from front of url
 * @param bool back strips slash from back of url
 */
if(!function_exists('url_strip_slashes'))
{
    function url_strip_slashes(string $url, bool $front = false, bool $back = false) : string
    {
        if($front && str_starts_with($url, "/"))
            $url = substr($url, 1);

        if($back && str_ends_with($url, "/"))
            $url = substr($url, 0, -1);
        
        return $url;
    }
}

/**
 * url_add_slashes
 * 
 * @param string url url to strip slashes off
 * @param bool front adds slash to front of url
 * @param bool back adds slash to back of url
 */
if(!function_exists('url_add_slashes'))
{
    function url_add_slashes(string $url, bool $front = false, bool $back = false) : string
    {
        if($front && !str_starts_with($url, "/"))
            $url = "/$url";

        if($back && !str_ends_with($url, "/"))
            $url = "$url/";
        
        return $url;
    }
}

/**
 * url_format_slashes
 * 
 * @param string url
 * @param bool front false: removes the front slash, true: adds a front slash
 * @param bool back false: removes the back slash, true: adds a back slash
 */
if(!function_exists('url_format_slashes'))
{
    function url_format_slashes(string $url, bool $front = false, bool $back = false) : string
    {
        $hasFrontSlash = strlen($url) > 0 && $url[0] == "/";
        $hasBackSlash = strlen($url) > 0 && $url[strlen($url) - 1] == "/";

        if($hasFrontSlash)
        {
            if(!$front)
                $url = substr($url, 1);
        }
        else
        {
            if($front)
                $url = "/".$url;
        }

        if($hasBackSlash)
        {
            if(!$back)
                $url = substr($url, 0, strlen($url)-1);
        }
        else
        {
            if($back)
                $url = $url."/";
        }

        return $url;
    }
}

/**
 * format_uri
 */
if(!function_exists('format_uri'))
{
    function format_uri(...$args)
    {
        $uri = [];

        // Remove empty strings/nulls and restore indices to prevent errors, "0" and scalars are kept
        $args = array_values(array_map('strval', array_filter($args, fn($arg) => (is_string($arg) || is_int($arg) || is_float($arg)) && $arg !== "")));
        $count = count($args);
        $prefix = "";
        $suffix = "";

        // If the last arg has a trailing slash, we add it back later
        if($count > 0)
        {
            if(preg_match("/^https?:\/\//", $args[0], $matches))
            {
                // Store prefix for later use
                $prefix = $matches[0];
        
                // Remove prefix from fragment
                $args[0] = substr($args[0], strlen($prefix));
            }
            else if(str_starts_with($args[0], "/"))
            {
                $prefix = "/";
            }
            
            if(str_ends_with($args[$count-1], "/"))
            {
                $suffix = "/";
            }
        }

        // Build uri by exploding every arg
        foreach($args as $arg)
            array_push($uri, ...explode("/", $arg));

        // Put it back together
        return $prefix . implode("/", array_filter($uri, fn($segment) => $segment !== "")) . $suffix;
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
            default: $color = "";
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
        $padding = (int) ceil(($width - $textLength) / 2);

        // Print left padding
        print(cli_color(str_repeat(" ", $padding), $color, $background));

        // Print text
        print(cli_color($text, $color, $background));

        // Print right padding
        print(cli_color(str_repeat(" ", $width - $padding - $textLength), $color, $background));
    
        // Print newline
        print("\n");
    
        return $width;
    }
}