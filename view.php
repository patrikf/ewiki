<?php

class View
{
    protected $_dict = array();
    protected $_name;

    function __construct($file)
    {
	$this->_file = $file;
    }

    function __set($name, $value) { $this->_dict[$name] = $value; }
    function __get($name) { return $this->_dict[$name]; }
    function __isset($name) { isset($this->_dict[$name]); }
    function __unset($name) { unset($this->_dict[$name]); }

    function display($capture=FALSE)
    {
	foreach ($this->_dict as $name => $value)
	    $$name = $value;

	ob_start();
	require($this->_file);
	if ($capture)
	    return ob_get_clean();
	else
	    ob_end_flush();
    }

    function assign($vars)
    {
	foreach ($vars as $key => $value)
	    $this->$key = $value;
    }
}

?>
