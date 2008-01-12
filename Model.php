<?php
class Model {
	public function __construct($attr = null, $associations = null) {
		$this->attr = $attr;
		$this->relations = array();

		if (is_array($associations)) {
			foreach ($associations as $name => $ass) {
				$this->relations[$name] = $ass;
			}
		}
	}

	public function __get($name) {
		if (isset($this->attr[$name])) {
			return $this->attr[$name];
		} else {
			return false;
		}
	}

	public function __toString() {
		return "I'm a model!";
	}

	protected $attr;
	protected $relations;
}
?>
