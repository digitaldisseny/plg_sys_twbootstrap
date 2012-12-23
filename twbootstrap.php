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

defined('_JEXEC') or die( 'Restricted access' );

JLoader::import('joomla.plugin.plugin');
JLoader::import('joomla.filesystem.file');
JLoader::import('joomla.filesystem.folder');

/**
 * Main plugin class
 *
 * @version     31/08/2012
 * @package     Joomla.Plugin
 * @subpackage  System.Twbootstrap
 * @since       2.5
 *
 */
class PlgSystemTwbootstrap extends JPlugin
{
	private $_params = null;

	// Plugin info constants
	const TYPE = 'system';

	const NAME = 'twbootstrap';

	// Paths
	private $_pathPlugin    = null;

	private $_pathTemplate  = null;

	private $_pathOverrides = null;

	// URLs
	private $_urlPlugin       = null;

	private $_urlJs           = null;

	private $_urlCss          = null;

	private $_urlOverrides    = null;

	private $_urlJsOverrides  = null;

	private $_urlCssOverrides = null;

	// CSS & JS scripts calls
	private $_cssCalls = array();

	private $_jsCalls  = array();

	private $_bootstrapCssFiles = array(
			// CSS Reset
			'cssReset'        => 'reset.less',
			// Core variables and mixins
			'cssVariables'    => 'variables.less',
			'cssMixins'       => 'mixins.less',
			// Grid system and page structure
			'cssScafolding'   => 'scaffolding.less',
			'cssGrid'         => 'grid.less',
			'cssLayouts'      => 'layouts.less',
			// Grid system and page structure
			'cssType'         => 'type.less',
			'cssCode'         => 'code.less',
			'cssForms'        => 'forms.less',
			'cssTables'       => 'tables.less',
			// Grid system and page structure
			'cssSprites'      => 'sprites.less',
			'cssDropdowns'    => 'dropdowns.less',
			'cssWells'        => 'wells.less',
			'cssAnimations'   => 'component-animations.less',
			'cssClose'        => 'close.less',
			// Grid system and page structure
			'cssButtons'      => 'buttons.less',
			'cssButtonGroups' => 'button-groups.less',
			'cssAlerts'       => 'alerts.less',
			// Components: Nav
			'cssNavs'         => 'navs.less',
			'cssNavbars'      => 'navbar.less',
			'cssBreadcrumbs'  => 'breadcrumbs.less',
			'cssPagination'   => 'pagination.less',
			'cssPager'        => 'pager.less',
			// Components: Popovers
			'cssModals'       => 'modals.less',
			'cssTooltips'     => 'tooltip.less',
			'cssPopovers'     => 'popovers.less',
			// Components: Misc
			'cssThumbnails'   => 'thumbnails.less',
			'cssMedia'        => 'media.less',
			'cssLabelsBadges' => 'labels-badges.less',
			'cssProgressBars' => 'progress-bars.less',
			'cssAccordion'    => 'accordion.less',
			'cssCarousel'     => 'carousel.less',
			'cssHeroUnit'     => 'hero-unit.less',
			// Utility classes
			'jsTypeahead'     => 'utilities.less',
			// Responsive
			'cssResponsive'   => 'responsive-utilities.less',
			'cssResp1200aMin' => 'responsive-1200px-min.less',
			'cssResp768a979'  => 'responsive-768px-979px.less',
			'cssResp767aMax'  => 'responsive-767px-max.less',
			'cssRespNav'      => 'responsive-navbar.less'
	);

	private $_bootstrapJsFiles = array(
			'jsAffix'      => 'bootstrap-affix.js',
			'jsAlert'      => 'bootstrap-alert.js',
			'jsButton'     => 'bootstrap-button.js',
			'jsCarousel'   => 'bootstrap-carousel.js',
			'jsCollapse'   => 'bootstrap-collapse.js',
			'jsDropdown'   => 'bootstrap-dropdown.js',
			'jsModal'      => 'bootstrap-modal.js',
			'jsTooltip'    => 'bootstrap-tooltip.js',
			'jsPopover'    => 'bootstrap-popover.js',
			'jsScrollspy'  => 'bootstrap-scrollspy.js',
			'jsTab'        => 'bootstrap-tab.js',
			'jsTransition' => 'bootstrap-transition.js',
			'jsTypeahead'  => 'bootstrap-typeahead.js'
	);

