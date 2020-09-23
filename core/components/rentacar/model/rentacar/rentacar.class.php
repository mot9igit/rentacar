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
		$assets_path = $this->modx->getOption('rentacar_assets_path',null,$this->modx->getOption('base_path').'assets/components/rentacar/');
		require_once $assets_path.'libs/PHPExcel/PHPExcel.php';

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
		//$this->modx->log(1, $ctx);
		$this->config = array_merge($this->config, $scriptProperties);
		$this->config['ctx'] = $ctx;
		$this->modx->getService('lexicon','modLexicon');
		$lang = $this->modx->getOption('cultureKey');
		$this->modx->lexicon->load($lang.':rentacar:default');
		$this->modx->lexicon->load($lang.':babel:translate');

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
					'formRegionsSelector' => 'select.regions',
					'formPlacesSelector' => 'select.placepickup',
					'formOfferSelector' => "form.offer",
					'formFormSelector' => '.rentacar_form',
					'priceDataSelector' => ".rentacar-price-data",
					'cultureKey' => $this->modx->getOption("cultureKey"),
					'carcontainerUrl' => $this->modx->makeUrl($this->modx->getOption("carscontainer")),
					'ctx' => $ctx
				), true);
				$this->modx->regClientStartupScript(
					'<script type="text/javascript">rentacarConfig = ' . $data . ';</script>', true
				);
			}
		}
		// Добавляем опции по умолчанию
		$criteria = array(
			"checked" => 1,
			"active" => 1
		);
		$options = $this->modx->getCollection("rentacar_cars_options", $criteria);
		foreach($options as $option){
			$option_data = array(
				"id" => $option->id
			);
			$_SESSION["cardata"]["options"][$option->id] = $option_data;
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
		$this->modx->log(1, $action);
		switch ($action) {
			case 'form/submit':
				$response = $this->formSubmit($_REQUEST);
				break;
			case 'formoffer/submit':
				$response = $this->madeOffer($_REQUEST);
				break;
			case 'adminoffer/submit':
				$data['datefrom'] = strtotime(str_replace(".", "-", $data["datepickup"]).' '.$data["timepickup"]);
				$data['dateto'] = strtotime(str_replace(".", "-", $data["datedropoff"]).' '.$data["timedropoff"]);
				$_SESSION["cardata"] = $this->getTiming($data);

				$_SESSION["cars"][$_REQUEST["resource"]] = $this->calculate($_REQUEST["resource"], $_SESSION["cardata"]);
				$this->modx->log(1, print_r($_SESSION["cardata"], 1));
				$this->modx->log(1, print_r($_REQUEST, 1));
				$this->modx->log(1, print_r($_SESSION["cars"], 1));
				foreach($_REQUEST as $key => $item){
					if($key == "option" || $key == "warranty"){
						foreach($_REQUEST["option"] as $option){
							$data_s = array(
								"name" => "option[]",
								"value" => $option
							);
							if($_REQUEST['count_'.$option]){
								$data_s["count"] = $_REQUEST['count_'.$option];
							}
							$this->formOfferAdd($data_s);
						}
					}
					if($key == "warranty") {
						$data_s = array(
							"name" => $key,
							"value" => $item
						);
						if ($_REQUEST['count_' . $item]) {
							$data_s["count"] = $_REQUEST['count_' . $item];
						}
						$this->formOfferAdd($data_s);
					}
				}
				$response = $this->madeOffer($_REQUEST);
				break;
			case 'formoffer/edit':
				$response = $this->editOffer($_REQUEST);
				break;
			case 'formoffer/add':
				$response = $this->formOfferAdd($_REQUEST);
				break;
			case 'formoffer/remove':
				$response = $this->formOfferRemove($_REQUEST);
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

	public function getSeasons($year_from, $year_to, $timepickup = "10:00"){
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
					$tmper["from"] = strtotime($ranger["dayfrom"]."-".$ranger["monthfrom"]."-".$i." ".$timepickup);
					$tmper["to"] = strtotime($ranger["dayto"]."-".$ranger["monthto"]."-".$i." ".$timepickup);
					$tmper["from_date"] = $ranger["dayfrom"]."-".$ranger["monthfrom"]."-".$i." ".$timepickup;
					$tmper["to_date"] = $ranger["dayto"]."-".$ranger["monthto"]."-".$i." ".$timepickup;
					$out[$key][] = $tmper;
				}
			}
		}
		//$this->modx->log(1, print_r($out, 1));
		return $out;
	}

	/**
	 * Расчитываем стоимость опции
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function calcOptionPrice($id, $count = 1){
		$option = $this->modx->getObject("rentacar_cars_options", $id);
		$defprice = 0;
		if ($option){
			$opt = $option->toArray();
			if($opt["price_perday"]){
				if($count <= $opt["free_count"]){
					$defprice = 0;
				}else{
					$koe = $count - $opt["free_count"];
					$defprice = $opt["price"]*$_SESSION["cardata"]["all"]*$koe;
				}
			}else{
				if($count <= $opt["free_count"]){
					$defprice = 0;
				}else{
					$koe = $count - $opt["free_count"];
					$defprice = $opt["price"]*$koe;
				}
			}
			return $defprice;
		}else{
			return 0;
		}
	}

	/**
	* Расчитываем стоимость опции
	*
	* @return array
	*/
	public function updateOptionPrice($dprice){
		$price = 0;
		foreach($dprice["options"] as $option){
			$price += $option["price"];
		}
		return $price;
	}

	/**
	 * Расчитываем стоимость доставки
	 *
	 * @return array
	 */
	public function updateDeliveryPrice($dprice){
		$price = 0;
		$this->modx->log(1, print_r($dprice["deliverys"], 1));
		foreach($dprice["deliverys"] as $option){
			$price += $option["price"];
		}
		return $price;
	}

	/**
	 * Добавляем поля
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function formOfferAdd($data){
		if($data["name"] == "option[]"){
			$option_data = array(
				"id" => $data["value"],
				"price" => $this->calcOptionPrice($data["value"], $data["count"]),
				"count" => $data["count"]
			);
			$_SESSION["cardata"]["options"][$data["value"]] = $option_data;
			$price = $this->updateOptionPrice($_SESSION["cardata"]);
			$_SESSION["cardata"]["options_price"] = $price;
		}
		if($data["name"] == "warranty") {
			$this->modx->log(1, $data["name"]);
			$warranty_data = array(
				"id" => $data["value"]
			);
			$war = $this->modx->getObject("rentacar_cars_warranty", $warranty_data['id']);
			if($war) {
				$warranty_data['price'] = $war->get("price");
			}
			$_SESSION["cardata"]["warrantys"] = $warranty_data;
		}
		if($data["name"] == "timepickup" || $data["name"] == "timedropoff"){
			$new_data = array(
				"datepickup" => date("d.m.Y",$_SESSION["cardata"]["datefrom"]),
				"timepickup" => date("H:i",$_SESSION["cardata"]["datefrom"]),
				"datedropoff" => date("d.m.Y",$_SESSION["cardata"]["dateto"]),
				"timedropoff" => date("H:i",$_SESSION["cardata"]["dateto"]),
				'region' => $_SESSION["cardata"]["region"],
				'regionpickup' => $_SESSION["cardata"]["regionpickup"],
				'placepickup' => $_SESSION["cardata"]["placepickup"],
			);
			if($data["name"] == "timepickup"){
				$new_data["timepickup"] = $data["value"];
				$dateto = date("H:i",$_SESSION["cardata"]["dateto"]);
				if($dateto == "00:00"){
					$dateto = "10:00";
				}
				$new_data["timedropoff"] = $dateto;
			}else{
				$datefrom = date("H:i",$_SESSION["cardata"]["datefrom"]);
				if($datefrom == "00:00"){
					$datefrom = "10:00";
				}
				$new_data["timepickup"] = $datefrom;
				$new_data["timedropoff"] = $data["value"];
			}
			$work = $this->modx->getOption("rentacar_worktime");
			$worked = explode("-", $work);
			$rgTimes = array(
				new DateTime($worked[0].':00'), new DateTime($worked[1].':00')
			);
			$time_from = new DateTime($new_data["timepickup"]);
			$time_to = new DateTime($new_data["timedropoff"]);
			if($time_from < $rgTimes[0] || $time_from > $rgTimes[1]){
				$criteria = array(
					"type" => 2
				);
				$option = $this->modx->getObject("rentacar_cars_options", $criteria);
				if($option){
					$option_data = array(
						"id" => $option->id."_from",
						"price" => $this->calcOptionPrice($option->id, 1),
						"count" => 1
					);
					$_SESSION["cardata"]["deliverys"][$option->id."_from"] = $option_data;
					$price = $this->updateDeliveryPrice($_SESSION["cardata"]);
					$_SESSION["cardata"]["deliverys_price"] = $price;
				}
			}else{
				$criteria = array(
					"type" => 2
				);
				$option = $this->modx->getObject("rentacar_cars_options", $criteria);
				$this->modx->log(1, $option->id);
				if($option) {
					unset($_SESSION["cardata"]["deliverys"][$option->id . "_from"]);
				}
			}
			if($time_to < $rgTimes[0] || $time_to > $rgTimes[1]){
				$criteria = array(
					"type" => 2
				);
				$option = $this->modx->getObject("rentacar_cars_options", $criteria);
				if($option){
					$option_data = array(
						"id" => $option->id."_to",
						"price" => $this->calcOptionPrice($option->id, 1),
						"count" => 1
					);
					$_SESSION["cardata"]["deliverys"][$option->id."_to"] = $option_data;
					$price = $this->updateDeliveryPrice($_SESSION["cardata"]);
					$_SESSION["cardata"]["deliverys_price"] = $price;
				}
			}else{
				$criteria = array(
					"type" => 2
				);
				$option = $this->modx->getObject("rentacar_cars_options", $criteria);
				$this->modx->log(1, $option->id);
				if($option) {
					unset($_SESSION["cardata"]["deliverys"][$option->id . "_to"]);
				}
			}
			$this->formSubmit($new_data);
		}
		if($data["name"] == "count") {
			if (isset($_SESSION["cardata"]["options"][$data["option"]])) {
				$_SESSION["cardata"]["options"][$data["option"]]["count"] = $data["value"];
				$_SESSION["cardata"]["options"][$data["option"]]["price"] = $this->calcOptionPrice($data["option"], $data["value"]);
				$price = $this->updateOptionPrice($_SESSION["cardata"]);
				$_SESSION["cardata"]["options_price"] = $price;
			}
		}
		// DIRTY DATA
		if($data["name"] == "region"){
			$_SESSION["cardata"]["regionpickup"] = $data["value"];
			$_SESSION["cardata"]["region"] = $data["value"];
		}
		if($data["name"] == "placepickup"){
			$_SESSION["cardata"]["placepickup"] = $data["value"];
		}
		if($data["name"] == "datepickup"){
			if($_SESSION["cardata"]["timepickup"] ==  ""){
				$_SESSION["cardata"]["timepickup"] = "10:00";
			}
			$out['datefrom'] = strtotime(str_replace(".", "-", $data["value"]).' '.$_SESSION["cardata"]["timepickup"]);
			$_SESSION["cardata"]['datefrom'] = $out['datefrom'];
		}
		if($data["name"] == "datedropoff"){
			if($_SESSION["cardata"]["timedropoff"] ==  "") {
				$_SESSION["cardata"]["timedropoff"] = "10:00";
			}
			$out['dateto'] = strtotime(str_replace(".", "-", $data["value"]).' '.$_SESSION["cardata"]["timedropoff"]);
			$_SESSION["cardata"]['dateto'] = $out['dateto'];
		}
		if(isset($_SESSION["cardata"]["regionpickup"])
			&& isset($_SESSION["cardata"]["placepickup"])
			&& isset($_SESSION["cardata"]["datefrom"])
			&& isset($_SESSION["cardata"]["dateto"])
		){
			$dirty = array(
				'region' => $_SESSION["cardata"]["regionpickup"],
				'placepickup' => $_SESSION["cardata"]["placepickup"],
				'datetimepickup' => $_SESSION["cardata"]["datefrom"],
				'datetimedropoff' => $_SESSION["cardata"]["dateto"],
			);
			$this->formSubmit($dirty);
		}
		$this->modx->lexicon->load($data['cultureKey'].":rentacar:default");
		return $this->pdoTools->getChunk("@FILE chunks/tpl_price_info.tpl", array(
			"id" => $data["id"],
			"all" => $_SESSION["cardata"]["all"],
			"deliverys" => $_SESSION["cardata"]["deliverys"],
			"warrantys" => $_SESSION["cardata"]["warrantys"],
			"options" => $_SESSION["cardata"]["options"]
		));
	}

	/**
	 * Выставляем опции по умолчанию
	 *
	 * @return boolean
	 */
	public function setOptions(){
		// выставляем опции
		$criteria = array(
			"checked" => 1,
			"active" => 1
		);
		$options = $this->modx->getCollection("rentacar_cars_options", $criteria);
		foreach($options as $option){
			$option_data = array(
				"id" => $option->id,
				"price" => $this->calcOptionPrice($option->id),
				"count" => 1
			);
			$_SESSION["cardata"]["options"][$option->id] = $option_data;
			$price = $this->updateOptionPrice($_SESSION["cardata"]);
			$_SESSION["cardata"]["options_price"] = $price;
		}
		// выставляем страховку
		$criteria = array(
			"checked" => 1,
			"active" => 1
		);
		$warrantys = $this->modx->getCollection("rentacar_cars_warranty", $criteria);
		foreach($warrantys as $warranty){
			$warranty_data = array(
				"id" => $warranty->id
			);
			if($warranty_data['id'] != $this->modx->getOption("rentacar_warranty_z")){
				if($warranty){
					$warranty_data['price'] = $warranty->get("price");
				}
			}
			$_SESSION["cardata"]["warrantys"] = $warranty_data;
		}
		return true;
	}

	/**
	 * Удаляем опции
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function formOfferRemove($data){
		if($data["name"] == "option[]"){
			unset($_SESSION["cardata"]["options"][$data["value"]]);
			$price = $this->updateOptionPrice($_SESSION["cardata"]);
			$_SESSION["cardata"]["options_price"] = $price;
		}
		$this->modx->lexicon->load($data['cultureKey'].":rentacar:default");
		return $this->pdoTools->getChunk("@FILE chunks/tpl_price_info.tpl", array(
			"id" => $data["id"],
			"all" => $_SESSION["cardata"]["all"],
			"deliverys" => $_SESSION["cardata"]["deliverys"],
			"warrantys" => $_SESSION["cardata"]["warrantys"],
			"options" => $_SESSION["cardata"]["options"]
		));
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
		if($data['datetimepickup'] && $data['datetimedropoff']){
			$out['datefrom'] = $data['datetimepickup'];
			$out['dateto'] = $data['datetimedropoff'];
			$data["timepickup"] = date("H:i", $out['datefrom']);
			$data["timedropoff"] = date("H:i", $out['dateto']);
		}else{
			if($data["timepickup"] ==  ""){
				$data["timepickup"] = "10:00";
				$criteria = array(
					"type" => 2
				);
				$option = $this->modx->getObject("rentacar_cars_options", $criteria);
				if($option){
					unset($_SESSION["cardata"]["deliverys"][$option->id.'_from']);
					$price = $this->updateDeliveryPrice($_SESSION["cardata"]);
					$_SESSION["cardata"]["deliverys_price"] = $price;
				}
			}
			if($data["timedropoff"] ==  ""){
				$data["timedropoff"] = "10:00";
				$criteria = array(
					"type" => 2
				);
				$option = $this->modx->getObject("rentacar_cars_options", $criteria);
				if($option){
					unset($_SESSION["cardata"]["deliverys"][$option->id.'_to']);
					$price = $this->updateDeliveryPrice($_SESSION["cardata"]);
					$_SESSION["cardata"]["deliverys_price"] = $price;
				}
			}
			$out['datefrom'] = strtotime(str_replace(".", "-", $data["datepickup"]).' '.$data["timepickup"]);
			$out['dateto'] = strtotime(str_replace(".", "-", $data["datedropoff"]).' '.$data["timedropoff"]);
		}

		$out['year_from'] = date("Y", $out['datefrom']);
		$out['year_to'] = date("Y", $out['dateto']);

		$_SESSION["cardata"]['datefrom'] = $out['datefrom'];
		$_SESSION["cardata"]['dateto'] = $out['dateto'];
		$_SESSION["cardata"]['timepickup'] = $data["timepickup"];
		$_SESSION["cardata"]['timedropoff'] = $data["timedropoff"];
		$_SESSION["cardata"]['region'] = $data["region"];


		$seasons = $this->getSeasons($out['year_from'], $out['year_to'], $data["timepickup"]);
		$tosave = array();
		$tosave['all'] = 0;
		$add_flag = false;
		foreach($seasons as $key => $season){
			foreach($season as $range){
				if($range["from"] <= $out['dateto'] AND $range["to"] >= $out['datefrom']) {
					$tmp = $range;
					$tmp['diap'] = MAX(-MAX($range["from"], $out['datefrom']) + MIN($range["to"], $out['dateto']), 0);
					$tmp['diap_days'] = floor($tmp['diap']/(60*60*24));
					$tmp['diap_hours'] = ($tmp['diap']%(60*60*24))/(60*60);
					$out[$key]["ranger"][] = $tmp;
					$tosave[$key]['days'] += $tmp['diap_days'];
					$tosave['all'] += $tmp['diap_days'];
					// прибавляем единицу, так как дней 0
					if($tmp['diap_hours'] >= 12){
						$tosave[$key]['days'] += 1;
						$tosave['all'] += 1;
						//$add_flag = true;
					}
					$this->modx->log(1, print_r($tmp, 1));
					$tosave["last"] = $key;
				}
			}
		}

		$out['timefrom'] = strtotime(date("d-m-Y").' '.$data["timepickup"]);
		$out['timeto'] = strtotime(date("d-m-Y").' '.$data["timedropoff"]);
		$diap = $out['timeto'] - $out['timefrom'];
		// считаем часы
		$this->modx->log(1, print_r($out, 1));
		$this->modx->log(1, $diap);
		if(($diap >= 18000) && !$add_flag){
			$tosave[$tosave["last"]]['days']++;
			$tosave['all']++;
		}
		// новогодний период
		if($out['year_from'] != $out['year_to']){
			$tosave["middle"]['days']++;
			$tosave['all']++;
		}
		if($data["placepickup"]){
			$place = $this->modx->getObject("modResource", $data["placepickup"]);
			if($place){
				$delivery_cost = $place->getTVValue($this->modx->getOption("rentacar_delivery_tv"));
				$criteria = array(
					"type" => 3
				);
				$option = $this->modx->getObject("rentacar_cars_options", $criteria);
				if($delivery_cost){
					if($option){
						$option_data = array(
							"id" => $option->id,
							"price" => $delivery_cost,
							"count" => 1
						);
						$_SESSION["cardata"]["deliverys"][$option->id] = $option_data;
						$price = $this->updateDeliveryPrice($_SESSION["cardata"]);
						$_SESSION["cardata"]["deliverys_price"] = $price;
					}
				}else{
					if($option) {
						unset($_SESSION["cardata"]["deliverys"][$option->id]);
						$price = $this->updateDeliveryPrice($_SESSION["cardata"]);
						$_SESSION["cardata"]["deliverys_price"] = $price;
					}
				}
			}
		}

		$_SESSION["cardata"]["regionpickup"] = $data["region"];
		$_SESSION["cardata"]["placepickup"] = $data["placepickup"];
		$_SESSION["cardata"]["low"] = $tosave["low"]['days'];
		$_SESSION["cardata"]["middle"] = $tosave["middle"]['days'];
		$_SESSION["cardata"]["big"] = $tosave["big"]['days'];
		$_SESSION["cardata"]["all"] = floor($tosave['all']);
		$tosave['success'] = true;
		if(!count($_SESSION["cardata"]["warrantys"])){
			$this->setOptions();
		}
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
		$this->modx->lexicon->load($data['cultureKey'].":rentacar:default");
		$output = '<option value="-">'.$this->modx->lexicon('rentacar_auto_form_choose').'</option>';
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

	/**
	 * Расчет времени авто
	 *
	 *
	 */

	public function getTiming($data){
		$result['cardata']['datefrom'] = $data['datefrom'];
		$result['cardata']['dateto'] = $data['dateto'];
		$result['cardata']['timepickup'] = $data['timepickup'];
		$result['cardata']['timedropoff'] = $data['timedropoff'];
		$result['cardata']['region'] = $data['region'];
		$result['cardata']['regionpickup'] = $data['region'];
		$result['cardata']['placepickup'] = $data['placepickup'];
		$out['year_from'] = date("Y", $result['cardata']['datefrom']);
		$out['year_to'] = date("Y", $result['cardata']['dateto']);

		$seasons = $this->getSeasons($out['year_from'], $out['year_to'], $result['cardata']['timepickup']);
		$tosave = array();
		$tosave['all'] = 0;
		foreach($seasons as $key => $season){
			foreach($season as $range){
				if($range["from"] <= $data['dateto'] AND $range["to"] >= $data['datefrom']) {
					$tmp = $range;
					$tmp['diap'] = MAX(-MAX($range["from"], $data['datefrom']) + MIN($range["to"], $data['dateto']), 0);
					$tmp['diap_days'] = floor($tmp['diap']/(60*60*24));
					$tmp['diap_hours'] = ($tmp['diap']%(60*60*24))/(60*60);
					$out[$key]["ranger"][] = $tmp;
					$tosave[$key]['days'] += $tmp['diap_days'];
					$tosave['all'] += $tmp['diap_days'];
					$tosave["last"] = $key;
				}
			}
		}

		$data['timefrom'] = strtotime(date("d-m-Y").' '.$result['cardata']["timepickup"]);
		$data['timeto'] = strtotime(date("d-m-Y").' '.$result['cardata']["timedropoff"]);
		$diap = $data['timeto'] - $data['timefrom'];
		// считаем часы
		if($diap >= 18000 || $diap < 0){
			$tosave[$tosave["last"]]['days']++;
			$tosave['all']++;
		}
		// новогодний период
		if($out['year_from'] != $out['year_to']){
			$tosave["middle"]['days']++;
			$tosave['all']++;
		}
		$result['cardata']["low"] = $tosave["low"]['days'];
		$result["cardata"]["middle"] = $tosave["middle"]['days'];
		$result["cardata"]["big"] = $tosave["big"]['days'];
		$result["cardata"]["all"] = floor($tosave['all']);
		return $result["cardata"];
	}
	/**
	 * Редактируем заказ
	 *
	 * @return array
	 */
	public function editOffer($data){
		if($data["offer_id"]){
			$order = $this->modx->getObject("msOrder", $data["offer_id"]);
			/* все компануем в массив $result */
			$result = array();
			if($order){
				// simple data
				$address = $order->getOne("Address");
				$user = $address->getOne("UserProfile");
				// in order
				if($data["name"]){
					$address->set("receiver", $data["name"]);
				}
				if($data["phone"]){
					$address->set("phone", $data["phone"]);
				}
				if($data["email"]){
					$user->set("email", $data["email"]);
				}
				if($data["region"]){
					$order->set("regionpickup", $data["region"]);
				}
				if($data["placepickup"]){
					$order->set("placepickup", $data["placepickup"]);
					/* считаем сбор за доставку */
					$place = $this->modx->getObject("modResource", $data["placepickup"]);
					if($place){
						$delivery_cost = $place->getTVValue($this->modx->getOption("rentacar_delivery_tv"));
						$criteria = array(
							"type" => 3
						);
						$option = $this->modx->getObject("rentacar_cars_options", $criteria);
						if($delivery_cost){
							if($option){
								$option_data = array(
									"id" => $option->id,
									"price" => $delivery_cost,
									"count" => 1
								);
								$result['cardata']["deliverys"][$option->id] = $option_data;
								$price = $this->updateDeliveryPrice($result['cardata']);
								$result['cardata']["deliverys_price"] = $price;
							}
						}
					}
				}
				if($data["place"]){
					$order->set("place", $data["place"]);
				}
				if($data["place"]){
					$order->set("placedropoff", $data["place"]);
				}
				$data['datefrom'] = strtotime(str_replace(".", "-", $data["datepickup"]).' '.$data["timepickup"]);
				$data['dateto'] = strtotime(str_replace(".", "-", $data["datedropoff"]).' '.$data["timedropoff"]);
				$order->set('datefrom', date('Y-m-d H:i:s', $data['datefrom']));
				$order->set('dateto', date('Y-m-d H:i:s', $data['dateto']));
				$order->save();
				$user->save();
				$address->save();
				// пересчет цены авто

				$result["cardata"] = $this->getTiming($data);
				$cardata = $this->calculate($data['car'], $result['cardata']);
				$result['cars'][$data['car']] = $cardata;
				$result["cardata"]["allwprice"] = $cardata["allwprice"];
				// autos
				if($data['carchoice']) {
					$criteria = array(
						'order_id' => $order->get("id")
					);
					$object = $this->modx->getObject('rentacar_cars_avaible', $criteria);
					if ($object) {
						$object->set("car_id", $data['carchoice']);
					} else {
						$object = $this->modx->newObject("rentacar_cars_avaible");
						$object->set("order_id", $order->get("id"));
					}
					if ($order->get("status") == 1) {
						$miniShop2 = $this->modx->getService('miniShop2');
						$miniShop2->changeOrderStatus($order->get('id'), 6);
					}
					$object->set("car_id", $data['carchoice']);
					$object->set("date_on", date('Y-m-d H:i:s', $data['datefrom']));
					$object->set("date_off", date('Y-m-d H:i:s', $data['dateto']));
					$object->save();
				}
				// опции
				if($data["option"]){
					foreach($data["option"] as $option){
						if($data["count_".$option]){
							$count = $data["count_".$option];
						}else{
							$count = 1;
						}
						$option_data = array(
							"id" => $option,
							"price" => $this->calcOptionPrice($option, $count),
							"count" => $count
						);
						$result['cardata']["options"][$option] = $option_data;
						$price = $this->updateOptionPrice($result['cardata']);
						$result['cardata']["options_price"] = $price;
					}
				}
				// страховка
				if($data['warranty']){
					$warranty_data = array(
						"id" => $data['warranty']
					);
					$war = $this->modx->getObject("rentacar_cars_warranty", $warranty_data['id']);
					if($war) {
						$cost_warranty = $this->modx->getOption("rentacar_warranty_z");
						if($warranty_data['id'] == $cost_warranty){
							$warranty_data['price'] = $result['cardata']['allwprice'];
						}else{
							$warranty_data['price'] = $war->get("price");
						}
					}
					$result['cardata']["warrantys"] = $warranty_data;
				}
				// сборы за доставку в нерабочее время
				if($data["timepickup"] || $data["timedropoff"]){
					$work = $this->modx->getOption("rentacar_worktime");
					$worked = explode("-", $work);
					$rgTimes = array(
						new DateTime($worked[0].':00'), new DateTime($worked[1].':00')
					);
					$time_from = new DateTime($data["timepickup"]);
					$time_to = new DateTime($data["timedropoff"]);
					if($time_from < $rgTimes[0] || $time_from > $rgTimes[1]){
						$criteria = array(
							"type" => 2
						);
						$option = $this->modx->getObject("rentacar_cars_options", $criteria);
						if($option){
							$option_data = array(
								"id" => $option->id."_from",
								"price" => $this->calcOptionPrice($option->id, 1),
								"count" => 1
							);
							$result['cardata']["deliverys"][$option->id."_from"] = $option_data;
							$price = $this->updateDeliveryPrice($result['cardata']);
							$result['cardata']["deliverys_price"] = $price;
						}
					}
					if($time_to < $rgTimes[0] || $time_to > $rgTimes[1]){
						$criteria = array(
							"type" => 2
						);
						$option = $this->modx->getObject("rentacar_cars_options", $criteria);
						if($option){
							$option_data = array(
								"id" => $option->id."_to",
								"price" => $this->calcOptionPrice($option->id, 1),
								"count" => 1
							);
							$result['cardata']["deliverys"][$option->id."_to"] = $option_data;
							$price = $this->updateDeliveryPrice($result['cardata']);
							$result['cardata']["deliverys_price"] = $price;
						}
					}
				}

				$result['price'] = $result['cardata']["options_price"] + $result['cardata']["deliverys_price"] + $result["cars"][$data['car']]["allprice"];
				if($result['cardata']["warrantys"]["price"] > 0){
					$result['price'] += $result["cars"][$data['car']]["allwprice"];
				}
				$products = $order->getMany('Products');
				$data_t = $order->toArray();
				foreach ($products as $product) {
					if($product->get('product_id') == $data['car']){
						$product->set("options", json_encode($result));
					}
					$product->set("price", $result["price"]);
					$product->set("cost", $result["price"]);
					$product->save();
					$data_t['products'][] = $product->toArray();
				}
				$order->set("properties", json_encode($result));
				$order->set("cart_cost", $result["price"]);
				$order->set("cost", $result["price"]);
				$order->save();
				$result['order_id'] = $order->get("id");
				$result['order_cost'] = $order->get("cost");
				$output['offer'] = $data_t;

				// status
				$arr = array(
					'success' => 1,
					'data' => array(
						'success' => "Заказ успешно сохранен.",
						'data' => $result,
						'html' => $this->pdoTools->getChunk('@FILE chunks/tpl_offer_cart.tpl', $output)
					)
				);
				return json_encode($arr);
			}
		}
	}

	/**
	 * Калькулятор авто
	 *
	 * @return array
	 */
	public function calculate($id, $data)
	{
		$car = $this->modx->getObject("msProduct", $id);
		$this->modx->log(1, print_r($data, 1).' '.$car->get("id"));
		if ($car) {
			$file = '';
			$type = $car->getTVValue("vnutrclasses");

			if ($type == '') {
				$this->modx->log(modX::LOG_LEVEL_ERROR, 'К машине {$id} не прикручен класс!');
			} else {
				$carscontainer = $this->modx->getOption('carscontainer');
				$class = $this->modx->getObject("modResource", $type);

				if ($class) {
					$pagetitle = $class->get("id");
					if ($car->get("parent") == $carscontainer) {
						if (isset($data["datefrom"]) && isset($data["dateto"])) {
							$seasons = array();
							$seasonsdays = array();
							/* ------------- Берем количество дней ------------------ */
							if ($data["low"] > 0) {
								$seasonsdays['low'] = $data["low"];
								$seasons["low"] = $data["low"];
							}
							if ($data["middle"] > 0) {
								$seasonsdays['middle'] = $data["middle"];
								$seasons["middle"] = $data["middle"];
							}
							if ($data["big"] > 0) {
								$seasonsdays['big'] = $data["big"];
								$seasons["big"] = $data["big"];
							}
							$alldays = $data["all"];
							$seasons["all"] = $data["all"];
							/* ------------- Берем количество дней ------------------ */
							// известен регион
							if (isset($data["placepickup"])) {
								$prices = $this->modx->getObject("modResource", $data["placepickup"]);
								if ($prices) {
									$tv = $prices->getTVValue("carprices");
									if ($tv == '') {
										$parent = $prices->get("parent");
										$prices = $this->modx->getObject("modResource", $parent);
										$tv = $prices->getTVValue("carprices");
										if ($tv != '') {
											$filesprices = json_decode($tv, true);
											foreach ($filesprices as $item) {
												if ($item["class"] == $pagetitle) {
													$file = $item["file"];
												}
											}
										} else {
											$this->modx->log(modX::LOG_LEVEL_ERROR, 'Пустой TV у родителя региона!');
										}
									} else {
										$filesprices = json_decode($tv, true);
										foreach ($filesprices as $item) {
											if ($item["class"] == $pagetitle) {
												$file = $item["file"];
											}
										}
									}
									if ($file == '') {
										$this->modx->log(modX::LOG_LEVEL_ERROR, 'Не найден файл с ценами у региона!');
										// TODO: взятие файла у класса авто

									} else {
										$this->modx->log(modX::LOG_LEVEL_ERROR, "Беру файл - " . $this->modx->getOption("base_path") . 'assets/files/' . $file);
										$excel = PHPExcel_IOFactory::load($this->modx->getOption("base_path") . 'assets/files/' . $file);
										$config = array(
											'cprice_low' => 2,
											'wprice_low' => 3,
											'cprice_middle' => 4,
											'wprice_middle' => 5,
											'cprice_big' => 6,
											'wprice_big' => 7
										);
										$seasondaysprice = array();
										$seasondaysprice["all"]["carprice"] = array();
										$seasondaysprice["all"]["wprice"] = array();

										foreach ($seasonsdays as $key => $value) {
											if (intval($seasons["all"]) > intval($value)) {
												// если количество дней в сезоне не совпадает с общим (пересечение сезонов)
												$dayscounter = $seasons["all"];
											} else {
												$dayscounter = intval($value);
											}
											if ($dayscounter > 31) {
												$dayscounter = 31;
											}
											for ($i = 1; $i < 16; $i++) {
												// Бежим по файлу
												$cellvalue = $excel->getActiveSheet()->getCellByColumnAndRow($i, 1)->getValue();
												$values = explode("-", $cellvalue);
												if (intval($dayscounter) == intval($values[0])) {
													$row = $config["cprice_" . $key];
													$carprice = $excel->getActiveSheet()->getCellByColumnAndRow($i, $row)->getValue();
													$row = $config["wprice_" . $key];
													$wprice = $excel->getActiveSheet()->getCellByColumnAndRow($i, $row)->getValue();
													$carprices = explode("/", $carprice);
													if (count($carprices) > 1) {
														$seasondaysprice[$key]["carprice"][] = $carprices[0];
														$seasondaysprice["all"]["carprice"][] = $carprices[0];
													} else {
														$seasondaysprice[$key]["carprice"][] = $carprices[0];
														$seasondaysprice["all"]["carprice"][] = $carprices[0];
													}
													$wprices = explode("/", $wprice);
													if (count($wprices) > 1) {
														$seasondaysprice[$key]["wprice"][] = $wprices[0];
														$seasondaysprice["all"]["wprice"][] = $wprices[0];
													} else {
														$seasondaysprice[$key]["wprice"][] = $wprices[0];
														$seasondaysprice["all"]["wprice"][] = $wprices[0];
													}
												}
												if (intval($dayscounter) == intval($values[1])) {
													$row = $config["cprice_" . $key];
													$carprice = $excel->getActiveSheet()->getCellByColumnAndRow($i, $row)->getValue();
													$row = $config["wprice_" . $key];
													$wprice = $excel->getActiveSheet()->getCellByColumnAndRow($i, $row)->getValue();
													$carprices = explode("/", $carprice);
													if (count($carprices) > 1) {
														$seasondaysprice[$key]["carprice"][] = $carprices[1];
														$seasondaysprice["all"]["carprice"][] = $carprices[1];
													} else {
														$seasondaysprice[$key]["carprice"][] = $carprices[0];
														$seasondaysprice["all"]["carprice"][] = $carprices[0];
													}
													$wprices = explode("/", $wprice);
													if (count($wprices) > 1) {
														$seasondaysprice[$key]["wprice"][] = $wprices[1];
														$seasondaysprice["all"]["wprice"][] = $wprices[1];
													} else {
														$seasondaysprice[$key]["wprice"][] = $wprices[0];
														$seasondaysprice["all"]["wprice"][] = $wprices[0];
													}
												}
												//$seasondaysprice["all"]["carprice"][] = $seasondaysprice[$key]["carprice"];
												//$seasondaysprice["all"]["wprice"][] = $seasondaysprice[$key]["wprice"];
											}
										}
										$result = array(
											'carprice' => 0,
											'wprice' => 0,
											'allprice' => 0,
											'allwprice' => 0,
											'id' => $id
										);
										foreach ($seasondaysprice as $key => $value) {
											if ($key != "all") {
												$result['allprice'] += $value['carprice'][0] * $seasonsdays[$key];
											}
											if ($key != "all") {
												$result['allwprice'] += $value['wprice'][0] * $seasonsdays[$key];
											}
										}
										if (count($seasondaysprice['low']['carprice']) > 0 && count($seasondaysprice['low']['wprice'])) {
											$result['carprice'] = min($seasondaysprice['low']['carprice']);
											$result['wprice'] = min($seasondaysprice['low']['wprice']);
										}
										if (count($seasondaysprice['middle']['carprice']) > 0 && count($seasondaysprice['middle']['wprice'])) {
											if ($result['carprice'] == 0 && $result['wprice'] == 0) {
												$result['carprice'] = min($seasondaysprice['middle']['carprice']);
												$result['wprice'] = min($seasondaysprice['middle']['wprice']);
											} else {
												if ($result['carprice'] > min($seasondaysprice['middle']['carprice'])) {
													$result['carprice'] = min($seasondaysprice['middle']['carprice']);
												}
												if ($result['wprice'] > min($seasondaysprice['middle']['wprice'])) {
													$result['wprice'] = min($seasondaysprice['middle']['wprice']);
												}
											}
										}
										if (count($seasondaysprice['big']['carprice']) > 0 && count($seasondaysprice['big']['wprice'])) {
											if ($result['carprice'] == 0 && $result['wprice'] == 0) {
												$result['carprice'] = min($seasondaysprice['big']['carprice']);
												$result['wprice'] = min($seasondaysprice['big']['wprice']);
											} else {
												if ($result['carprice'] > min($seasondaysprice['big']['carprice'])) {
													$result['carprice'] = $seasondaysprice['big']['carprice'];
												}
												if ($result['wprice'] > min($seasondaysprice['big']['wprice'])) {
													$result['wprice'] = min($seasondaysprice['big']['wprice']);
												}
											}
										}
										if ($data["placepickup"]) {
											$place = $this->modx->getObject("modResource", $_SESSION["cardata"]["placepickup"]);
											if($place){
												$result['dprice'] = $place->getTVValue('price');
											}
										}
										/* закомментить эти строки, если нужно минимальное значение */
										$result["alldays"] = $alldays;
										$result['def'] = (float)$result["allprice"] / $result["alldays"];
										$result['carprice'] = round($result['def'], 2);
										$result['wprice'] = round($result["allwprice"] / $result["alldays"], 2);
										/* закомментить эти строки, если нужно минимальное значение */
										$this->modx->log(1, print_r($result, 1));
										return $result;
									}
								} else {
									$this->modx->log(modX::LOG_LEVEL_ERROR, 'Неизвестный город!');
								}
							}
						}

					}
				}
			}
		}else{
			$this->modx->log(1, "Не найден товар - ".$id);
		}
	}

	/**
	 * Оформляем заказ
	 *
	 * @return array
	 */
	public function madeOffer($data){
		// проверяем есть ли даты
		if (isset($_SESSION["cardata"]["datefrom"]) && isset($_SESSION["cardata"]["dateto"])) {
			$datefrom = $_SESSION["cardata"]["datefrom"];
			$dateto = $_SESSION["cardata"]["dateto"];
			$alldays = $_SESSION["cardata"]["all"];
			// SAVE miniShop2 order
			if ($_POST['resource'] && $_POST['name'] && $_POST["email"]) {
				$scriptProperties = array(
					'json_response' => true,
					'max_count' => 1000,
					'allow_deleted' => false,
					'allow_unpublished' => false
				);

				$miniShop2 = $this->modx->getService('minishop2', 'miniShop2', MODX_CORE_PATH . 'components/minishop2/model/minishop2/', $scriptProperties);
				// генерация опции машины
				$option = array(
					"cardata" => $_SESSION["cardata"],
					"cars" => $_SESSION["cars"]
				);
				// init minishop2
				$miniShop2->initialize($data['ctx'], $scriptProperties);
				$miniShop2->cart->clean();
				$arr = json_decode($miniShop2->cart->add($data["resource"], 1, $option), true);
				$res = $this->modx->getObject("msProduct", $data["resource"]);

				// Формируем заказ
				$miniShop2->order->add('receiver', $data['name']);
				$miniShop2->order->add('email', $data["email"]);
				$miniShop2->order->add('phone', $data["phone"]);
				$miniShop2->order->add('datefrom', date('Y-m-d H:i:s', $datefrom));
				$miniShop2->order->add('dateto', date('Y-m-d H:i:s', $dateto));
				$miniShop2->order->add('placedropoff', $data["place"]);
				$miniShop2->order->add('regionpickup', $_SESSION["cardata"]["regionpickup"]);
				$miniShop2->order->add('placepickup', $_SESSION["cardata"]["placepickup"]);
				$miniShop2->order->add('place', $data['place']);
				//$miniShop2->order->add('comment', $newComment);
				$miniShop2->order->add('delivery', 1);
				$miniShop2->order->add('payment', 1);
				$regionpickup = $_SESSION["cardata"]["regionpickup"];
				$placepickup = $_SESSION["cardata"]["placepickup"];

				$orderfeed = $miniShop2->order->submit();
				$arr = json_decode($orderfeed, true);
				if ($arr['success'] == true && $arr["data"]["msorder"]) {
					$order = $this->modx->getObject("msOrder", $arr["data"]["msorder"]);
					if($order){
						$order->set('datefrom', date('Y-m-d H:i:s', $datefrom));
						$order->set('dateto', date('Y-m-d H:i:s', $dateto));
						$order->set('placedropoff', $data["place"]);
						$order->set('regionpickup', $regionpickup);
						$order->set('placepickup', $placepickup);
						$order->set('place', $data['place']);
						$order->save();
					}
					$arr["location"] = $this->modx->makeUrl($this->modx->getOption("offersuccespage"), '', array('msorder' => $arr["data"]["msorder"]));
				} else {
					$arr = array(
						'success' => 0,
						'data' => array(
							'error' => $this->modx->lexicon('rentacar_error_submit')
						)
					);
				}
			} else {
				$arr = array(
					'success' => 0,
					'data' => array(
						'error' => $this->modx->lexicon('rentacar_no_detail_bron').' 2'
					)
				);
			}
		} else {
			$arr = array(
				'success' => 0,
				'data' => array(
					'error' => $this->modx->lexicon('rentacar_no_detail_bron').' 1',
					'showform' => 1
				)
			);
		}
		echo json_encode($arr);
	}
}