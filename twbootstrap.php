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

class plgSystemTwbootstrap extends JPlugin
{
    private $_params = null;

    // plugin info constants
    const TYPE = 'system';
    const NAME = 'twbootstrap';

    // paths
    private $_pathPlugin = null;

    // urls
    private $_urlPlugin = null;
    private $_urlJs = null;
    private $_urlCss = null;

    // css & js scripts calls
    private $_cssCalls = array();
    private $_jsCalls = array();

    function __construct( &$subject ){

        parent::__construct( $subject );

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

    function onBeforeRender(){

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
                        $jquery = $this->_urlJs  . '/jquery-1.7.2.min.js';
                        break;
                    // load jQuery from Google
                    default:
                        $jquery = 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js';
                    break;
                }

                // add script to header
                $doc->addScript($jquery);
                $doc->addScriptDeclaration('jQuery.noConflict();');
            }

            // load Bootstrap ?
            if ($loadBootstrap) {

                // Bootstrap CSS - loaded in header
                $bootstrapCss = $this->_urlCss . '/bootstrap.min.css';
                $doc->addStyleSheet($bootstrapCss);

                // bootstrap JS - loaded before body ending
                $bootstrapJs = $this->_urlJs . '/bootstrap.min.js';
                $doc->addScript($bootstrapJs);
                //$this->_addJsCall($bootstrapJs);
            }

        }

        // get the document body
        $body = JResponse::getBody();

        // JS load
        if (!empty($this->_jsCalls)) {
            $jsIncludes = implode("\n\t", $this->_jsCalls);
            $body = str_replace ("</body>", "\n\t" . $jsIncludes . "\n</body>", $body);
        }

        // CSS load
        if (!empty($this->_cssCalls)) {
            $cssHtml = implode("\n\t", $this->_cssCalls);
            // css loads just after closing the head tag
            $body = str_replace ("</head>", $cssHtml . "\n</head>", $body);
        }

        // set the modified body
        JResponse::setBody($body);

        return true;
    }

    private function _initFolders() {

        // paths
        $this->_pathPlugin = JPATH_PLUGINS . DIRECTORY_SEPARATOR . self::TYPE . DIRECTORY_SEPARATOR . self::NAME;

        // urls
        $this->_urlPlugin = JURI::root()."plugins/" . self::TYPE . "/" . self::NAME;
        $this->_urlJs = $this->_urlPlugin . "/js";
        $this->_urlCss = $this->_urlPlugin . "/css";
    }

	/**
	* Add a css file declaration
	* @author Roberto Segura - Digital Disseny, S.L.
	* @version 23/04/2012
	*
	* @param string $cssUrl - url of css file
	*/
	private function _addCssCall($cssUrl) {
	    $cssCall = '<link rel="stylesheet" type="text/css" href="'.$cssUrl.'" >';
	    $this->_cssCalls[] = $cssCall;
	}

	/**
	 * Add a JS script declaration
	 * @author Roberto Segura - Digital Disseny, S.L.
	 * @version 21/05/2012
	 *
	 * @param string $jsUrl - url of the JS file
	 */
	private function _addJsCall($jsUrl) {
	    $jsCall = '<script src="'.$jsUrl.'" type="text/javascript"></script>';
	    $this->_jsCalls[] = $jsCall;
	}
}