	// HTML positions & associated regular expressions
	private $_htmlPositions = array(
			'headtop' => array( 'pattern' => "/(<head>)/isU",
								'replacement' => "$1\n\t##CONT##"),
			'headbottom' => array(  'pattern' => "/(<\/head>)/isU",
									'replacement' => "\n\t##CONT##\n$1"),
			'bodytop' => array( 'pattern' => "/(<body)(.*)(>)/isU",
								'replacement' => "$1$2$3\n\t##CONT##"),
			'bodybottom' => array(  'pattern' => "/(<\/body>)/isU",
									'replacement' => "\n\t##CONT##\n$1"),
			'belowtitle' => array(  'pattern' => "/(<\/title>)/isU",
									'replacement' => "$1\n\t##CONT##")
		);

	private $_htmlPositionsAvailable = array();

	/**
	 * Constructor
	 *
	 * @param   string  $subject  current identifier
	 */
	function __construct( $subject )
	{
		parent::__construct($subject);

		// Set the HTML available positions
		$this->_htmlPositionsAvailable = array_keys($this->_htmlPositions);

		// Load plugin parameters
		$this->_plugin = JPluginHelper::getPlugin(self::TYPE, self::NAME);
		$this->_params = new JRegistry($this->_plugin->params);

		// Init folder structure
		$this->_initFolders();

		// Load plugin language
		$this->loadLanguage('plg_' . self::TYPE . '_' . self::NAME, JPATH_ADMINISTRATOR);

	}

	/**
	 * This event is triggered after the framework has loaded and the application initialise method has been called.
	 * http://docs.joomla.org/Plugin/Events/System
	 *
	 * @return boolean
	 */
	function onAfterInitialise()
	{
		// Plugin parameters
		$comColumns    = $this->_params->get('comColumns', 12);
		$bootstrapMode = $this->_params->get('bootstrapMode', 'fluid');

		// Generate row and column classes
		switch ($bootstrapMode)
		{
			case 'fluid':
				$bootstrapContainerClass = 'container-fluid';
				$bootstrapRowClass = 'row-fluid';
				break;
			default:
				$bootstrapContainerClass = 'container';
				$bootstrapRowClass = 'row';
				break;
		}

		// Define constants | check if defined to allow override
		if (!defined('BOOTSTRAP_VERSION'))
		{
			define('BOOTSTRAP_VERSION', '2.0.4');
		}
		if (!defined('BOOTSTRAP_COM_COLUMNS'))
		{
			define('BOOTSTRAP_COM_COLUMNS', $comColumns);
		}
		if (!defined('BOOTSTRAP_CONTAINER_CLASS'))
		{
			define('BOOTSTRAP_CONTAINER_CLASS', $bootstrapContainerClass);
		}
		if (!defined('BOOTSTRAP_ROW_CLASS'))
		{
			define('BOOTSTRAP_ROW_CLASS', $bootstrapRowClass);
		}

	}

