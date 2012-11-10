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

require_once __DIR__ . '/php-closure/php-closure.php';

/**
 * Main compiler class
 *
 * @version     31/08/2012
 * @package     Joomla.Plugin
 * @subpackage  System.Twbootstrap
 * @since       2.5
 *
 */
class BootstrapCompiler extends PhpClosure
{

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		$this->setDebug(false);
	}

	/**
	 * Get the name of the generated cache file name
	 *
	 * @return string
	 */
	function _getCacheFileName()
	{
		return $this->_cache_dir . 'bootstrap.min.js';
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

}
