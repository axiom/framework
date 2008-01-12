<?php
/*
 * Class: Config
 *
 * Configuration class that parses the applications configuration file.
 * Imlemented as a singelton (<getInstance>).
 */
class Config {

	/*
	 * Constructor: __construct
	 *
	 * Reads the config file to a class variable and determains the
	 * applications base path.
	 */
	protected function __construct() {
		$this->BASEPATH = dirname($_SERVER["PHP_SELF"]);

		// FIXME: Hardcoded application path.
		// Read user settings from config file.
		$this->settings = parse_ini_file(
			dirname(__FILE__)."/../application/config/config.ini", true);

		// FIXME: Hardcoded application path.
		// Read routes from routes.php.
		require_once(dirname(__FILE__)."/../application/config/routes.php");
		$this->routes = $routes; 
	}

	public function get($section, $key) {
		return $this->getValueWithDefault($section, $key, null);
	}

	/*
	 * Method: getBasepath
	 *
	 * The basepath is the URL-location of this application relative to the
	 * webroot.
	 *
	 * Parameters:
	 *     full - If this is set to true the full url-basepath will be returned
	 *     (e.g.  _http://example.com/wickedapp/_), else just the relative url
	 *     will be returned
	 *
	 * Returns:
	 *     The basepath to the application.
	 */
	public function getBasepath($full = false) {
		if ($full) {
			return trim('http://'.$_SERVER['HTTP_HOST'].'/'.$this->BASEPATH, '/');
		} else {
			return $this->BASEPATH;
		}
	}

	/*
	 * Method: getCacheDir
	 *
	 * Get the configured directory for storing cache files, defaults to
	 * _application/cache_.
	 *
	 * Returns:
	 *     Full path to the directory with an ending slash.
	 */
	public function getCacheDir() {
		// FIXME: Hardcoded application path.
		$default = dirname(__FILE__)."/../application/cache/";
		return $this->getValueWithDefault('application', 'cache_dir', $default);
	}

	/*
	 * Method: getInstance
	 *
	 * Gets the single instance of this class (it's a singelton).
	 *
	 * Returns:
	 *     The only instance of this class.
	 */
	public static function getInstance() {
		if (!is_object(self::$INSTANCE)) {
			self::$INSTANCE = new self();
		}

		return self::$INSTANCE;
	}

	/*
	 * Method: useCache
	 *
	 * Returns:
	 *    True if config is turned on in the config-file otherwise false.
	 */
	public function useCache() {
		return $this->getValueWithDefault('application', 'use_cache', false);
	}

	/*
	 * Method: useImageCache
	 *
	 * Returns:
	 *    True if config is turned on in the config-file otherwise false.
	 */
	public function useImageCache() {
		return $this->getValueWithDefault('application', 'use_image_cache', true);
	}

	/*
	 * Method: getDefaultController
	 *
	 * Returns:
	 *    The default controller as specified in the configuration file, or
	 *    false if not specified.
	 */
	public function getDefaultController() {
		return $this->getValueWithDefault('application','default_controller',
			'page');
	}

	/*
	 * Method: getDefaultAction
	 *
	 * Returns:
	 *    The default action as specified in the configuration file, defaults
	 *    to 'index' if not specified in the configuration file.
	 */
	public function getDefaultAction() {
		return $this->getValueWithDefault('application', 'default_action',
			'index');
	}

	public function getErrorController() {
		return false;
	}

	/*
	 * Method: useCache
	 *
	 * Returns:
	 *    The specified database driver (e.g. mysql) from the configuration
	 *    file.
	 */
	public function getDatabaseDriver() {
		return $this->getValueWithDefault('database', 'driver', 'mysql');
	}

	/*
	 * Method: useCache
	 *
	 * Returns:
	 *    The specified database database name from the configuration file.
	 */
	public function getDatabaseName() {
		return $this->getValueWithDefault('database', 'database_name', 'app');
	}

	/*
	 * Method: useCache
	 *
	 * Returns:
	 *    The specified database username from the configuration file.
	 */
	public function getDatabaseUsername() {
		return $this->getValueWithDefault('database', 'username', 'root');
	}

	/*
	 * Method: useCache
	 *
	 * Returns:
	 *    The specified database password from the configuration file.
	 */
	public function getDatabasePassword() {
		return $this->getValueWithDefault('database', 'password', '');
	}

	/*
	 * Method: getDatabaseHostname
	 *
	 * Returns:
	 *    The specified database hostname from the configuration file.
	 */
	public function getDatabaseHostname() {
		return $this->getValueWithDefault('database', 'hostname', 'localhost');
	}

	/*
	 * Method: getDatabaseFile
	 *
	 * Returns:
	 *    The specified database filename from the configuration file.
	 */
	public function getDatabaseFile() {
		return $this->getValueWithDefault('database', 'file', '/dev/null');
	}

	/*
	 * Method getRoutes
	 *
	 * Gets the routes specified in the configuration file.
	 *
	 * Returns:
	 *    An array with all routes that are specified in the configuration
	 *    file.
	 */
	public function getRoutes() {
		return $this->routes;
	}

	/*
	 * Method: shouldDebug
	 *
	 * Wheather to go into debug mode or not.
	 *
	 * Returns:
	 *     True if debug mode should be enabled, false otherwise.
	 */
	public function shouldDebug() {
		return $this->getValueWithDefault('application', 'debug', true);
	}

	private $BASEPATH = "";
	private static $INSTANCE = null;
	private $settings;
	private $routes;

	protected function getValueWithDefault($section, $name, $default) {
		if ($this->settings && isset($this->settings[$section]) &&
			isset($this->settings[$section][$name]))
		{
			return $this->settings[$section][$name];
		} else {
			return $default;
		}
	}
}
?>