	/**
	 * This event is triggered immediately before pushing the document buffers into the template placeholders,
	 * retrieving data from the document and pushing it into the into the JResponse buffer.
	 * http://docs.joomla.org/Plugin/Events/System
	 *
	 * @return boolean
	 */
	function onBeforeRender()
	{
		/*
		// Validate view
		if (!$this->_validateUrl())
		{
			return true;
		}
		*/
		// Required objects
		$app        = JFactory::getApplication();
		$doc        = JFactory::getDocument();

		if (!$app->isSite())
		{
			return true;
		}
		$pageParams = $app->getParams();

		// Disable Default Bootstrap
		unset($doc->_scripts[JURI::root(true) . '/media/jui/js/bootstrap.min.js']);

		// If we are going to load jQuery disable any default jQuery loaded
		if ($this->_params->get('loadJquery', 0))
		{
			unset($doc->_scripts[JURI::root(true) . '/media/jui/js/jquery.min.js']);
			unset($doc->_scripts[JURI::root(true) . '/media/jui/js/jquery-noconflict.js']);
		}

		// Check if we have to disable Mootools for this item
		$bsEnabled = $pageParams->get('twbs_enabled', $this->_params->get('defaultMode', 0));
		if ($bsEnabled)
		{
			// Function used to replace window.addEvent()
			$doc->addScriptDeclaration("function do_nothing() { return; }");

			// Disable mootools javascript
			/*
			unset($doc->_scripts[JURI::root(true) . '/media/system/js/mootools-core.js']);
			unset($doc->_scripts[JURI::root(true) . '/media/system/js/mootools-more.js']);
			unset($doc->_scripts[JURI::root(true) . '/media/system/js/core.js']);
			unset($doc->_scripts[JURI::root(true) . '/media/system/js/caption.js']);
			unset($doc->_scripts[JURI::root(true) . '/media/system/js/modal.js']);
			unset($doc->_scripts[JURI::root(true) . '/media/system/js/mootools.js']);
			unset($doc->_scripts[JURI::root(true) . '/plugins/system/mtupgrade/mootools.js']);
*/
			// Disable css stylesheets
			unset($doc->_styleSheets[JURI::root(true) . '/media/system/css/modal.css']);

			// Disable 3rd party extensions added by the user
			if ($manualDisable = $this->_params->get('manualDisable', null))
			{
				$scripts = explode(',', $manualDisable);
				foreach ($scripts as $script)
				{
					// Try to disable relative and full URLs
					unset($doc->_scripts[$script]);
					unset($doc->_scripts[JURI::root(true) . $script]);
				}
			}
		}
		return true;
	}

