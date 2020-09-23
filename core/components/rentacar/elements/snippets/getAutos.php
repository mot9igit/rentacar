<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var rentacar $rentacar */
$rentacar = $modx->getService('rentacar', 'rentacar', MODX_CORE_PATH . 'components/rentacar/model/', $scriptProperties);
if (!$rentacar) {
	return 'Could not load rentacar class!';
}

if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {
	return false;
}
$pdoFetch = new pdoFetch($modx, $scriptProperties);

// Do your snippet code here. This demo grabs 5 items from our custom table.
$tpl = $modx->getOption('tpl', $scriptProperties, 'Item');
$sortby = $modx->getOption('sortby', $scriptProperties, 'rentacar_cars.id');
$sortdir = $modx->getOption('sortbir', $scriptProperties, 'DESC');
$limit = $modx->getOption('limit', $scriptProperties, 9999);
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, "\n");
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);

$res = $modx->getObject("modResource", $parents);
if($res){
	if($res->get("context_key") == "web"){
		$parent = $parents;
	}else{
		$parent = $modx->runSnippet("!BabelTranslation",
			array(
				"resourceId" => $parents,
				"contextKey" => "web"
			)
		);
	}

	$ppobject = $modx->getObject("modResource", $parent);
	if($ppobject){
		$class = $ppobject->getTVValue("vnutrclasses");

		$ppobjects = $modx->getCollection("modTemplateVarResource", array("tmplvarid" => 7, "value" => $class));
		$parents = array();
		foreach($ppobjects as $obj){
			$parents[] = $obj->get("contentid");
		}

		$c = $modx->newQuery('rentacar_cars');
		$c->where(
			array(
				"resource:IN" => $parents,
				"region" => $data["regionpickup"]
			)
		);
		$c->prepare();

		$items = $modx->getCollection('rentacar_cars', $c);

		$output = '';

		$data['datefrom'] = date("Y-m-d H:i:s", $data['datefrom']);
		$data['dateto'] = date("Y-m-d H:i:s", $data['dateto']);

		foreach ($items as $item) {
			// check checked
			$criteria = array(
				'order_id' => $offer_id
			);
			$datas = $item->toArray();
			$bron = $modx->getObject("rentacar_cars_avaible", $criteria);
			// check bussy
			$prefix = $modx->getOption('table_prefix');
			$sql = "SELECT * FROM `{$prefix}rentacar_cars_avaible` AS `rentacar_cars_avaible` WHERE (`rentacar_cars_avaible`.`date_on` BETWEEN \"{$data['datefrom']}\" AND \"{$data['dateto']}\" OR `rentacar_cars_avaible`.`date_off` BETWEEN \"{$data['datefrom']}\" AND \"{$data['dateto']}\" OR (`rentacar_cars_avaible`.`date_on` <= \"{$data['datefrom']}\" AND `rentacar_cars_avaible`.`date_off` >= \"{$data['dateto']}\")) AND `rentacar_cars_avaible`.`car_id` = {$item->get('id')}";
			if($offer_id){
				$sql .= " AND `rentacar_cars_avaible`.`order_id` != {$offer_id}";
			}
			$modx->log(1, $sql);
			$result = $modx->query($sql);
			$brons = $result->fetch(PDO::FETCH_ASSOC);
			if (count($brons) == 1) {
				if($bron){
					if($bron->get("car_id") == $data["id"]){
						$datas['checked'] = 1;
					}
				}
				$output .= $pdoFetch->getChunk($tpl, $datas);
			}
		}

		echo $output;
	}
}