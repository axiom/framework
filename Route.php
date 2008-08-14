<?php
/*
 * Class: Route
 *
 * Used to map the requested URI from a browser to a controller and an action.
 *
 * Example:
 * (start example)
 * $r = new Route("http://example.com/blog/show/1");
 * $r->getController(); // "Blog"
 * $r->getAction(); // "show"
 * $r->getParameters(); // array("1")
 * $r->getNumberOfParameters(); // 1
 * $r->getNumberOfRequiredParameters(); // 1
 * (end example)
 */
class Route
{
	public function __construct($uri)
	{
		$config = Config::getInstance();
		$this->uri = substr($uri, strlen($config->getBasepath()),strlen($uri));

		// Strip trailing slashes.
		$this->uri = trim($this->uri,"\/");

		if (!$config->getUrlCaseSensitive()) {
			$this->uri = trim(strtolower($this->uri),"\/");
		}

		$this->parse();

		// Divide the uri into segments.
		$this->segments = explode("/", $this->uri);
	}

	/*
	 * Method: parse
	 *
	 * Parses the URI with respect to the routes set up in the
	 * routes-configuration file.
	 */
	protected function parse()
	{
        $routes = Config::getInstance()->getRoutes();

		// Go through the routes in the config file from top to bottom, stoping
		// at the first match.
		foreach ($routes as $key => $r) {
			if ($key == "/") {
				$key = "";
			}

			// Expand the short-hand-regexp with real regexps.
			$regex = "/^" . str_replace("/", "\/", $key) . "$/";
			$regex = str_replace(":num", "(\d+)", $regex);
			$regex = str_replace(":alphanum", "([a-zA-Z0-9+_-]+)", $regex);
			$regex = str_replace(":alpha", "([a-zA-Z+_-]+)", $regex);

			$matches = array();
			// Check if the uri matches the current route.
			if (preg_match($regex, $this->uri, $matches)) {

				// Place every match in the correct position in the expanded
				// uri.
				for ($i = 1; $i < count($matches); $i++) {
					$r = str_replace("$".$i, $matches[$i], $r);
				}

				// Weed out every place holder that didn't match anything.
				$r = preg_replace("/\\$\d+\/?/", "", $r);

				// Remove starting and trailing slashes.
				$this->uri = trim($r, "\/");

				// We can break the loop now because we have found a match.
				break;
			}
		}
	}

	/*
	 * Method: getURI
	 *
	 * Simple getter for the uri attribute.
	 *
	 * Returns:
	 *     The uri attribute of the class.
	 */
	public function getURI()
	{
		return $this->uri;
	}

	/*
	 * Method: getSegment
	 *
	 * Returns a segment from the URI, mostly used internally by this class.
	 *
	 * Parameters:
	 *     segment_number - Which segment to return, the first one is 1.
	 *
	 * Returns:
	 *     If the segment exists it will be returned otherwise false will be
	 *     returned.
	 */
	public function getSegment($segment_number)
	{
		if ($segment_number <= count($this->segments)) {
			return $this->segments[$segment_number - 1];
		} else {
			return false;
		}
	}

	/*
	 * Method: getParameters
	 *
	 * Get all the parameters given by the browser (i.e. that is every segment
	 * after the action-segment).
	 *
	 * Returns:
	 *     An array of parameters if there was any, otherwise false.
	 */
	public function getParameters()
	{
		$params = array_slice($this->segments, 2);

		if (count($params) == 0) {
			return false;
		} else {
			return $params;
		}
	}

	/*
	 * Method: getNumberOfParameters
	 *
	 * Check the number of supplied parameters (i.e. number of segments
	 * exluding the controller- and action-segment).
	 *
	 * Returns:
	 *     Number of paramaters, if no extra parameters where given it returns
	 *     0.
	 */
	public function getNumberOfParameters()
	{
		$n = count($this->segments) - 2;
		return max(0, $n);
	}

	/*
	 * Method: getController
	 *
	 * Find out what controller class was requested by the browser.
	 *
	 * Returns:
	 *     The capitalized name of the controller (without any suffix).
	 */
	public function getController()
	{
		return ucfirst($this->getSegment(1));
	}

	/*
	 * Method: getAction
	 *
	 * Find out which action the user wanted.
	 *
	 * Returns:
	 *     The (lowercase) name of the requested action.
	 */
	public function getAction()
	{
		return strtolower($this->getSegment(2));
	}
}
?>