	/**
	 * This event is triggered after pushing the document buffers into the template placeholders,
	 * retrieving data from the document and pushing it into the into the JResponse buffer.
	 * http://docs.joomla.org/Plugin/Events/System
	 *
	 * @return boolean
	 */
	function onAfterRender()
	{
		// Required objects
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		// URL params
		$jinput = $app->input;
		$tmpl   = $jinput->get('tmpl', null, 'cmd');

		// Plugin parameters
		$loadFrontBack  = $this->_params->get('loadFrontBack', 'frontend');
		$onlyHTML       = $this->_params->get('onlyHTML', 1);
		$disableModal   = $this->_params->get('disableModal', 1);
		$loadJquery     = $this->_params->get('loadJquery', 0);
		$loadBootstrap  = $this->_params->get('loadBootstrap', 0);
		$injectPosition = $this->_params->get('injectPosition', 'headtop');
		$updated 		= $this->_params->get('updated', '0000-00-00 00:00:00');

		// Check modals
		$disabledTmpls = array('component', 'raw');
		if ($disableModal && in_array($tmpl, $disabledTmpls))
		{
			return true;
		}

		// Check HTML only
		if ($onlyHTML && $doc->getType() != 'html')
		{
			return true;
		}

		// Site modifications
		if ( ($app->isSite() && ($loadFrontBack == 'frontend' || $loadFrontBack == 'both'))
			|| ($app->isAdmin() && ($loadFrontBack == 'backend' || $loadFrontBack == 'both')) )
		{

			// Load jQuery ? jQuery is added to header to avoid non-ready errors
			if ($loadJquery)
			{
				switch ($loadJquery)
				{
					// Load jQuery locally
					case 1:
						$jquery = $this->_urlJs . '/jquery.min.js';
						break;

					// Load jQuery from Google
					default:
						$jquery = 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js';
					break;
				}

				// Add script to header
				$this->_addJsCall($jquery, $injectPosition);
				$this->_addJsCall('jQuery.noConflict();', $injectPosition, 'script');
			}

			// Load Bootstrap ?
			if ($loadBootstrap)
			{
				// Bootstrap CSS - loaded in header
				$bootstrapCss = $this->_urlCss . '/bootstrap.min.css';

				// Bootstrap responsive CSS
				$bootstrapResponsiveCss = $this->_urlCss . '/bootstrap-responsive.min.css';

				$activeJsFiles  = $this->getBootstrapActiveJsFiles();
				$activeCssFiles = $this->getBootstrapActiveCssFiles();

				if (count($activeCssFiles) != count($this->_bootstrapCssFiles))
				{
					$sourceDir      = dirname(__FILE__) . '/less';
					$outputDir      = dirname(__FILE__) . '/css';
					$outputFilename = 'boostrap-custom.min.css';

					require_once __DIR__ . '/lib/lessphp/twbs-lessc.php';
					$lessc = new JoomlaLessCompiler;
					$lessc->setUpdated($updated);
					$lessc->setFormatter("compressed");
					$sourceCss = '';
					$outputCss = '';
					foreach ($activeCssFiles as $file)
					{
						$lessc->addLessFile($sourceDir . '/' . $file);
					}

					try
					{
						$lessc->createCssFile($outputDir . '/' . $outputFilename);
						$bootstrapCss           = $this->_urlCss . '/' . $outputFilename;
						$bootstrapResponsiveCss = null;
					}
					catch (exception $e)
					{
						echo 'LESS compile error: ' . implode('<br />', $lessc->errors);
					}
				}
				$this->_addCssCall($bootstrapCss, $injectPosition);
				if (!is_null($bootstrapResponsiveCss))
				{
					$this->_addCssCall($bootstrapResponsiveCss, $injectPosition);
				}

				// User has chosen some files (and not all to be loaded)
				if ($activeJsFiles && count($activeJsFiles) != count($this->_bootstrapJsFiles))
				{
					require_once __DIR__ . '/lib/php-closure/my-php-closure.php';
					$jsCompiler = new MyPhpClosure;
					foreach ($activeJsFiles as $file)
					{
						$jsCompiler->add(__DIR__ . '/js/bootstrap/' . $file);
					}
					$jsCompiler->simpleMode();

					// Advanced mode fails to compile bootstrap | $c->advancedMode()
					$jsCompiler->useClosureLibrary();

					$jsCompiler->setUpdated($updated);

					// TODO : Change or make sure path exists and is writable.
					$jsCompiler->cacheDir(__DIR__ . '/js');
					ob_start();
					$jsCompiler->write();
					$content = ob_end_clean();

					// Load the Bootstrap customized version
					$bootstrapJs = $this->_urlJs . '/bootstrap-custom.min.js';
				}
				else
				{
					// Load the Bootstrap standard version
					$bootstrapJs = $this->_urlJs . '/bootstrap.min.js';
				}

				// Bootstrap JS - loaded before body ending
				$this->_addJsCall($bootstrapJs, 'bodybottom');
			}

		}

		// CSS load
		if (!empty($this->_cssCalls))
		{
			$this->_loadCSS();
		}

		// JS load
		if (!empty($this->_jsCalls))
		{
			$this->_loadJS();
		}

		return true;
	}

	/**
	 * Initialize required folder structure
	 *
	 * @return none
	 *
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 28/06/2012
	 *
	 */
	private function _initFolders()
	{

		// Active template
		$currentTemplate = $this->getCurrentTplName();

		// Paths
		$this->_pathPlugin = JPATH_PLUGINS . '/' . self::TYPE . '/' . self::NAME;
		$this->_pathTemplate = JPATH_THEMES . '/' . $currentTemplate;
		$this->_pathOverrides = $this->_pathTemplate . '/html/plg_' . self::TYPE . '_' . self::NAME;

		// URLs
		$this->_urlPlugin       = JURI::root(true) . "/plugins/" . self::TYPE . "/" . self::NAME;
		$this->_urlJs           = $this->_urlPlugin . "/js";
		$this->_urlCss          = $this->_urlPlugin . "/css";
		$this->_urlOverrides    = JURI::root(true) . '/templates/' . $currentTemplate
								. '/html/plg_' . self::TYPE . '_' . self::NAME;
		$this->_urlCssOverrides = $this->_urlOverrides . '/css';
		$this->_urlJsOverrides  = $this->_urlOverrides . '/js';
	}

