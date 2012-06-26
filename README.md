Joomla Twitter Bootstrap Plugin
===============

Twitter bootstrap plugin for Joomla 2.5.  
This plugin tries to simplify the Twitter Bootstrap integration for Joomla 2.5.x. Load all the required files and also enables some tweaks to improve the component and extensions template development.

Version 
---------------
**Plugin version:** 1.0.0  
**Bootstrap Version:** 2.0.4  

Install
---------------
Clone this repository or just download from:  
[[Download Zip](https://github.com/digitaldisseny/plg_sys_twbootstrap/zipball/master)]  
[[Download tar.gz](https://github.com/digitaldisseny/plg_sys_twbootstrap/tarball/master)]  
Then install normally throught Joomla! Extension Manager as package (if you downloaded the compressed version) or from folder (if you cloned the repository).

Template development improvements
---------------
Plugin defines some constants to make easier template development and avoid views duplicities for static and fluid bootstrap modes. Constants are available to use them anywhere but are designed to use in template views.  
Current constants:  
**BOOTSTRAP_VERSION** : Shows the bootstrap loaded version  
**BOOTSTRAP_COM_COLUMNS** : Shows the available columns for component. This allows us to customize views depending on available columns (see example below).  
**BOOTSTRAP_CONTAINER_CLASS** : Container class for current bootstrap mode.  
**BOOTSTRAP_ROW_CLASS** : Row class for current bootstrap mode.  

An example template view would start with:  

    $bsContainerClass = defined('BOOTSTRAP_CONTAINER_CLASS') ? BOOTSTRAP_CONTAINER_CLASS : 'container';  
    $bsRowClass = defined('BOOTSTRAP_ROW_CLASS') ? BOOTSTRAP_ROW_CLASS : 'row';  
    $bsComColumns = defined('BOOTSTRAP_COM_COLUMNS') ? BOOTSTRAP_COM_COLUMNS : 12;

Now you can, for example, define bootstrap span columns depending on component available width:  

    switch ($bsComColumns) {
        case 8:
        case 12:
            $bsSpanColumns = 4;
            break;
        case 9:
        case 10:
        case 11:
            $bsSpanColumns = 3;
            break;
        default:
            $bsSpanColumns = 4;
        break;
    }
    $bsSpanClass = 'span' . $bsSpanColumns;  

And then start the content display as:  

	<div class="<?php echo $bsContainerClass; ?>">
		<div class="bsRowClass">
			<?php foreach ($items as $item): ?>
				<div class="<?php echo bsSpanClass; ?>">
					<p><?php echo $item->title; ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>