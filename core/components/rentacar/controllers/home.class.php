<?php

/**
 * The home manager controller for rentacar.
 *
 */
class rentacarHomeManagerController extends modExtraManagerController
{
    /** @var rentacar $rentacar */
    public $rentacar;


    /**
     *
     */
    public function initialize()
    {
        $this->rentacar = $this->modx->getService('rentacar', 'rentacar', MODX_CORE_PATH . 'components/rentacar/model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['rentacar:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('rentacar');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->rentacar->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->rentacar->config['jsUrl'] . 'mgr/rentacar.js');
        $this->addJavascript($this->rentacar->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->rentacar->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->rentacar->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->rentacar->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->rentacar->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->rentacar->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        rentacar.config = ' . json_encode($this->rentacar->config) . ';
        rentacar.config.connector_url = "' . $this->rentacar->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "rentacar-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="rentacar-panel-home-div"></div>';

        return '';
    }
}