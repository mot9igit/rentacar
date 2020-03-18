<?php

class rentacar
{
	public $version = '1.0.0-beta';
	/** @var modX $modx */
	public $modx;
	/** @var pdoFetch $pdoTools */
	public $pdoTools;

	/** @var array $initialized */
	public $initialized = array();

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/rentacar/';
        $assetsUrl = MODX_ASSETS_URL . 'components/rentacar/';

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage('rentacar', $this->config['modelPath']);
        //$this->modx->lexicon->load('rentacar:default');

		if ($this->pdoTools = $this->modx->getService('pdoFetch')) {
			$this->pdoTools->setConfig($this->config);
		}
    }

	/**
	 * Initializes component into different contexts.
	 *
	 * @param string $ctx The context to load. Defaults to web.
	 * @param array $scriptProperties Properties for initialization.
	 *
	 * @return bool
	 */
	public function initialize($ctx = 'web', $scriptProperties = array())
	{
		if (isset($this->initialized[$ctx])) {
			return $this->initialized[$ctx];
		}
		$this->config = array_merge($this->config, $scriptProperties);
		$this->config['ctx'] = $ctx;
		$this->modx->lexicon->load('rentacar:default');

		if ($ctx != 'mgr' && (!defined('MODX_API_MODE') || !MODX_API_MODE)) {
			$config = $this->pdoTools->makePlaceholders($this->config);

			// Register CSS
			$css = trim($this->modx->getOption('rentacar_frontend_css'));
			if (!empty($css) && preg_match('/\.css/i', $css)) {
				if (preg_match('/\.css$/i', $css)) {
					$css .= '?v=' . substr(md5($this->version), 0, 10);
				}
				$this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
			}

			// Register JS
			$js = trim($this->modx->getOption('rentacar_frontend_js'));
			if (!empty($js) && preg_match('/\.js/i', $js)) {
				if (preg_match('/\.js$/i', $js)) {
					$js .= '?v=' . substr(md5($this->version), 0, 10);
				}
				$this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));

				$data = json_encode(array(
					'cssUrl' => $this->config['cssUrl'] . 'web/',
					'jsUrl' => $this->config['jsUrl'] . 'web/',
					'actionUrl' => $this->config['actionUrl'],
					'ctx' => $ctx
				), true);
				$this->modx->regClientStartupScript(
					'<script type="text/javascript">rentacarConfig = ' . $data . ';</script>', true
				);
			}
		}
		$this->initialized[$ctx] = $load;
		return $load;
	}

	/**
	 * Handle frontend requests with actions
	 *
	 * @param $action
	 * @param array $data
	 *
	 * @return array|bool|string
	 */
	public function handleRequest($action, $data = array())
	{
		$ctx = !empty($data['ctx'])
			? (string)$data['ctx']
			: 'web';
		if ($ctx != 'web') {
			$this->modx->switchContext($ctx);
		}
		$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
		$this->initialize($ctx, array('json_response' => $isAjax));

		switch ($action) {
			case 'cart/add':
				//$response = $this->cart->add(@$data['id'], @$data['count'], @$data['options']);
				break;
			default:
				$message = ($data['rentacar_action'] != $action)
					? 'rentacar_err_register_globals'
					: 'rentacar_err_unknown';
				$response = $this->error($message);
		}

		return $response;
	}

}