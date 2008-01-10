<?php
class Model {
	public function __construct($attr = null) {
		$this->attr = $attr;
		$this->relations = array();
		$this->load = Loader::getInstance();
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
	protected $load;
}
?>
