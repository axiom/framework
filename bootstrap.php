<?php
// Setup the timezone and the locale to swedish. (For dates and such.)
date_default_timezone_set('Europe/Stockholm');
setlocale(LC_TIME, 'sv_SE.UTF-8');

// Loads every file in the directory '$framework_path'.
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
