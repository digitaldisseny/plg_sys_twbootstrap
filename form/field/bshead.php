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

class JFormFieldBshead extends JFormField
{
	var	$type = 'bshead';

	function getInput()
	{
		return '';
	}

	function getLabel()
	{
		return '<h4 class="twbs-backend-label">' . JText::_((string) $this->element['label']) . ':</h4>';
	}

}