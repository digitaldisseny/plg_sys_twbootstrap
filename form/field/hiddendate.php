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

/**
 * Custom field: Hidden current date
 *
 * @version     31/08/2012
 * @package     Joomla.Plugin
 * @subpackage  System.Twbootstrap
 * @since       2.5
 *
 */
class JFormFieldHiddendate extends JFormField
{

	var	$type = 'hiddendate';

	/**
	 * Get the input HTML code
	 *
	 * @return string input field for the form
	 */
	function getInput()
	{
		$bootstrapJs = JURI::root() . 'plugins/system/twbootstrap/js/bootstrap-custom.min.js';
		$js = "
		    window.addEvent('domready', function() {
		    	// To standarize the field get the form named adminForm
			    document.getElement('form[name=adminForm]').addEvent('submit', function(e) {
					var d=new Date();
					// Date in SQL format
			    	var strtonow = d.getFullYear() + '-' + (d.getMonth()+1) + '-' + d.getDate() + ' ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds();
			    	$('" . $this->id . "').set('value', strtonow);
			    	//alert($('" . $this->id . "').get('value'));
			    });
	    	});
		";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
		return '<input id="' . $this->id . '" type="hidden" name="' . (string) $this->name . ' " value="' . date("Y-m-d H:i:s") . '">';
	}

	/**
	 * Get the label of the field
	 *
	 * @return string Label to display before the input
	 */
	function getLabel()
	{
		return '';
	}
}
