<?php
if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {
	return false;
}
$rentacar = $modx->getService('rentacar','rentacar',$modx->getOption('rentacar_core_path',null,$modx->getOption('core_path').'components/rentacar/').'model/rentacar/',$scriptProperties);
if (!$rentacar) {
	$modx->log(1, 'Could not load rentacar class!');
}else{
	$rentacar->initialize($modx->context->key);
}
$pdoFetch = new pdoFetch($modx, $scriptProperties);

$datefrom = $_REQUEST['datepickup'];
$dateto = $_REQUEST['datedropoff'];
$class = $_REQUEST['class'];
if($_REQUEST['region']){
	$region = $_REQUEST['region'];
}else{
	$region = 24;
}

if($datefrom && $dateto && $region){
	// Автомобили
	//$class = 331;
	if($class){
		$criteria = array(
			"tmplvarid" => 7,
			"value" => $class
		);
		$resources = $modx->getCollection("modTemplateVarResource", $criteria);
		$arr = array();
		foreach($resources as $res){
			$arr[] = $res->get('contentid');
		}
	}

	$criteria = array(
		"region" => $region
	);
	if(count($arr)){
		$criteria["resource:IN"] = $arr;
	}
	$resources = $modx->getCollection("rentacar_cars", $criteria);
	$cars = array();
	$autos = array();
	$data = array();
	$data['newoffer']['product_id'] = $arr[0];
	$data['newoffer']['cardata'] =array(
		'datefrom' => strtotime(str_replace(".", "-", $datefrom).' '.$data["timepickup"]),
		'dateto' => strtotime(str_replace(".", "-", $dateto).' '.$data["timedropoff"]),
		'regionpickup' => $region
	);
	$data['timepickup'] = "10:00";
	$data['timedropoff'] = "10:00";
	$data['datefrom'] = strtotime(str_replace(".", "-", $datefrom).' '.$data["timepickup"]);
	$data['dateto'] = strtotime(str_replace(".", "-", $dateto).' '.$data["timedropoff"]);
	$data['region'] = $region;
	$place = $modx->getObject("modResource", array("parent" => $region));
	$data['placepickup'] = $place->get("id");
	$autos = array();
	$auters = array();
	$dater = array();
	foreach($resources as $res){
		$car_id = $res->get("id");
		$resource = $res->get("resource");
		$par = $modx->getObject("modResource", $resource);
		if($par){
			$class = $par->getTVValue("vnutrclasses");
			if($class){
				$par = $modx->getObject("modResource", $class);
				if($par){
					$class = $par->get("pagetitle");
				}
			}
		}
		$dater['autos'][$car_id]["car"] = $res->toArray();
		$dater['autos'][$car_id]["class"] = $class;
		$dater['autos'][$car_id]["timedata"] = $rentacar->getTiming($data);
		$dater['autos'][$car_id]["calcdata"] = $rentacar->calculate($resource, $dater['autos'][$car_id]["timedata"]);
		$autos[$class][] = $car_id;
		$auters[] = $car_id;
	}

	ksort($autos);

	foreach($autos as $key => $val){
		foreach($val as $v){
			$data['autos'][$v] = $dater['autos'][$v];
		}
	}

	$start = new DateTime($datefrom);
	$end = new DateTime($dateto);
	$step = new DateInterval('P1D');
	$period = new DatePeriod($start, $step, $end->modify( '+1 Day' ));

	// Даты
	$dates = array();
	foreach($period as $datetime) {
		$dates[$datetime->format("Y")][$datetime->format("m")][$datetime->format("j")] = $datetime->format("d.m.Y");
	}

	$data['dates'] = $dates;

	$criteria = array(
		"date_on:<=" => $end->format("Y-m-d H:i:s"),
		"date_off:>=" => $start->format("Y-m-d H:i:s"),
		"car_id:IN" => $auters
	);

	$brons = $modx->getCollection("rentacar_cars_avaible", $criteria);

	if (count($brons)){
		foreach($brons as $bron){
			$off = array();
			$off = $bron->toArray();
			// order_data
			$order = $bron->getOne("Order");
			$data_t = array();
			$data_t = $order->toArray();
			$products = $order->getMany('Products');
			$status = $modx->getObject("msOrderStatus", $data_t["status"]);
			$data_t['statuses'] = $status->toArray();
			foreach($products as $product){
				$data_t['products'][] = $product->toArray();
			}
			$data_t['address'] = $order->getOne('Address')->toArray();
			$off["order"] = $data_t;
			$auto_start = new DateTime($off['date_on']);
			$auto_end = new DateTime($off['date_off']);
			$auto_step = new DateInterval('P1D');
			$auto_period = new DatePeriod($auto_start, $auto_step, $auto_end->modify( '+1 Day' ));
			foreach($auto_period as $datetime) {
				$off['dates'][] = $datetime->format("d.m.Y");
			}
			//$count = count($off['dates'])-1;
			//$off["order"]["dateto"] = $off['dates'][$count];
			$data['autos'][$bron->get("car_id")]['brons'][] = $off;
		}
	}
	$modx->log(1, print_r($data['autos'], 1));
	$criteria = array(
		"datefrom:<=" => $end->format("Y-m-d H:i:s"),
		"dateto:>=" => $start->format("Y-m-d H:i:s"),
		"status" => 1
	);

	$orders = $modx->getCollection("msOrder", $criteria);
	if (count($orders)){
		foreach($orders as $order){
			$data_t = array();
			$off = array();
			$data_t = $order->toArray();
			$products = $order->getMany('Products');
			$status = $modx->getObject("msOrderStatus", $data_t["status"]);
			$data_t['statuses'] = $status->toArray();
			foreach($products as $product){
				$data_t['products'][] = $product->toArray();
			}
			$data_t['address'] = $order->getOne('Address')->toArray();
			$off = $data_t;
			$off["order"] = $data_t;
			$off['dates'] = array();
			$order_start = new DateTime($data_t['datefrom']);
			$order_end = new DateTime($data_t['dateto']);
			$order_step = new DateInterval('P1D');
			$order_period = new DatePeriod($order_start, $order_step, $order_end->modify( '+1 Day' ));
			foreach($order_period as $datetime) {
				$off['dates'][] = $datetime->format("d.m.Y");
			}
			$count = count($off['dates'])-1;
			$off["order"]["dateto"] = $off['dates'][$count];
			$data['orders'][] = $off;
		}
	}
	$output = $pdoFetch->getChunk($tpl, $data);

	echo $output;
}