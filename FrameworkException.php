<?php
class FrameworkException extends Exception {
	const NOT_FOUND = 1;
	const NOT_FOUND_IN_DATABASE = 2;
	const NOT_FOUND_IN_FILESYSTEM = 4;
	const OTHER = 8;

	public function __construct($code, $message)
	{
		$this->error_lookup = array(
			self::NOT_FOUND => 'Fel',
			self::NOT_FOUND_IN_DATABASE => 'Databasfel',
			self::NOT_FOUND_IN_FILESYSTEM => 'Lagringsfel',
			self::OTHER => 'Okänt fel'
		);
		
		parent::__construct($message, $code);
		$this->code = $this->error_lookup[$code];
	}

	protected $error_lookup;
}
?>
