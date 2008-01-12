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
	public function __construct() {
		$this->config = Config::getInstance();
		$driver = $this->config->getDatabaseDriver();
		$host = $this->config->getDatabaseHostname();
		$user = $this->config->getDatabaseUsername();
		$pass = $this->config->getDatabasePassword();
		$dbname = $this->config->getDatabaseName();
		$file = $this->config->getDatabaseFile();

		try {
			switch ($driver) {
				case 'sqlite':
					$this->db = new PDO('sqlite:'.$file);
					break;

				default:
				case 'mysql':
					$this->db = new PDO($driver.':dbname='.$dbname.';host='.$host,
					                    $user, $pass);
			}
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new Exception("Kunde inte etablera anslutning till databasen. (".
			                    $e->getMessage().')');
		}
	}

	/*
	 * Variable: db
	 *
	 * An instance of a PDO object connected to the database. All database
	 * access should go thruh this object.
	 */
	protected $db;
	protected $config;

	protected function getMany($sql, $model_name, $associations = null)
	{
		$items = $this->db->query($sql, PDO::FETCH_ASSOC)->fetchAll();

		if (!is_array($items))
			return false;

		$objects = array();
		foreach ($items as $item) {
			array_push($objects, new $model_name($item, $associations));
		}

		return $objects;
	}

	protected function getOne($sql, $model_name)
	{
		$item = $this->db->query($sql, PDO::FETCH_ASSOC)->fetch();
		return (is_array($item) ? new $model_name($item) : false);
	}

	/*
	 * Method: instanciateCollection
	 *
	 * Helper method intended to instanciate an array of rows returned from a
	 * fetchAll() operation.
	 *
	 * Parameters:
	 *     data_collection - An assoiative array with rows from a fetchAll()
	 *     operation. Each row will be passed as the single argument to the
	 *     constructor function of the *model_name* class.
	 *     model_name - Which model to instanciate (e.g. <PhotoModel>).
	 *
	 * Returns:
	 *     An array with the instantiated objects.
	 */
	protected function instanciateCollection($data_collection, $model_name) {
		$items = array();

		foreach ($data_collection as $data_item) {
			array_push($items, new $model_name($data_item));
		}

		return $items;
	}
}
?>
