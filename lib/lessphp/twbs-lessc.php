<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Twbootstrap
 *
 * @author      Roberto Segura <roberto@phproberto.com>
 * @copyright   (c) 2012 Roberto Segura. All Rights Reserved.
 * @license     GNU/GPL 2, http://www.gnu.org/licenses/gpl-2.0.htm
 * @link        http://digitaldisseny.com/en/extensions/twitter-bootstrap-plugin-joomla
 */
require_once dirname(__FILE__) . '/lessc.inc.php';

/**
 * lessphp extended class to compile less files
 *
 * @version     31/08/2012
 * @package     Joomla.Plugin
 * @subpackage  System.Twbootstrap
 * @since       2.5
 *
 */
class JoomlaLessCompiler extends lessc
{
	var $sourceCss = null;

	var $outputCss = null;

	var $sourceFiles = array();

	var $errors = array();

	private $_updated = null;

	/**
	 * Add a file to be processed
	 *
	 * @param   string  $filepath  The file path
	 *
	 * @return  boolean
	 */
	public function addLessFile($filepath)
	{
		if (file_exists($filepath))
		{
			$this->sourceFiles[] = $filepath;
			return true;
		}
		$this->errors[] = 'file ' . $filepath . ' doesnt exist';
		return false;
	}

	/**
	 * Generate the output CSS from the source files
	 *
	 * @return [type] [description]
	 */
	public function generateCss()
	{
		if (!empty($this->sourceFiles))
		{
			foreach ($this->sourceFiles as $filepath)
			{
				try
				{
					$this->outputCss .= @$this->compile(file_get_contents($filepath));
				}
				catch (exception $e)
				{
					$this->errors[] = $e->getMessage();
				}
			}
			return $this->outputCss;
		}

		$this->errors[] = 'No source LESS files were added';
		return false;
	}

	/**
	 * Generate the CSS and save it to file
	 *
	 * @param   string  $outputFile  The destination CSS file
	 *
	 * @return boolean
	 */
	public function createCssFile($outputFile)
	{
		if ($this->_isRecompileNeeded($outputFile))
		{
			$output = $this->generateCss();
			if ($output !== false)
			{
				try
				{
					if (touch($outputFile))
					{
						file_put_contents($outputFile, $output);
					}
				}
				catch (exception $e)
				{
					$this->errors[] = $e->getMessage();
					return false;
				}
				return true;
			}
		}
		return true;
	}

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
		foreach ($this->sourceFiles as $src)
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

	function setUpdated($date)
	{
		$this->_updated = $date;
	}
}
