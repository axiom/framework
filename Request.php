<?php
/*
 * Class: Request
 *
 * The Request class abstracts a request comming from the webclient.
 */
class Request
{

	/*
	 * Constructor: __construct
	 *
	 * Initiates the class.
	 *
	 * Parameters:
	 *     raw_request - Should come from PHP's *$_SERVER['REQUEST']*.
	 */
	public function __construct($raw_request)
	{
		$this->uri = $raw_request['REQUEST_URI'];
		$this->method = strtolower($raw_request['REQUEST_METHOD']);
		$this->time = $raw_request['REQUEST_TIME'];
		$this->query = $raw_request['QUERY_STRING'];
		$this->post_data = $_POST;
		$this->raw_request = $raw_request;
	}

	/*
	 * Method: getURI
	 *
	 * Returns:
	 *     The requested URI.
	 */
	public function getURI()
	{
		return substr($this->uri, 0, strlen($this->uri));
	}

	/*
	 * Method: getMethod
	 *
	 * The method used by the browser i.e. one of: head, get, post (and in
	 * the future also: delete and put).
	 *
	 * Returns:
	 *     One of post, get, head, put or delete.
	 */
	public function getMethod()
	{
		return strtolower($this->method);
	}

	/*
	 * Method: isPost
	 *
	 * Check if the request was a post request.
	 *
	 * Returns:
	 *     True if the request was a post request false otherwise.
	 */
	public function isPost()
	{
		return $this->getMethod() == 'post';
	}

	/*
	 * Method: isGet
	 *
	 * Check if the request was a get request.
	 *
	 * Returns:
	 *     True if the request was a get request false otherwise.
	 */
	public function isGet()
	{
		return $this->getMethod() == 'get';
	}

	/*
	 * Method: isXMLHttp
	 *
	 * Tells whether an request was made as a XMLHttpRequest or not.
	 *
	 * Returns:
	 *     True if teh request was made as a XMLHttpRequest, false otherwise.
	 */
	public function isXMLHttp()
	{
		$header = $this->getHeader('X-Requested-With');
		return ($header == 'XMLHttpRequest');
	}

	/*
	 * Method: getQueryString
	 *
	 * The querystring is what was appended as extra information to the
	 * requested resource. For example if I were to request
	 * _/hello/kitty/print.php?s=HelloWorld_ the query string would be
	 * _s=HelloWorld_.
	 *
	 * Returns:
	 *     The query string.
	 */
	public function getQueryString()
	{
		return $this->query;
	}

	/*
	 * Method: getPostData
	 *
	 * Get the data posted by the client.
	 *
	 * Parameters:
	 *     key - If key is given only the data named key will be returned.
	 *
	 * Returns:
	 *     An associated array of key value pairs, or just the value if key was
	 *     given.
	 */
	public function getPostData($key = null)
	{
		if ($key && isset($this->post_data[$key])) {
			return $this->post_data[$key];
		} else if ($key == null) {
			return $this->post_data;
		} else {
			return $null;
		}
	}

	/*
	 * Method: getHeader
	 *
	 * Get a header from the apache_request_headers method.
	 *
	 * Parameters:
	 *     header - Which header to get e.g. If-None-Match.
	 *
	 * Returns:
	 *     The header value, if the header key existed false otherwise.
	 */
	public function getHeader($header)
	{
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (array_key_exists($header, $headers)) {
				return $headers[$header];
			} else {
				return false;
			}
		}
	}

	private $uri;
	private $method;
	private $time;
	private $query;
	private $post_data;
	private $raw_request;
}
?>
