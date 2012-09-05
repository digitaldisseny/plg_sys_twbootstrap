Twitter Bootstrap Plugin for Joomla!
===============

Twitter bootstrap plugin for Joomla 2.5.x.  

This plugin tries to simplify the Twitter Bootstrap integration for Joomla 2.5.x. Load all the required files and also enables some tweaks to improve the component and extensions template development.

Our recommendation is to use this plugin to load Bootstrap and disable any other bootstrap load.  

**Includes a patched bootstrap.min.js file to solve issues in collapsable items when Mootools & Bootstrap are both enabled** (We do not recommended you to use jQuery and Mootools together).

Version 
---------------
**Plugin version:** 1.0.3  
**Bootstrap Version:** 2.0.4  

Install
---------------
Clone this repository or just download from:  
[[Download Zip](https://github.com/digitaldisseny/plg_sys_twbootstrap/zipball/master)]  
[[Download tar.gz](https://github.com/digitaldisseny/plg_sys_twbootstrap/tarball/master)]  
Then install normally throught Joomla! Extension Manager as package (if you downloaded the compressed version) or from folder (if you cloned the repository).

Customize / Overrides
---------------
Since version 1.0.2 the plugin allows you to override the included CSS & JS files. Overrides can help you in some special cases:    
* You use patched/customized version of bootstrap/jQuery CSS or JS files (not recommended)  
* New versions of jQuery/bootstrap are released and you want to update them just NOW before this plugin is updated  
* This plugin is updated but that crashes all! You still can use overrides to force loading the old files  
  
If you need to use overrides:  
1. In your template create the folder  
    **templates/YOURTEMPLATE/plg_system_twbootstrap**  
2. Copy the js & css folders from   
    **plugins/system/twbootstrap**  
    to:  
        **templates/YOURTEMPLATE/html/plg_system_twbootstrap**  
3. You are done! The plugin will detect any file overriden and load it instead of plugin's default version

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
    
This way you will ensure that your bootstrap content is working well with and without the plugin enabled/installed.  

Now you can, for example, define bootstrap span columns depending on component available width:  

    switch ($bsComColumns) {
        case 4:
        case 5:
        case 8:
        case 12:
            $bsSpanColumns = 4;
            break;
        case 6:
        case 7:
        case 9:
        case 9:
        case 10:
        case 11:
            $bsSpanColumns = 3;
            break;
        default:
            $bsSpanColumns = $bsComColumns;
        break;
    }
    $bsSpanClass = 'span' . $bsSpanColumns; 

And then start the content display as:  

	<div class="<?php echo $bsContainerClass; ?>">
		<div class="bsRowClass">
			<?php foreach ($this->items as $item): ?>
				<div class="<?php echo bsSpanClass; ?>">
					<p><?php echo $item->title; ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	
You can improve the code to open new rows when span exceed the available space (required in fluid mode):

	<div class="<?php echo $bsContainerClass; ?>">
	    <div class="bsRowClass">
		    <?php
		        $availableColumns = $bsComColumns;
		        $openedRow = true;
		    ?>
			<?php foreach ($this->items as $item): ?>
			    <?php if($availableColumns < $bsSpanColumns):?>
			        <?php if($openedRow):?>
			            </div>
			        <?php endif; ?>
			        <div class="bsRowClass">
			        <?php
			            $availableColumns = $bsComColumns;
                        $openRow = true;
                    ?>
			    <?php endif; ?>
				<div class="<?php echo $bsSpanClass; ?>">
					<p><?php echo $item->title; ?></p>
				</div>
				<?php $availableColumns -= $bsSpanColumns; ?>
			<?php endforeach; ?>
		</div>
	</div>
	
This way you ensure that your content is going to be shown allways as expected and you allow the user to select the desired bootstrap mode.  

Release History
---------------
1.0.3. -> Added spanish language & fix code to be Joomla! standard compliant.  
1.0.2. -> Add template overrides support.  
1.0.1. -> Bug fixes and selectable CSS/JS inject position  
1.0.0. -> First stable version  

What's next?
---------------
We are going to use this plugin as the bootstrap base of all our developments. We plan to keep this plugin updated with the latest Twitter Bootstrap versions. We also want to improve it and add some tweaks as:  
* Allow the user to set the component columns width per menu item. 
* Create a backend CSS override to ensure that Joomla 2.5.x works with bootstrap.
* Solve future Mootools / Bootstrap conflicts     