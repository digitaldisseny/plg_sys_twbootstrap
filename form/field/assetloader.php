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

defined('_JEXEC') or die;

JLoader::import('joomla.form.formfield');

class JFormFieldAssetLoader extends JFormField
{

	var	$type = 'assetloader';

	function getInput()
	{
	    $doc = JFactory::getDocument();
	    $css = "
	    .twbs-backend-label {
			background-color: #999999;
			clear: both;
			color: #FFFFFF;
			padding: 5px;
	    }
	    ";
	    $doc->addStyleDeclaration($css);
		return '';
	}

	function getLabel()
	{
		return '';
	}

}