	/**
	 * Load / inject CSS
	 *
	 * @return none
	 *
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 27/06/2012
	 *
	 */
	private function _loadCSS()
	{
		if (!empty($this->_cssCalls))
		{
			$body = JResponse::getBody();
			foreach ($this->_cssCalls as $position => $cssCalls)
			{
				if (!empty($cssCalls))
				{
					// If position is defined we append code (inject) to the desired position
					if (in_array($position, $this->_htmlPositionsAvailable))
					{
						// Generate the injected code
						$cssIncludes = implode("\n\t", $cssCalls);
						$pattern = $this->_htmlPositions[$position]['pattern'];
						$replacement = str_replace('##CONT##', $cssIncludes, $this->_htmlPositions[$position]['replacement']);
						$body = preg_replace($pattern, $replacement, $body);
					}
					else
					{
						$doc = JFactory::getDocument();
						foreach ($cssCalls as $cssUrl)
						{
							$doc->addStyleSheet($cssUrl);
						}
					}
				}
			}
			JResponse::setBody($body);
			return $body;
		}
	}

	/**
	 * Load / inject Javascript
	 *
	 * @return none
	 *
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 27/06/2012
	 *
	 */
	private function _loadJS()
	{
		if (!empty($this->_jsCalls))
		{
			$body = JResponse::getBody();
			foreach ($this->_jsCalls as $position => $jsCalls)
			{
				if (!empty($jsCalls))
				{
					// If position is defined we append code (inject) to the desired position
					if (in_array($position, $this->_htmlPositionsAvailable))
					{
						// Generate the injected code
						$jsIncludes  = implode("\n\t", $jsCalls);
						$pattern     = $this->_htmlPositions[$position]['pattern'];
						$replacement = str_replace('##CONT##', $jsIncludes, $this->_htmlPositions[$position]['replacement']);
						$body        = preg_replace($pattern, $replacement, $body);
					}
					else
					{
						$doc = JFactory::getDocument();
						foreach ($jsCalls as $jsUrl)
						{
							$doc->addScript($jsUrl);
						}
					}
				}
			}
			JResponse::setBody($body);
			return $body;
		}
	}

	/**
	* Add a css file declaration
	*
	* @param   string  $cssUrl    url of the CSS file
	* @param   string  $position  position where we are going to load JS
	*
	* @return none
	*
	* @author Roberto Segura - Digital Disseny, S.L.
	* @version 23/04/2012
	*/
	private function _addCssCall($cssUrl, $position = null)
	{

		// Check for CSS overrides
		$overrideUrl = str_replace($this->_urlCss, $this->_urlCssOverrides, $cssUrl);
		if ($this->checkUrl($overrideUrl, true))
		{
			$cssUrl = $overrideUrl;
		}

		// If position is not available we will try to load the url through $doc->addScript
		if (is_null($position) || !in_array($position, $this->_htmlPositionsAvailable))
		{
			$position = 'addstylesheet';
			$cssCall = $cssUrl;
		}
		else
		{
			$cssCall = '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '" >';
		}

		// Initialize position
		if (!isset($this->_cssCalls[$position]))
		{
			$this->_cssCalls[$position] = array();
		}

		// Insert CSS call
		$this->_cssCalls[$position][] = $cssCall;

	}

	/**
	 * Add a JS script declaration
	 *
	 * @param   string  $jsUrl     url of the JS file or script content for type != url
	 * @param   string  $position  position where we are going to load JS
	 * @param   string  $type      url || script
	 *
	 * @return  none
	 *
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 27/06/2012
	 *
	 */
	private function _addJsCall($jsUrl, $position = null, $type = 'url')
	{

		// Check for overrides
		if ($type == 'url')
		{
			$overrideUrl = str_replace($this->_urlJs, $this->_urlJsOverrides, $jsUrl);
			if ($this->checkUrl($overrideUrl, true))
			{
				$jsUrl = $overrideUrl;
			}
		}

		// If position is not available we will try to load the url through $doc->addScript
		if (is_null($position) || !in_array($position, $this->_htmlPositionsAvailable))
		{
			$position = 'addscript';
			$jsCall = $jsUrl;
		}
		else
		{
			if ($type == 'url')
			{
				$jsCall = '<script src="' . $jsUrl . '" type="text/javascript"></script>';
			}
			else
			{
				$jsCall = '<script type="text/javascript">' . $jsUrl . '</script>';
			}
		}

		// Initialize position
		if (!isset($this->_jsCalls[$position]))
		{
			$this->_jsCalls[$position] = array();
		}

		// Insert JS call
		$this->_jsCalls[$position][] = $jsCall;
	}

