<?php
/*
 * Class: Cache
 *
 * This class is responsible for handling the caching of the application. It's
 * use is controlled by the configuration file. There is an option _use_cache_
 * that can be either true or false, when true the caching is enabled when
 * false it's disabled.
 *
 * The caching mechanism works on a file basis. Each request is identified
 * (with the request uri) and the identifier is then hashed by the sha1
 * algorithm. A cache file is created by redirecting the output buffer to the
 * cache file.
 *
 * If caching is enabled and a cache file exists for a given identifier, then
 * that file is served to the output buffer. Otherwise the cache file will be
 * created.
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
	 *     identifier - A string that should identify the requested resources
	 *     e.g. _blog/show/1_
	 */
	public function __construct($request) {
		$this->config = Config::getInstance();
		$this->request = $request;
		$this->identifier = $this->request->getURI();

		if (strpos($this->identifier, 'thumbnail') !== false) {
			$time = round(time() / (60 * 60 * 24 * 30));
		} else {
			if (!$this->config->useCache()) {
				return false;
			}

			$time = round(time() / (60 * 60));
		}

		// Generate an identifier that can be used as a file descriptor.
		$this->identifier = sha1($this->identifier . $time);

		// Check if the client has cached the file already.
		if ($request->getHeader('If-None-Match') == $this->identifier) {
			header("HTTP/1.1 304 Not Modified");
			header('ETag: '.$this->identifier);
			$this->served = true;
			return true;
		}

		$this->cacheDir = $this->config->getCacheDir();

		// Construct the cache filename.
		$this->cacheFile = $this->cacheDir.'/'.$this->identifier;

		// Can we already serve the cache file?
		if (is_file($this->cacheFile) && is_readable($this->cacheFile)) {
			$this->serveCache();
			$this->served = true;
			return true;
		}

		// Check if we can write to the cache directory, if not we should make
		// someone aware of this since it disables the caching mechanism.
		else if (is_writable($this->cacheDir)) {
			$this->active = true;
			ob_start();
			// Tag the file so we can serve cache next time.
			header('ETag: '.$this->identifier);
			return true;
		}
		// TODO: Raise hell!
		else {
			echo "Couldn't write to cache directory.";
			return false;
		}
	}

	/*
	 * Destructor: __destruct
	 *
	 * Closes any open cache-files and buffers.
	 */
	public function __destruct() {
		if ($this->active) {
			$fh = fopen($this->cacheFile, 'w');
			fwrite($fh, ob_get_contents());
			fclose($fh);
			ob_end_flush();
			$this->active = false;
		}
		return true;
	}

	/*
	 * Method: serveCache
	 *
	 * Serves up the cache-file to the browser.
	 */
	private function serveCache() {
		header('ETag: '.$this->identifier);
		header('Content-Type: image/jpeg');
		readfile($this->cacheFile);
		return;
	}

	/*
	 * Method: isActive
	 *
	 * Returns:
	 *     True if caching has been activated, false otherwise.
	 */
	public function isActive() {
		return $this->active;
	}

	/*
	 * Method: isServed
	 *
	 * Returns:
	 *     True if the cache-file has been served to the browser, false
	 *     otherwise.
	 */
	public function isServed() {
		return $this->served;
	}

	/*
	 * Method: getCacheFile
	 *
	 * Returns:
	 *     Filename for the specified cache-resource. It's implemented as a
	 *     sha1-hash of the cache-resources.
	 */
	public static function getCacheFile($uri) {
		return sha1($uri);
	}

	private $active = false;
	private $cacheFile;
	private $cacheDir;
	private $served = false;
	private $identifier;
	private $request;
}
?>
