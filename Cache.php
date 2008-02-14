<?php
/*
 * Class: Cache
 *
 * This class is responsible for handling the caching of the application. It's
 * use is controlled by the configuration file. There is an option _use_cache_
 * that can be either true or false, when true the caching is enabled when
 * false it's disabled.
 *
 * There is also an option for activating image caching: _use_image_cache_ which
 * will, when enabled, cache images for a period of 30 days.
 *
 * The caching mechanism works on a URI basis. Each request is identified
 * (with the request uri) and this uri together with a timestamp (of sorts) is
 * then hashed by the sha1  algorithm. A cache file is created by redirecting
 * the output buffer to the cache file.
 *
 * If caching is enabled and a cache file exists for a given identifier, then
 * that file is served to the output buffer. Otherwise the cache file will be
 * created. However if the browser use the If-None-Match header we can skip
 * sending the file.
 *
 * FIXME: The caching mechanism are quite dumb, it does not allow any dynamic
 * interaction with the server. This is something that must be fixed to be more
 * clever.
 */
class Cache {

	/*
	 * Constructor: __construct
	 *
	 * Initialize the class.
	 *
	 * Parameters:
	 *     request - An instance of the <Request> class. (Used to get URI and
	 *     headers.)
	 */
	public function __construct($request)
	{
		$this->config = Config::getInstance();
		$this->request = $request;
		$this->identifier = $this->request->getURI();

		// FIXME: Quite crude identification of images.
		if (strpos($this->identifier, 'thumbnail') !== false) {
			// FIXME: Configuration setting?
			// The timespan a cached file is considered valid. (30 days).
			$time = round(time() / (60 * 60 * 24 * 30));
		} else {
			if (!$this->config->useCache()) {
				return false;
			}

			// FIXME: Configuration setting?
			// The timespan a cached file is considered valid.
			$time = round(time() / (60 * 60));
		}

		// Generate an identifier that can be used as a file descriptor.
		// $this->identifier = sha1($this->identifier . $time);
		$this->identifier = $this->getIdentifier($this->identifier);

		// Check if the client already have the file in it's local cache. If it
		// does and the cache file is up-to-date we can send a 304 Not Modified
		// reply (which is nice).
		if ($request->getHeader('If-None-Match') == $this->identifier) {
			header("HTTP/1.1 304 Not Modified");
			header('ETag: '.$this->identifier);
			$this->served = true;
			return true;
		}

		$this->cacheDir = $this->config->getCacheDir();
		$this->cacheFile = $this->cacheDir.'/'.$this->identifier;

		// If we've the file in our cache just serve that and be done with it.
		if (is_file($this->cacheFile) && is_readable($this->cacheFile)) {
			$this->serveCache();
			$this->served = true;
			return true;
		}

		else if (is_writable($this->cacheDir)) {
			$this->active = true;
			ob_start();
			// Tag the file so we can serve cache next time.
			header('ETag: '.$this->identifier);
			return true;
		}
		// We couldn't write to the cache directory, we need to make someone
		// aware of this since it effectively disables the whole caching
		// mechanism.
		else {
			throw new FrameworkException(FrameworkException::CACHE_DIR_NOT_WRITEABLE,
				"Kunde inte skriva till cache katalogen, kontrollera rättigheterna.");
		}
	}

	/*
	 * Destructor: __destruct
	 *
	 * Collects the output from the application and writes it to disc and serves
	 * it to the browser.
	 */
	public function __destruct()
	{
		if ($this->active && ob_get_level() && ob_get_length()) {
			mkdir(dirname($this->cacheFile), 0755, true);
			$fh = fopen($this->cacheFile, 'w');
			fwrite($fh, ob_get_contents());
			fclose($fh);
			ob_end_flush();
			$this->active = false;
		}
		return true;
	}

	/*
	 * Method: isActive
	 *
	 * Returns:
	 *     True if caching is active, false otherwise.
	 */
	public function isActive()
	{
		return $this->active;
	}

	/*
	 * Method: isServed
	 *
	 * Returns:
	 *     True if the cache-file has been served to the browser, false
	 *     otherwise.
	 */
	public function isServed()
	{
		return $this->served;
	}

	private $active = false;
	private $cacheFile;
	private $cacheDir;
	private $served = false;
	private $identifier;
	private $request;

	/*
	 * Method: serveCache
	 *
	 * Serves the cache-file to the browser and sets the ETag header for caching
	 * purposes.
	 */
	private function serveCache()
	{
		header('ETag: '.$this->identifier);
		readfile($this->cacheFile);
		return;
	}

	private function getIdentifier($uri)
	{
		$uri = urldecode($uri);

		$parts = explode('/', $uri);

		foreach ($parts as $i => $part) {
			$parts[$i] = trim(preg_replace('/[^a-zA-Z0-9._-]/', '_', $part), '_/');
		}

		$uri = trim(implode('/', $parts), '/_');

		if ($uri == '') {
			$uri = 'index';
		}

		if (strpos($uri, '.jpg') === false &&
		    strpos($uri, '.png') === false) {
			$uri = $uri.'.html';
		}

		return $uri;
	}
}
?>
