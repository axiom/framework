<?php

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
	if (strpos($class_name, 'Model')) {
		require_once(dirname(__FILE__).'/../application/models/'.$class_name);
	}
	else if (strpos($class_name, 'Datalayer')) {
		require_once(dirname(__FILE__).'/../application/datalayers/'.$class_name);
	}
}

function err($errno, $errstr, $errfile, $errline) {
	if (error_reporting() == 0) {
		return;
	}
	echo '<div style="margin: 1em; color: #A40000; background: #FF9999; '.
	     'padding: 0.5em; border: 2px solid #A40000;">'.
	     '<span style="display: block;"><strong>'.$errfile.'</strong>'.
	     ' on line <strong>'.$errline.'</strong></span>'.
	     $errstr.
	     '</div>';
}

set_error_handler('err');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Stockholm');
setlocale(LC_TIME, 'sv_SE.UTF-8');

if (($dh = opendir($framework_path)) === false) {
	throw new Exception("Couldn't open the framework directory.".
	                    "Please specify it in 'public/index.php'.");
}

// Load all php files in the framework directory.
while (($file = readdir($dh)) !== false) {
	if (strpos($file, '.php') && strpos($file, basename(__FILE__)) === false) {
		require_once($framework_path.'/'.$file);
	}
}

if (Config::getInstance()->shouldDebug()) {
	// Let's time this whole thing.
	$TIMER = microtime(true);

	// Let's show every error for debugging purposes.
	error_reporting(E_ALL);
}

try {
	// Run the whole thing.
	new Dispatcher($_SERVER);
} catch (Exception $e) {
	trigger_error($e->getMessage());
	throw new Exception("Framework error.");
}

if (isset($TIMER)) {
	// echo "<!-- ".(microtime(true) - $TIMER)." sekunder att generera -->";
}
?>
