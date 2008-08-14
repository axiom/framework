<?php
/*
 * Class: Dispatcher
 *
 * The Dispatcher is the thing that take a request and calls the right methods
 * in the right classes. One can say that it maps URI requests to method
 * invocations.
 */
class Dispatcher
{

	/*
	 * Constructor: __construct
	 *
	 * Initializes the Dispatcher. Starts caching if it is enabled in the
	 * config-file.
	 */
	public function __construct($uri = null)
	{
		$this->config = Config::getInstance();
		$this->request = new Request($_SERVER);

		$uri = ($uri ? $uri : $this->request->getURI());
		$this->route = new Route($uri);

		if ($this->config->useCache() || $this->config->useImageCache()) {
			$this->cache = new Cache($this->request);

			// If caching is enabled but the cache file is not in the system
			// the dispatch has to be done so we can create the file.
			if (!$this->cache->isServed()) {
				$this->dispatch();
			}
		} else {
			$this->dispatch();
		}
	}

	/*
	 * Method: dispatch
	 *
	 * Creates a Controller and calls the Controller method that corresponds
	 * to the requested URI. For example, if the requested URI is /news/post/12
	 * the controller News is instantiated, and News->post is called with 12 as
	 * the argument.
	 *
	 * If the dispatch is unsuccessful the user will be sent to the default 404
	 * error file, _framework/errors/404.php_. There are numerous reasons
	 * why the dispatch might not succeed:
	 *
	 *  - The Controller can't be created, typically this happens when the
	 *    controller can't be found. Controllers are located in
	 *    application/controllers.
	 *
	 *  - The expected method doesn't exist.
	 *
	 *  - Required parameters are missing in the method call.
	 */
	private function dispatch()
	{
		$success = $this->tryDispatch();

		if (!$success) {
			$error_controller = $this->config->getErrorController();
			if(!empty($error_controller)) {
				$this->route = new Route("/$error_controller/notFound");
				if(!$this->tryDispatch()) {
					if ($this->config->shouldDebug()) {
						throw new Exception("404 Not found");
					} else {
						include(dirname(__FILE__) . "/errors/404error.php");
					}
				}
			} else {
				// 404 Not Found
				throw new FrameworkException(FrameworkException::NOT_FOUND,
					"Kunde tyvärr inte hitta sidan du sökte.");
			}
		}
		return $success;
	}

	/*
	 * Method: tryDispatch
	 *
	 * FIXME: Reimund?
	 */
	private function tryDispatch()
	{
		// Get the name of the controller to be instantiated.
		$controller_name = $this->route->getController();
		$defualt_method = $this->config->getDefaultAction();

		// Use the default controller if no other controller was specified.
		if (empty($controller_name) && $this->config->getDefaultController()) {
			$controller_name = ucfirst($this->config->getDefaultController());
		}

		// Add 'Controller' suffix.
		$controller_name .= "Controller";

		// FIXME: Hardcoded application path.
		// Make sure the controller-class file exists

		$controller_file = $this->config->getApplicationPath() . "/controllers/" .
			$controller_name . ".php";
		if (file_exists($controller_file)) {
			// Include the controller-class file.
			require_once($controller_file);
		}

		$success = false;

		if (class_exists($controller_name)) {
			$controller = new $controller_name($this->request);
			$method = $this->route->getAction();

			if (empty($method)) {
				if (method_exists($controller,$defualt_method)) {
					call_user_func(array($controller,$defualt_method));
					$success = true;
				}
			} else if (method_exists($controller,$method)) {
				$rmethod = new ReflectionMethod($controller_name, $method);
				$rparams = $rmethod->getNumberOfRequiredParameters();
				$params = $rmethod->getNumberOfParameters();
				$user_params = $this->route->getNumberOfParameters();

				if ($user_params <= $params && $user_params >= $rparams) {
					call_user_func_array(array($controller,$method),
						$this->route->getParameters());
					$success = true;
				}
			}
		}
		return $success;
	}

	private $request;
	private $route;
	private $cache;
	private $config;
}
?>