	/**
	 * Check if a folder/file path exists
	 *
	 * @param   string   $path  Path to check
	 * @param   boolean  $file  Is a file check?
	 *
	 * @return boolean
	 *
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 27/06/2012
	 */
	public function checkPath($path, $file = false)
	{
		if ($file && JFile::exists($path))
		{
			return true;
		}
		elseif (JFolder::exists($path))
		{
			return true;
		}
		return false;
	}

	/**
	 * Check if a url is valid
	 *
	 * @param   string   $url   url to check
	 * @param   boolean  $file  is a file check
	 *
	 * @return boolean
	 *
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 27/06/2012
	 */
	public function checkUrl($url, $file = false)
	{
		$subpath = str_replace(JURI::root(true), '', $url);
		if (empty($subpath))
		{
			return true;
		}
		else
		{
			$subpath = str_replace('/', DIRECTORY_SEPARATOR, $subpath);
			$calculatedPath = JPATH_ROOT . DIRECTORY_SEPARATOR . $subpath;
			if ($file && JFile::exists($calculatedPath))
			{
				return true;
			}
			elseif (JFolder::exists($calculatedPath))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the list of active bootstrap JS files
	 *
	 * @return   array  Active Bootstrap JS files to load
	 */
	function getBootstrapActiveJsFiles()
	{
		$files = array();
		if (!empty($this->_bootstrapJsFiles))
		{
			foreach ($this->_bootstrapJsFiles as $paramName => $jsFile)
			{
				if ($this->_params->get($paramName, 0))
				{
					$files[] = $jsFile;
				}
			}
		}
		return $files;
	}

	/**
	 * Get the list of active bootstrap CSS files
	 *
	 * @return   array  Active Bootstrap CSS files to load
	 */
	function getBootstrapActiveCssFiles()
	{
		$files = array();
		if (!empty($this->_bootstrapCssFiles))
		{
			foreach ($this->_bootstrapCssFiles as $paramName => $cssFile)
			{
				if ($this->_params->get($paramName, 0) || $paramName == 'cssVariables' || $paramName == 'cssMixins' )
				{
					$files[] = $cssFile;
				}
			}
		}
		return $files;
	}

	/**
	 * Get the name of the active Template
	 *
	 * @return string template name
	 *
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 27/06/2012
	 */
	public function getCurrentTplName()
	{

		// Required objects
		$app    = JFactory::getApplication();
		$jinput = $app->input;
		$db     = JFactory::getDBO();

		// Default values
		$menuParams = new JRegistry;
		$client_id  = $app->isSite() ? 0 : 1;
		$itemId     = $jinput->get('Itemid', 0);
		$tplName    = null;

		// Try to load custom template if assigned
		if ($itemId)
		{
			$sql = " SELECT ts.template " .
					" FROM #__menu as m " .
					" INNER JOIN #__template_styles as ts" .
					" ON ts.id = m.template_style_id " .
					" WHERE m.id=" . (int) $itemId . " " .
					"";
			$db->setQuery($sql);
			$tplName = $db->loadResult();
		}

		// If no itemId or no custom template assigned load default template
		if (!$itemId || empty($tplName))
		{
			$tplName = $this->getDefaultTplName($client_id);
		}

		return $tplName;
	}

	/**
	 * Get the default template name
	 *
	 * @param   integer  $client_id  0->site | 1->admin
	 *
	 * @return string
	 *
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 27/06/2012
	 */
	public function getDefaultTplName($client_id = 0)
	{
		$result = null;
		$db = JFactory::getDBO();
		$query = " SELECT template FROM #__template_styles " .
				" WHERE client_id=" . (int) $client_id . " " .
				" AND home = 1 ";
		$db->setQuery($query);
		try
		{
			$result = $db->loadResult();
		}
		catch (JDatabaseException $e)
		{
			return $e;
		}

		return $result;
	}
}
