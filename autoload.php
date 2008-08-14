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
	'return getcwd()."/../application/".$dir;'), $include_path);

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
	$include_path = get_include_path();
	$include_path = explode(PATH_SEPARATOR, $include_path);
	$file = $class_name.'.php';

	foreach ($include_path as $path) {
		if (is_readable($path.'/'.$file)) {
			include_once($file);
			return true;
		}
	}
	return false;
}
?>
