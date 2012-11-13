<?php
/**
 * Custom class to compile Bootstrap with php-closure
 * (http://code.google.com/p/php-closure/)
 *
 * @package     Joomla.Plugin
 * @subpackage  System.Twbootstrap
 *
 * @author      Roberto Segura <roberto@phproberto.com>
 * @copyright   (c) 2012 Roberto Segura. All Rights Reserved.
 * @license     GNU/GPL 2, http://www.gnu.org/licenses/gpl-2.0.htm
 * @link        http://digitaldisseny.com/en/extensions/twitter-bootstrap-plugin-joomla
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/php-closure.php';

/**
 * Main compiler class
 *
 * @version     31/08/2012
 * @package     Joomla.Plugin
 * @subpackage  System.Twbootstrap
 * @since       2.5
 *
 */
class MyPhpClosure extends PhpClosure
{

	var $_updated = null;
	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->setDebug(false);
	}

	/**
	 * Set the debug mode ON/OFF
	 *
	 * @param   boolean  $status  desired debug mode
	 *
	 * @return  void
	 */
	function setDebug($status = false)
	{
		$this->_debug = $status;
	}

	function setUpdated($date)
	{
		$this->_updated = $date;
	}

	/**
	 * Get the name of the generated cache file name
	 *
	 * @return string
	 */
	function _getCacheFileName()
	{
		return $this->_cache_dir . '/bootstrap-custom.min.js';
	}

	/**
	 * Determine if the compiled JS has to be updated/recompiled
	 *
	 * @param   string  $cache_file  file that stores the cache
	 *
	 * @return boolean             [description]
	 */
	function _isRecompileNeeded($cache_file)
	{
		// If there is no cache file, we obviously need to recompile.
		if (!file_exists($cache_file))
		{
			return true;
		}

		$cache_mtime = filemtime($cache_file);
		if (!is_null($this->_updated) && strtotime($this->_updated) > $cache_mtime)
		{
			return true;
		}

		// If the source files are newer than the cache file, recompile.
		foreach ($this->_srcs as $src)
		{
			if (filemtime($src) > $cache_mtime)
			{
				return true;
			}
		}

		/**
		 * If this script calling the compiler is newer than the cache file,
		 * recompile.  Note, this might not be accurate if the file doing the
		 * compilation is loaded via an include().
		 */
		if (filemtime($_SERVER["SCRIPT_FILENAME"]) > $cache_mtime)
		{
			return true;
		}

		// Cache is up to date.
		return false;
	}
}
