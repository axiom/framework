<?php
/*
 * Class: Model
 *
 * This is the base class for domain specific models. Pretty basic really.
 */
class Model
{
	/*
	 * Method: __construct
	 *
	 * Sets up the attributes and associations.
	 *
	 * Parameters:
	 *     attr - The attributes of the object i.e. an associative array.
	 *     associations - Associations to other objects (or anything really).
	 *     I.e. an associative array of stuff, much like attributes but can be
	 *     done later too.
	 */
	public function __construct($attr = null, $associations = null)
	{
		$this->attr = $attr;
		$this->relations = array();

		if (is_array($associations)) {
			foreach ($associations as $name => $ass) {
				$this->relations[$name] = $ass;
			}
		}
	}

	/*
	 * Method: __get
	 *
	 * Magic method that maps from $model->name to $model->attr['name'].
	 *
	 * Parameters:
	 *     name - The attribute name to get from the object (i.e. a table field
	 *     from the database).
	 */
	public function __get($name)
	{
		if (isset($this->attr[$name])) {
			return $this->attr[$name];
		} else {
			return false;
		}
	}

	public function __toString()
	{
		return "I'm a model!";
	}

	protected $attr;
	protected $relations;
}
?>
