<?php
/*
 * Class: Controller
 *
 * This class handles an incoming request and acts on it. All controllers
 * should extend this class, and add actions to it. An action is the same as a
 * method in the class, e.g. a method named _show_ in a controller class means
 * that it's an action, _show_, for that controller.
 *
 * The most common thing to do in an action, at least at some point, is to
 * render a view. This is done by the <render> method. Controllers also usually
 * communicate with one or more <Model> classes.
 */
class Controller
{
	public function __construct($request = null)
	{
		$this->request = $request;
		$this->config = Config::getInstance();
	}

	/*
	 * Method: render
	 *
	 * Renders a view to the browser, with or without a layout.
	 *
	 * Parameters:
	 *     view_name - The name of the view-file. It should be placed at
	 *     _application/views/{view_name}.php_
	 *
	 *     data - An array with key, data pairs to be used in the view.
	 *
	 *     layout - Which layout to use. Layouts should recide in
	 *     _application/views/layouts_.
	 *
	 * Returns:
	 *     Will return false if something went horribly wrong, otherwise it
	 *     will return true.
	 */
	protected function render($view_name, $data = null, $layout = "default")
	{
		// Extract variables from the array into the scope of the view.
		if (is_array($data)) {
			extract($data, EXTR_SKIP | EXTR_REFS);
		} else {
			$data_name = split('/', $view_name);
			$data_name = array_shift($data_name);
			extract(array($data_name => $data));
		}


		// Suspend output so that we can put it in a string later.
		ob_start();

		// FIXME: Hardcoded application path.
		// Run through the view (with output going to the buffer).
		include(dirname(__FILE__)."/../application/views/".$view_name.".php");

		// Place the buffer contents in the variable and clear the buffer.
		$content_for_layout = ob_get_clean();

		// Now we check if a layout should be used and if it exists, else we'll
		// go ahead without a layout.
		if ($layout) {
			// FIXME: Hardcoded application path.
			include(dirname(__FILE__)."/../application/views/layouts/default.php");
		} else {
			echo $content_for_layout;
		}

		return true;
	}

	/*
	 * Method: renderPartial
	 *
	 * Basicly renders a layout as specified in <render> but will never use a
	 * layout.
	 *
	 * Parameters:
	 *     view_name - Same as in <render>.
	 *     data - Same as in <render>.
	 *
	 * Returns:
	 *     Nothing.
	 */
	protected function renderPartial($view_name, $data = null)
	{
		$this->render($view_name, $data, null);
	}

	/*
	 * Method: renderPartialCollection
	 *
	 * Will take a collection of "data-entries" and render a partial view for
	 * each of them. Optionally entries can be seperated with a seperator-view. 
	 * The rendered view will have the data available in the variable with the 
	 * same name as the view. E.g. if we render 'blog/post' the data will be in 
	 * the variable _$post_.
	 *
	 * Parameters:
	 *     view - Same as in <render>.
	 *
	 *     data - An array of data-entries (see <render>).
	 *
	 *     seperator_view_name - The name of a seperator view to be placed in
	 *     between each entry (that means that there will be no seperator after
	 *     the last entry).
	 */
	protected function renderPartialCollection($view, $data, $seperator = null)
	{
		$i = 1;
		foreach ($data as $item_data) {
			$data_name = split('/', $view);
			$data_name = array_pop($data_name);
			$this->renderPartial($view, array($data_name => $item_data));

			if ($seperator && $i++ < sizeof($data)) {
				$this->renderPartial($seperator);
			}
		}
	}

	/*
	 * Method: renderText
	 *
	 * Output some text to the browser.
	 *
	 * Parameters:
	 *     text - The text to be added to the output buffer.
	 */
	protected function renderText($text)
	{
		echo $text;
	}

	/*
	 * Method: redirect
	 *
	 * Will redirect the browser to a new location (sets the header 
	 * 'Location').
	 *
	 * Parameters:
	 *     url - A <Route> e.g. _blog/show/1_.
	 */
	protected function redirect($url)
	{
		header('Location: '.$this->config->getBasepath(true).'/'.$url);
	}

	protected function __toString()
	{
		return "I'm a controller.";
	}

	protected $request;
	protected $config;
}
