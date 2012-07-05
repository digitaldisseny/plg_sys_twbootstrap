<?php
/*
* ------------------------------------------------------------------------
* Twitter Bootstrap plugin for Joomla
* ------------------------------------------------------------------------
* Copyright (C) 2012 Digital Disseny, S.L. All Rights Reserved.
* @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
* Author: Roberto Segura - Digital Disseny, S.L.
* Website: http://www.digitaldisseny.com
* ------------------------------------------------------------------------
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class plgSystemTwbootstrap extends JPlugin
{
    private $_params = null;

    // plugin info constants
    const TYPE = 'system';
    const NAME = 'twbootstrap';

    // paths
    private $_pathPlugin = null;
    private $_pathTemplate = null;
    private $_pathOverrides = null;

    // urls
    private $_urlPlugin = null;
    private $_urlJs = null;
    private $_urlCss = null;
    private $_urlOverrides = null;
    private $_urlJsOverrides = null;
    private $_urlCssOverrides = null;

    // css & js scripts calls
    private $_cssCalls = array();
    private $_jsCalls = array();

    // html positions & associated regular expressions
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

    function __construct( &$subject ){

        parent::__construct( $subject );

        // set the HTML available positions
        $this->_htmlPositionsAvailable = array_keys($this->_htmlPositions);

        // Load plugin parameters
        $this->_plugin = JPluginHelper::getPlugin( self::TYPE, self::NAME );
        $this->_params = new JRegistry( $this->_plugin->params );

        // init folder structure
        $this->_initFolders();

        // load plugin language
        $this->loadLanguage ('plg_' . self::TYPE . '_' . self::NAME, JPATH_ADMINISTRATOR);

    }

    function onAfterInitialise()
    {

        // plugin parameters
        $comColumns = $this->_params->get('comColumns',12);
        $bootstrapMode = $this->_params->get('bootstrapMode','fluid');

        // generate row and column classes
        switch ($bootstrapMode) {
            case 'fluid':
                $bootstrapContainerClass = 'container-fluid';
                $bootstrapRowClass = 'row-fluid';
                break;
            default:
                $bootstrapContainerClass = 'container';
                $bootstrapRowClass = 'row';
                break;
        }

        // define constants | check if defined to allow override
        if (!defined('BOOTSTRAP_VERSION')) {
            define('BOOTSTRAP_VERSION','2.0.4');
        }
        if (!defined('BOOTSTRAP_COM_COLUMNS')) {
            define('BOOTSTRAP_COM_COLUMNS',$comColumns);
        }
        if (!defined('BOOTSTRAP_CONTAINER_CLASS')) {
            define('BOOTSTRAP_CONTAINER_CLASS',$bootstrapContainerClass);
        }
        if (!defined('BOOTSTRAP_ROW_CLASS')) {
            define('BOOTSTRAP_ROW_CLASS',$bootstrapRowClass);
        }

    }

    function onAfterRender(){

        // required objects
        $app =& JFactory::getApplication();
        $doc = JFactory::getDocument();

        // url params
        $jinput = $app->input;
        $tmpl = $jinput->get('tmpl',null,'cmd');

        // plugin parameters
        $loadFrontBack = $this->_params->get('loadFrontBack','frontend');
        $onlyHTML = $this->_params->get('onlyHTML',1);
        $disableModal = $this->_params->get('disableModal',1);
        $loadJquery = $this->_params->get('loadJquery', 0);
        $loadBootstrap = $this->_params->get('loadBootstrap',0);
        $injectPosition = $this->_params->get('injectPosition','headtop');

        // check modals
        $disabledTmpls = array('component', 'raw');
        if ($disableModal && in_array($tmpl, $disabledTmpls)) {
            return true;
        }

        // check HTML only
        if ($onlyHTML && $doc->getType() != 'html') {
            return true;
        }

        // site modifications
        if ( ($app->isSite() && ($loadFrontBack == 'frontend' || $loadFrontBack == 'both'))
             || ($app->isAdmin() && ($loadFrontBack == 'backend' || $loadFrontBack == 'both')) )
        {

            // load jQuery ? jQuery is added to header to avoid non-ready errors
            if ($loadJquery)
            {
                switch ($loadJquery) {
                    // load jQuery locally
                    case 1:
                        $jquery = $this->_urlJs  . '/jquery.min.js';
                        break;
                    // load jQuery from Google
                    default:
                        $jquery = 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js';
                    break;
                }

                // add script to header
                $this->_addJsCall($jquery, $injectPosition);
                $this->_addJsCall('jQuery.noConflict();',$injectPosition,'script');
            }

            // load Bootstrap ?
            if ($loadBootstrap) {

                // Bootstrap CSS - loaded in header
                $bootstrapCss = $this->_urlCss . '/bootstrap.min.css';
                $this->_addCssCall($bootstrapCss, $injectPosition);

                // Bootstrap responsive CSS
                $bootstrapResponsiveCss = $this->_urlCss . '/bootstrap-responsive.min.css';
                $this->_addCssCall($bootstrapResponsiveCss, $injectPosition);

                // bootstrap JS - loaded before body ending
                $bootstrapJs = $this->_urlJs . '/bootstrap.min.js';
                $this->_addJsCall($bootstrapJs, 'bodybottom');
            }

        }

        // JS load
        if (!empty($this->_jsCalls)) {
            $this->_loadJS();
        }

        // CSS load
        if (!empty($this->_cssCalls)) {
            $this->_loadCSS();
        }

        return true;
    }

    private function _initFolders() {

        // active template
        $currentTemplate = $this->getCurrentTplName();

        // paths
        $this->_pathPlugin = JPATH_PLUGINS . DIRECTORY_SEPARATOR . self::TYPE . DIRECTORY_SEPARATOR . self::NAME;
        $this->_pathTemplate = JPATH_THEMES
                                . DIRECTORY_SEPARATOR . $currentTemplate;
        $this->_pathOverrides = $this->_pathTemplate
                                    . DIRECTORY_SEPARATOR . 'html'
                                    . DIRECTORY_SEPARATOR . 'plg_' . self::TYPE . '_' . self::NAME;

        // urls
        $this->_urlPlugin = JURI::root(true)."plugins/" . self::TYPE . "/" . self::NAME;
        $this->_urlJs = $this->_urlPlugin . "/js";
        $this->_urlCss = $this->_urlPlugin . "/css";
        $this->_urlOverrides =  JURI::root(true). 'templates/'
                                    . $currentTemplate
                                    . '/html/plg_' . self::TYPE . '_' . self::NAME;
        $this->_urlCssOverrides = $this->_urlOverrides . '/css';
        $this->_urlJsOverrides = $this->_urlOverrides . '/js';
    }

    /**
     * Load / inject CSS
     * @author Roberto Segura - Digital Disseny, S.L.
     * @version 27/06/2012
     *
     */
    private function _loadCSS() {
        if (!empty($this->_cssCalls)) {
            $body = JResponse::getBody();
            foreach ($this->_cssCalls as $position => $cssCalls) {
                if (!empty($cssCalls)) {
                    // if position is defined we append code (inject) to the desired position
                    if(in_array($position, $this->_htmlPositionsAvailable)) {
                        // generate the injected code
                        $cssIncludes = implode("\n\t", $cssCalls);
                        $pattern = $this->_htmlPositions[$position]['pattern'];
                        $replacement = str_replace('##CONT##', $cssIncludes, $this->_htmlPositions[$position]['replacement']);
                        $body = preg_replace($pattern, $replacement, $body);
                        //die('<h1>CSS:</h1>' . $body .'<h1>Fin</h1>');
                    // non-defined positions will be threated as css url to load with $doc->addStylesheet
                    } else  {
                        $doc = JFactory::getDocument();
                        foreach ($cssCalls as $cssUrl) {
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
     * @author Roberto Segura - Digital Disseny, S.L.
     * @version 27/06/2012
     *
     */
    private function _loadJS() {
        if (!empty($this->_jsCalls)) {
            $body = JResponse::getBody();
            foreach ($this->_jsCalls as $position => $jsCalls) {
                if (!empty($jsCalls)) {
                    // if position is defined we append code (inject) to the desired position
                    if(in_array($position, $this->_htmlPositionsAvailable)) {
                        // generate the injected code
                        $jsIncludes = implode("\n\t", $jsCalls);
                        $pattern = $this->_htmlPositions[$position]['pattern'];
                        $replacement = str_replace('##CONT##', $jsIncludes, $this->_htmlPositions[$position]['replacement']);
                        $body = preg_replace($pattern, $replacement, $body);
                        //$body = str_replace ($pattern, "\n\t" . $jsIncludes, $body);
                    // non-defined positions will be threated as js url to load with $doc->addScript
                    } else  {
                        $doc = JFactory::getDocument();
                        foreach ($jsCalls as $jsUrl) {
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
	* @author Roberto Segura - Digital Disseny, S.L.
	* @version 23/04/2012
	*
	* @param string $cssUrl - url of the CSS file
    * @param string $position - position where we are going to load JS
	*/
	private function _addCssCall($cssUrl, $position = null) {

	    // check for CSS overrides
	    $overrideUrl = str_replace($this->_urlCss, $this->_urlCssOverrides, $cssUrl);
	    if ($this->checkUrl($overrideUrl, true)) {
	        $cssUrl = $overrideUrl;
	    }

	    // if position is not available we will try to load the url through $doc->addScript
	    if (is_null($position) || !in_array($position,$this->_htmlPositionsAvailable)) {
	        $position = 'addstylesheet';
	        $cssCall = $cssUrl;
	    } else {
	        $cssCall = '<link rel="stylesheet" type="text/css" href="'.$cssUrl.'" >';
	    }

	    // initialize position
	    if (!isset($this->_cssCalls[$position])) {
	        $this->_cssCalls[$position] = array();
	    }

	    // insert CSS call
	    $this->_cssCalls[$position][] = $cssCall;

	}

	/**
	 * Add a JS script declaration
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 21/05/2012
	 *
	 * @param string $jsUrl - url of the JS file
	 */

	/**
	 * Add a JS script declaration
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 27/06/2012
	 *
	 * @param string $jsUrl - url of the JS file or script content for type != url
	 * @param string $position - position where we are going to load JS
	 * @param string $type - url || script
	 */
	private function _addJsCall($jsUrl, $position = null, $type = 'url') {

	    // check for overrides
	    if ($type == 'url') {
    	    $overrideUrl = str_replace($this->_urlJs, $this->_urlJsOverrides, $jsUrl);
    	    if ($this->checkUrl($overrideUrl, true)) {
    	        $jsUrl = $overrideUrl;
    	    }
	    }

	    // if position is not available we will try to load the url through $doc->addScript
	    if (is_null($position) || !in_array($position,$this->_htmlPositionsAvailable)) {
            $position = 'addscript';
            $jsCall = $jsUrl;
	    } else {
	        if ($type == 'url') {
	            $jsCall = '<script src="'.$jsUrl.'" type="text/javascript"></script>';
	        } else {
	            $jsCall = '<script type="text/javascript">'.$jsUrl.'</script>';
	        }
	    }

	    // initialize position
	    if (!isset($this->_jsCalls[$position])) {
	        $this->_jsCalls[$position] = array();
	    }

	    // insert JS call
	    $this->_jsCalls[$position][] = $jsCall;
	}

	public function checkPath($path, $file = false) {
	    if ($file && JFile::exists($path)) {
	        return true;
	    } elseif (JFolder::exists($path)) {
	        return true;
	    }
	    return false;
	}

	public function checkUrl($url, $file = false) {
	    $subpath = str_replace(JURI::root(true), '', $url);
	    if (empty($subpath)) {
	        return true;
	    } else {
	        $subpath = str_replace('/', DIRECTORY_SEPARATOR, $subpath);
	        $calculatedPath = JPATH_ROOT . DIRECTORY_SEPARATOR . $subpath;
	        if ($file && JFile::exists($calculatedPath)) {
	            return true;
	        } elseif (JFolder::exists($calculatedPath)) {
	            return true;
	        }
	    }
	    return false;
	}

	public function getCurrentTplName() {

	    // required objects
	    $app =& JFactory::getApplication();
	    $jinput = $app->input;
	    $db = JFactory::getDBO();

	    // default values
	    $menuParams = new JRegistry();
	    $client_id = $app->isSite() ? 0 : 1;
	    $itemId = $jinput->get('Itemid',0);
	    $tplName = null;

	    // try to load custom template if assigned
	    if ($itemId) {
	        $sql = " SELECT ts.template " .
	                " FROM #__menu as m " .
	                " INNER JOIN #__template_styles as ts" .
	                " ON ts.id = m.template_style_id " .
	                " WHERE m.id=".(int)$itemId." " .
	                "";
	        $db->setQuery($sql);
	        $tplName = $db->loadResult();
	    }
	    // if no itemId or no custom template assigned load default template
	    if( !$itemId || empty($tplName)) {
	        $tplName = $this->getDefaultTplName($client_id);
	    }

	    return $tplName;
	}

	public function getDefaultTplName($client_id = 0) {
	    $result = null;
	    $db = JFactory::getDBO();
	    $query =  " SELECT template FROM #__template_styles "
	    ." WHERE client_id=".(int)$client_id." "
	    ." AND home = 1 ";
	    $db->setQuery($query);
	    try {
	        $result = $db->loadResult();
	    } catch (JDatabaseException $e) {
	        return $e;
	    }

	    return $result;
	}
}