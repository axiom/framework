<?php
/*
 * Class: Loader
 *
 * This is an utility class that can be used to load various resources.
 */
class Loader {
	protected function __construct() {
		$this->datalayers = array();
		$this->models = array();
		$this->lib = array();
		$this->helpers = array();
	}

	/*
	 * Method: model
	 *
	 * Load a model by the name /name/. If specified /alias/ will be the name
	 * used to access this model.
	 *
	 * Parameters:
	 *     name - The name of the model, this is the part of the filename
	 *     without the .php extension.
	 *
	 *     alias - An alias to be used instead of the name, useful if the name
	 *     is really long and akward.
	 *
	 * Returns:
	 *     An instance of the loaded model.
	 */
	public function model($name, $alias = null) {
		return $this->load($name, 'model', $alias, false);
	}

	/*
	 * Method: datalayer
	 *
	 * Load a datalayer by the name /name/. If specified /alias/ will be the name
	 * used to access this datalayer.
	 *
	 * Parameters:
	 *     name - The name of the datalayer, this is the part of the filename
	 *     without the .php extension.
	 *
	 *     alias - An alias to be used instead of the name, useful if the name
	 *     is really long and akward.
	 *
	 * Returns:
	 *     An instance of the loaded datalayer.
	 */
	public function datalayer($name, $alias = null) {
		return $this->load($name, 'datalayer', $alias);
	}

	/*
	 * Method: helper
	 *
	 * Load a helper by the name /name/. If specified /alias/ will be the name
	 * used to access this helper.
	 *
	 * Parameters:
	 *     name - The name of the helper, this is the part of the filename
	 *     without the .php extension.
	 *
	 *     alias - An alias to be used instead of the name, useful if the name
	 *     is really long and akward.
	 *
	 * Returns:
	 *     An instance of the loaded helper.
	 */
	public function helper($name, $alias = null) {
		return $this->load($name, 'helper', $alias);
	}

	/*
	 * Method: lib
	 *
	 * Load a library by the name /name/. If specified /alias/ will be the name
	 * used to access this library.
	 *
	 * Parameters:
	 *     name - The name of the library, this is the part of the filename
	 *     without the .php extension.
	 *
	 *     alias - An alias to be used instead of the name, useful if the name
	 *     is really long and akward.
	 *
	 * Returns:
	 *     An instance of the loaded library.
	 */
	public function lib($name, $alias = null) {
		return $this->load($name, 'lib', $alias);
	}

	public static function getInstance() {
		if (!is_object(self::$INSTANCE)) {
			self::$INSTANCE = new self();
		}

		return self::$INSTANCE;
	}

	private static $INSTANCE = null;

	private $datalayers;
	private $models;
	private $helpers;
	private $lib;

	protected function load($name, $type, $alias = null, $instanciate = true) {
		// Check if first argument was an array, and if so iterate through it.
		if (is_array($name)) {
			foreach ($name as $n) {
				$this->load($n, $type);
			}
			return true;
		}

		// Check if it's already loaded.
		if ($this->$name) {
			return false;
		}

		$alias = (empty($alias) ? $name : $alias);
		$dir = '';

		if (strpos($type, 'lib') !== false) {
			// FIXME: Hardcoded application path.
			if (is_dir(dirname(__FILE__).'/../application/lib/'.strtolower($name))) {
				$dir = strtolower($name);
			}
		}

		// Pluralize stuff unless it's lib. (E.g. datalayer becomes datalayers.)
		else {
			$name .= '_'.$type;
			$type .= 's';
		}

		// Split the string by underscore so we can do stuff to each part.
		$class_name = explode('_', $name);

		// Capitalize all parts.
		$class_name = array_map('ucfirst', $class_name);

		// Join the parts together again.
		$class_name = implode('', $class_name);

		// FIXME: Hardcoded application path.
		require_once(dirname(__FILE__).'/../application/'.
		             $type.'/'.$dir.'/'.$class_name.'.php');

		if ($instanciate) {
			$class = new $class_name;
		} else {
			$class = $class_name;
		}

		$alias = (isset($alias) ? $alias : $name);
		$this->$type = array_merge($this->$type, array($alias => $class));
		return $class;
	}

	protected function __get($name) {
		if (isset($this->datalayers[$name])) {
			return $this->datalayers[$name];
		} else if (isset($this->models[$name])) {
			return $this->models[$name];
		} else if (isset($this->helpers[$name])) {
			return $this->helpers[$name];
		} else if (isset($this->lib[$name])) {
			return $this->lib[$name];
		}
		return false;
	}
}
?>
