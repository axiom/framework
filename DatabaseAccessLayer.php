<?php
/*
 * Class: DatabaseAccessLayer
 *
 * This class is used as an layer between the application and the database. It
 * should be used to do basic CRUD actions and other stuff that has to do with
 * the database. It should also do stuff specific to the actual model.
 */
class DatabaseAccessLayer {

	/*
	 * Constructor: __construct
	 *
	 * The constructor function for the class, it will setup a connection to the
	 * database specified in the config-file.
	 *
	 * Throws:
	 * Will throw an PDOException if the database connection fails.
	 */
	public function __construct()
	{
		$this->config = Config::getInstance();
		self::setupDb();
	}

	/*
	 * Variable: db
	 *
	 * An instance of a PDO object connected to the database. All database
	 * access should go thruh this object.
	 */
	protected static $db;
	protected $config;

	/*
	 * Method: getMany
	 *
	 * Helper method intended to instanciate an array objects.
	 *
	 * Parameters:
	 *     sql - Sql statement that should return multiple rows.
	 *     model_name - Which model to instanciate (e.g. <PhotoModel>).
	 *     associations - Any associations to setup with the model object (an 
	 *     associative array).
	 *
	 * Returns:
	 *     An array with the instantiated objects.
	 */
	protected function getMany($sql, $model_name, $associations = null)
	{
		$items = self::$db->query($sql, PDO::FETCH_ASSOC)->fetchAll();

		if (!is_array($items))
			return false;

		$stuff_on_my_cat = array();
		$i = 0;
		foreach ($items as $item) {
			array_push($stuff_on_my_cat, new $model_name($item, $associations));
		}

		return $stuff_on_my_cat;
	}

	protected function getOne($sql, $model_name)
	{
		$item = self::$db->query($sql, PDO::FETCH_ASSOC)->fetch();
		return (is_array($item) ? new $model_name($item) : false);
	}

	protected static function setupDb()
	{
		if (is_object(self::$db)) {
			return self::$db;
		}

		$config = Config::getInstance();
		$driver = $config->getDatabaseDriver();
		$host = $config->getDatabaseHostname();
		$user = $config->getDatabaseUsername();
		$pass = $config->getDatabasePassword();
		$dbname = $config->getDatabaseName();
		$file = $config->getDatabaseFile();

		try {
			switch ($driver) {
				case 'sqlite':
					self::$db = new PDO('sqlite:'.$file);
					break;

				default:
				case 'mysql':
					self::$db = new PDO($driver.':dbname='.$dbname.';host='.$host,
					                    $user, $pass,array(PDO::ATTR_PERSISTENT => true));
			}
			self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new Exception("Kunde inte etablera anslutning till databasen. (".
			                    $e->getMessage().')');
		}
	}
}
?>
