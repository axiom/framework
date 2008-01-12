<?php
/*
 * Class: Config
 *
 * Configuration class that parses the applications configuration file.
 * Imlemented as a singelton (<getInstance>).
 */
class Config
{

	/*
	 * Constructor: __construct
	 *
	 * Load the configuration file for the application.
	 */
	protected function __construct()
	{
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

	/*
	 * Method: get
	 *
	 * Get settings from the configuration file. Also see
	 * <getValueWithDefault>.
	 *
	 * Parameters:
	 *     section - The section to use.
	 *     key - The setting keyword to look up.
	 *
	 * Returns:
	 *     Returns the configuration value if found, null otherwise.
	 */
	public function get($section, $key)
	{
		return $this->getValueWithDefault($section, $key, null);
	}

	/*
	 * Method: getBasepath
	 *
	 * The basepath is the URL-location of this application.
	 *
	 * Parameters:
	 *     full - If this is set to true the full url-basepath will be returned
	 *     (e.g.  _http://example.com/wickedapp/_), else just the relative url
	 *     will be returned
	 *
	 * Returns:
	 *     The basepath to the application.
	 */
	public function getBasepath($full = false)
	{
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
	 *     Full path to the cache directory.
	 */
	public function getCacheDir()
	{
		// FIXME: Hardcoded application path.
		$default = dirname(__FILE__)."/../application/cache";
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
	public static function getInstance()
	{
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
	public function useCache()
	{
		return $this->getValueWithDefault('application', 'use_cache', false);
	}

	/*
	 * Method: useImageCache
	 *
	 * Returns:
	 *    True if config is turned on in the config-file otherwise false.
	 */
	public function useImageCache()
	{
		return $this->getValueWithDefault('application', 'use_image_cache', true);
	}

	/*
	 * Method: getDefaultController
	 *
	 * Get the default controller for the application, e.g. when no controller
	 * is specified use this one.
	 *
	 * Returns:
	 *    The default controller as specified in the configuration file, or
	 *    null if not specified.
	 */
	public function getDefaultController()
	{
		return $this->getValueWithDefault('application', 'default_controller',
			null);
	}

	/*
	 * Method: getDefaultAction
	 *
	 * Get the default action to use when no action is specified.
	 *
	 * Returns:
	 *    The default action as specified in the configuration file, defaults
	 *    to 'index' if not specified in the configuration file.
	 */
	public function getDefaultAction()
	{
		return $this->getValueWithDefault('application', 'default_action',
			'index');
	}

	/*
	 * Method: getErrorController
	 *
	 * FIXME: Reimund?
	 */
	public function getErrorController()
	{
		return false;
	}

	/*
	 * Method: getDatabaseDriver
	 *
	 * Returns:
	 *    The specified database driver (e.g. mysql) from the configuration
	 *    file.
	 */
	public function getDatabaseDriver()
	{
		return $this->getValueWithDefault('database', 'driver', 'mysql');
	}

	/*
	 * Method: getDatabaseName
	 *
	 * Returns:
	 *    The specified database database name from the configuration file.
	 */
	public function getDatabaseName()
	{
		return $this->getValueWithDefault('database', 'database_name', 'app');
	}

	/*
	 * Method: getDatabaseUsername
	 *
	 * Returns:
	 *    The specified database username from the configuration file (defaults
	 *    to 'root').
	 */
	public function getDatabaseUsername()
	{
		return $this->getValueWithDefault('database', 'username', 'root');
	}

	/*
	 * Method: getDatabasePassword
	 *
	 * Returns:
	 *    The specified database password from the configuration file (defaults
	 *    to '' (empty)).
	 */
	public function getDatabasePassword()
	{
		return $this->getValueWithDefault('database', 'password', '');
	}

	/*
	 * Method: getDatabaseHostname
	 *
	 * Returns:
	 *    The specified database hostname from the configuration file (defaults
	 *    to 'localhost').
	 */
	public function getDatabaseHostname()
	{
		return $this->getValueWithDefault('database', 'hostname', 'localhost');
	}

	/*
	 * Method: getDatabaseFile
	 *
	 * Returns:
	 *    The specified database filename from the configuration file (defaults
	 *    to '' (empty)).
	 */
	public function getDatabaseFile()
	{
		return $this->getValueWithDefault('database', 'file', '');
	}

	/*
	 * Method getRoutes
	 *
	 * Gets the routes specified in routes.php file in the config directory.
	 *
	 * Returns:
	 *    An array with all routes that are specified in the routes.php
	 *    configuration file.
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/*
	 * Method: shouldDebug
	 *
	 * Whether to go into debug mode or not.
	 *
	 * Returns:
	 *     As specified in the configuration file defaults to true.
	 */
	public function shouldDebug()
	{
		return $this->getValueWithDefault('application', 'debug', true);
	}

	private $BASEPATH = "";
	private static $INSTANCE = null;
	private $settings;
	private $routes;

	/*
	 * Method: getValueWithDefault
	 *
	 * Get a setting from the configuration but fallback on a default value if
	 * the setting can not be found in the configuration file.
	 *
	 * Parameters:
	 *     section - The section to look under.
	 *     name - The keyword to look up.
	 *     default - The default value.
	 *
	 * Returns:
	 *     The value specified in the configuration file, or _default_ if no
	 *     value was specified in the configuration file.
	 */
	protected function getValueWithDefault($section, $name, $default)
	{
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
