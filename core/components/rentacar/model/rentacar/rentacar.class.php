<?php

class rentacar
{
	public $version = '1.0.0-beta';
	/** @var modX $modx */
	public $modx;
	/** @var pdoFetch $pdoTools */
	public $pdoTools;
	public $config;

	/** @var array $initialized */
	public $initialized = array();

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = $this->modx->getOption('rentacar_core_path', $config, $this->modx->getOption('core_path') . 'components/rentacar/');
        $assetsUrl = $this->modx->getOption('rentacar_assets_url', $config, $this->modx->getOption('assets_url') . 'components/rentacar/');
		$assetsPath = $this->modx->getOption('rentacar_assets_path', $config, $this->modx->getOption('base_path') . 'assets/components/rentacar/');

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
			'assetsPath' => $assetsPath,
			'actionUrl' => $assetsUrl . 'action.php',
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',

			'formSelector' => '.search_form'
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
		$this->modx->lexicon->load('babel:translate');

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
					'formSelector' => $this->config['formSelector'],
					'formRegionsSelector' => $this->config['formSelector'].' select.regions',
					'formPlacesSelector' => 'select.placepickup',
					'cultureKey' => $this->modx->getOption("cultureKey"),
					'carcontainerUrl' => $this->modx->makeUrl($this->modx->getOption("carscontainer")),
					'ctx' => $ctx
				), true);
				$this->modx->regClientStartupScript(
					'<script type="text/javascript">rentacarConfig = ' . $data . ';</script>', true
				);
			}
		}
		$this->initialized[$ctx] = true;
		return $this->initialized[$ctx];
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
			case 'form/submit':
				$response = $this->formSubmit($_REQUEST);
				break;
			case 'form/getregions':
				$response = $this->getRegions($_REQUEST);
				break;
			default:
				$message = ($data['rentacar_action'] != $action)
					? 'rentacar_err_register_globals'
					: 'rentacar_err_unknown';
				$response = $this->error($message);
		}

		return $response;
	}

	public function getSeasons($year_from, $year_to){
		$seasons = array();
		$out = array();
		// берем сезоны
		$lowseason = $this->modx->getOption("rentacar_diaplowseason");
		$middleseason = $this->modx->getOption("rentacar_diapmiddleseason");
		$bigseason = $this->modx->getOption("rentacar_diapbigseason");

		$seasons["low"] = explode(";", $lowseason);
		$seasons["middle"] = explode(";", $middleseason);
		$seasons["big"] = explode(";", $bigseason);

		foreach($seasons as $key =>$season){
			foreach($season as $range){
				$rangetmp = explode("-",$range);
				$ranger = array();
				foreach($rangetmp as $keys => $atom){
					$atomtmp = explode("/",$atom);
					if($keys==0){
						$ranger["dayfrom"] = $atomtmp[0];
						$ranger["monthfrom"] = $atomtmp[1];
					}
					if($keys==1){
						$ranger["dayto"] = $atomtmp[0];
						$ranger["monthto"] = $atomtmp[1];
					}
				}
				for($i = $year_from; $i <= $year_to; $i++){
					$tmper = array();
					$tmper["from"] = strtotime($ranger["dayfrom"]."-".$ranger["monthfrom"]."-".$i);
					$tmper["to"] = strtotime($ranger["dayto"]."-".$ranger["monthto"]."-".$i);
					$out[$key][] = $tmper;
				}
			}
		}

		return $out;
	}

	/**
	 * Заполняем основные параметры с которыми будем работать
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function formSubmit($data)
	{
		$out['datefrom'] = strtotime(str_replace(".", "-", $data["datepickup"]).' '.$data["timepickup"]);
		$out['dateto'] = strtotime(str_replace(".", "-", $data["datedropoff"]).' '.$data["timedropoff"]);

		$out['year_from'] = date("Y", $out['datefrom']);
		$out['year_to'] = date("Y", $out['dateto']);

		$_SESSION["cardata"]['datefrom'] = $out['datefrom'];
		$_SESSION["cardata"]['dateto'] = $out['dateto'];
		$_SESSION["cardata"]['region'] = $data["region"];


		$seasons = $this->getSeasons($out['year_from'], $out['year_to']);
		$tosave = array();

		foreach($seasons as $key => $season){
			foreach($season as $range){
				if($range["from"] <= $out['dateto'] AND $range["to"] >= $out['datefrom']) {
					$tmp = $range;
					$tmp['diap'] = MAX(-MAX($range["from"], $out['datefrom']) + MIN($range["to"], $out['dateto']), 0);
					$tmp['diap_days'] = floor($tmp['diap']/(60*60*24));
					$tmp['diap_hours'] = ($tmp['diap']%(60*60*24))/(60*60);
					$out[$key]["ranger"][] = $tmp;
					$tosave[$key]['days'] += $tmp['diap_days'];
					if($tmp['diap_hours'] > 5){
						$tosave[$key]['days']++;
					}
				}
			}
		}

		$tosave['all'] = ($out['dateto'] - $out['datefrom']) /(60*60*24);
		$_SESSION["cardata"]["regionpickup"] = $data["region"];
		$_SESSION["cardata"]["placepickup"] = $data["placepickup"];
		$_SESSION["cardata"]["low"] = $tosave["low"]['days'];
		$_SESSION["cardata"]["middle"] = $tosave["middle"]['days'];
		$_SESSION["cardata"]["big"] = $tosave["big"]['days'];
		$_SESSION["cardata"]["all"] = $tosave['all'];
		$tosave['success'] = true;

		return $tosave;
	}

	/**
	 * Берем регионы
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function getRegions($data)
	{
		$output = '<option value="-">'.$this->modx->lexicon('babel_site_translate_auto_form_choose').'</option>';
		if($data["region"] > 0){
			$criteria = array(
				"parents" => $data["region"],
				"depth" => '0',
				"limit" => '0',
				"tpl" => '@FILE chunks/tpl_option.tpl',
				"sortby" => 'menuindex',
				"sortdir" => 'ASC',
				"key" => 'placepickup'
			);
			$output = $this->modx->runSnippet("pdoResources",$criteria);
		}
		return $output;
	}

}