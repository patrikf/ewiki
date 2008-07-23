<?php

class Core
{
    static function shortdump($x, $recurse=TRUE)
    {
	if ($x === NULL)
	    return 'NULL';
	if (is_bool($x))
	    return $x ? 'TRUE' : 'FALSE';
	if (is_scalar($x))
	{
	    if (is_string($x))
	    {
		$s = "'";
		$last = min(100, strlen($x));
		for ($i = 0; $i < $last; $i++)
		{
		    $c = ord($x{$i});
		    $special = array("\n" => '\\n', "\r" => '\\r', "\t" => '\\t', '\'' => '\\\'', '\\' => '\\\\');
		    if (isset($special[$x{$i}]))
			$s .= $special[$x{$i}];
		    else if ($c < 32 || $c > 126)
			$s .= sprintf('\\x%02X', $c);
		    else
			$s .= $x{$i};
		}
		$s .= "'";
		if (strlen($x) > 100)
		    $s .= '...';
		return $s;
	    }
	    else
		return (string)$x;
	}
	if (is_resource($x))
	    return sprintf("[%s resource]", get_resource_type($x));
	if (is_object($x))
	    return sprintf("[%s instance]", get_class($x));
	if (is_array($x))
	{
	    if (!$recurse)
		return '[array]';
	    $s = 'array(';
	    $first = TRUE;
	    foreach (array_slice($x,0,5) as $key => $value)
	    {
		if (!$first)
		    $s .= ', ';
		$first = FALSE;
		if (!is_int($key))
		    $s .= sprintf("%s => ", self::shortdump($key));
		$s .= self::shortdump($value, FALSE);
	    }
	    if (count($x) > 5)
		$s .= ', ...';
	    $s .= ')';
	    return $s;
	}
    }

    static function format_exception($e)
    {
	$s = 'Uncaught Exception ('.get_class($e).")\n";
	$s .= "\n";
	$s .=  "Message:\n";
	foreach (explode("\n", $e->getMessage()) as $line)
	    $s .=  "    $line\n";
	$s .=  "\n";
	$s .=  "Stack trace:\n";
	foreach ($e->getTrace() as $frameno => $frame)
	{
	    $s .= sprintf("\n    #%-2d ", $frameno);
	    $s .= sprintf("%s(", (isset($frame['class']) ? $frame['class'].$frame['type'].$frame['function'] : $frame['function']));
	    foreach ($frame['args'] as $key => $value)
	    {
		$s .=  "\n                ";
		if (!is_int($key))
		    $s .= sprintf("$%s = ", $key);
		$s .=  self::shortdump($value);
	    }
	    if (count($frame['args']))
		$s .= "\n            ";
	    $s .= ");\n";
	    $s .= "        called from ";
	    if (isset($frame['file']))
	    {
		$s .= sprintf("%s", $frame['file']);
		if (isset($frame['line']))
		    $s .= sprintf(":%d\n", $frame['line']);
	    }
	    else
		$s .=  "[internal function]\n";
	}
	return $s;
    }

    static function fail_plaintext($msg)
    {
	while (ob_get_level() > 0)
	    ob_end_clean();
	$plain = !headers_sent();
	if ($plain)
	{
	    header('Content-Type: text/plain');
	    echo $msg;
	}
	else
	{
	    echo '<pre>';
	    echo htmlspecialchars($msg, ENT_NOQUOTES, 'UTF-8');
	    echo '</pre>';
	}
	exit(1);
    }

    static function format_error($errno, $msg, $file, $line)
    {
	$name = array(
		E_WARNING => 'Warning',
		E_NOTICE => 'Notice',
		E_USER_ERROR => 'User Error',
		E_USER_WARNING => 'User Warning',
		E_USER_NOTICE => 'User Notice',
		E_STRICT => 'Strict Standards Warning',
	    );
	$s = isset($name[$errno]) ? $name[$errno] : 'Unknown Error';

	$s .= sprintf(" (%d):\n", $errno);
	$s .= sprintf("    at %s:%d\n", $file, $line);
	$s .= "\nMessage:\n";
	foreach (explode("\n", $msg) as $line)
	    $s .= '    '.$line;
	return $s;
    }

    static function handle_exception($e)
    {
	self::fail_plaintext(self::format_exception($e));
    }

    static function handle_error($errno, $msg, $file, $line)
    {
	throw new Exception(self::format_error($errno, $msg, $file, $line));
    }

    static function handle_assertion($file, $line)
    {
        throw new Exception(sprintf('failed assertion at %s:%d', $file, $line));
    }
}

set_exception_handler(array('Core', 'handle_exception'));
set_error_handler(array('Core', 'handle_error'));
assert_options(ASSERT_CALLBACK, array('Core', 'handle_assertion'));
assert_options(ASSERT_BAIL, 0); /* this would end the program without displaying anything */

?>
