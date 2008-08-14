<?php
class ErrorHandler {
	public function __construct()
	{
		set_error_handler(array($this, 'errorHandler'));
		set_exception_handler(array($this, 'exceptionHandler'));

		if (Config::getInstance()->shouldDebug()) {
			// Let's show every error for debugging purposes.
			error_reporting(E_ALL);
		}
	}

	public function errorHandler($errno, $errstr, $errfile = null,
	                             $errline = null, $errcontext = null)
	{
		if (!error_reporting()) {
			return;
		}

		debug_backtrace();
	}

	public function exceptionHandler(Exception $e)
	{
		if (!error_reporting()) {
			return;
		}

		$this->exception = $e;

		// Send a stacktrace to the developers if there is a serious error.
		if ($e->getCode() > FrameworkException::NOT_FOUND) {
			$this->mailTrace($e, 'johannes@antiklimax.se');
		}

		if (!Config::getInstance()->shouldDebug() && $this->serveErrorPage()) {
			die();
		}

		echo '<div class="error">';
		echo '<h1>'.$e->getCode().'</h1>';
		echo '<p>'.$e->getMessage().'</p>';
		echo '</div>';
	}

	protected $exception;
	protected $error;

	protected function serveErrorPage()
	{
		$error_page = dirname(__FILE__).'/../application/views/errors/index.php';

		// Check if we've got a nice error page to show.
		if (is_readable($error_page)) {
			// Etract the exception so the error page can do stuff with it.
			$exception = $this->exception;
			include($error_page);
			return true;
		} else {
			echo $this->exception->getMessage();
			return false;
		}
	}

	// mailTrace {{{
	/*
	 * Method: mailTrace
	 *
	 * Sends a backtrace from an exception to an email address.
	 *
	 * Parameters:
	 *     e - The exception.
	 *     address - The email address to send the mail to.
	 */
	protected function mailTrace(Exception $e, $address)
	{
		$trace = print_r($this->exception->getTrace(), true);
		$body = <<<EOL
Hej detta är webbapplikationen på cffc.se. Det har tyvärr uppståt ett problem
med webbapplikationen som jag tänkte att du kanske borde få veta. Jag bifogar
en felrapport med mailet och en fullständig bakåtspårning av felet.

Felrapport
==========
{$this}

Bakåtspårning
=============
{$trace}

EOL;

		$subject = '[cffc.se] Backtrace från cffc.se';
		$headers = "From: cffc-www@cffc.se\r\n" .
		           "Return-Path: " . $address . "\r\n" .
		           "Errors-To: " . $address . "\r\n";

		// FIXME: fix so that mails get delivered correctly.
		//mail($address, $subject, $body, $headers);
	}
	// }}}
}
?>
