<?php

$include_path = array(
	'datalayers',
	'controllers',
	'models',
	'helpers',
	'lib',
	'lib/thumbnailer',
);

$include_path = array_map(create_function('$dir',
	'return dirname(__FILE__)."/../application/".$dir;'), $include_path);

set_include_path(implode(PATH_SEPARATOR, $include_path));
/*
 * Function: __autoload
 *
 * Automatically tries to load class definitions from files.
 *
 * Parameters:
 *     class_name - The name of the class to load.
 */
function __autoload($class_name)
{
   $file = $class_name.'.php';

   require_once($file);
}
?>
