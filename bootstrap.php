<?php
date_default_timezone_set('Europe/Stockholm');
setlocale(LC_TIME, 'sv_SE.UTF-8');

// Load all framework files.
foreach (scandir($framework_path) as $file) {
	if (strpos($file, '.php')) {
		require_once($framework_path.'/'.$file);
	}
}

// Setup the error and exception handlers.
new ErrorHandler();

// Dispatch the incoming request.
new Dispatcher();
?>
