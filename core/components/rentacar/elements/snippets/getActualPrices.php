<?php
$suffix = '_'.$id;
$rentacar = $modx->getService('rentacar','rentacar',$modx->getOption('rentacar_core_path',null,$modx->getOption('core_path').'components/rentacar/').'model/rentacar/',$scriptProperties);
if (!$rentacar) {
	$modx->log(1, 'Could not load rentacar class!');
}else{
	$rentacar->initialize($modx->context->key);
}

$assets_path = $modx->getOption('rentacar_assets_path',null,$modx->getOption('base_path').'assets/components/rentacar/');

require_once $assets_path.'libs/PHPExcel/PHPExcel.php';

$product = $modx->getObject("msProduct", $id);

$file = '';
$car = $product;
$type = $car->getTVValue("vnutrclasses");
if($type==''){
	$modx->log(modX::LOG_LEVEL_ERROR, '[getactualprices:11] к машине не прикручен класс!');
}else{
	$carscontainer = $modx->getOption('carscontainer');
	$class = $modx->getObject("modResource", $type);
	if($class){
		$pagetitle = $class->get("id");
		if($product->get("parent")==$carscontainer){
			if(isset($_SESSION["cardata"]["datefrom"])&&isset($_SESSION["cardata"]["dateto"])){
				$datefrom = $_SESSION["cardata"]["datefrom"];
				$dateto = $_SESSION["cardata"]["dateto"];
				$seasons = array();
				$seasonsdays = array();

				/* ------------- Берем количество дней ------------------ */
				if($_SESSION["cardata"]["low"]>0){
					$seasonsdays['low'] = $_SESSION["cardata"]["low"];
				}
				if($_SESSION["cardata"]["middle"]>0){
					$seasonsdays['middle'] = $_SESSION["cardata"]["middle"];
				}
				if($_SESSION["cardata"]["big"]>0){
					$seasonsdays['big'] = $_SESSION["cardata"]["big"];
				}
				$alldays = $_SESSION["cardata"]["all"];
				/* ------------- Берем количество дней ------------------ */

				$seasons["low"] = $_SESSION["cardata"]["low"];
				$seasons["middle"] = $_SESSION["cardata"]["middle"];
				$seasons["big"] = $_SESSION["cardata"]["big"];
				$seasons["all"] = $_SESSION["cardata"]["all"];


				if(isset($_SESSION["cardata"]["placepickup"])){
					$prices = $modx->getObject("modResource", $_SESSION["cardata"]["placepickup"]);
					if($prices){
						$tv = $prices->getTVValue("carprices");
						if($tv==''){
							//$modx->log(modX::LOG_LEVEL_ERROR, '[getactualprices:21] пустой TV!');
							$parent = $prices->get("parent");
							$prices = $modx->getObject("modResource", $parent);
							$tv = $prices->getTVValue("carprices");
							if($tv!=''){
								$filesprices = json_decode($tv, true);
								foreach($filesprices as $item){
									if($item["class"]== $pagetitle){
										$file = $item["file"];
									}
								}
							}else{
								$modx->log(modX::LOG_LEVEL_ERROR, '[getactualprices:33] пустой TV у родителя!');
							}
						}else{
							$filesprices = json_decode($tv, true);
							foreach($filesprices as $item){
								if($item["class"]== $pagetitle){
									$file = $item["file"];
								}
							}
						}
						if($file==''){
							$modx->log(modX::LOG_LEVEL_ERROR, '[getPrices:156] не найден файл с ценами!');
						}else{
							$modx->log(modX::LOG_LEVEL_ERROR, "Беру файл - ".$modx->getOption("base_path").'assets/files/'.$file);
							$excel = PHPExcel_IOFactory::load($modx->getOption("base_path").'assets/files/'.$file);
							//getCellByColumnAndRow(столбец нумерация с нуля, 1)
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


							foreach($seasonsdays as $key => $value){
								if(intval($seasons["all"])>intval($value)){
									// если количество дней в сезоне не совпадает с общим (пересечение сезонов)
									$dayscounter = $seasons["all"];
								}else{
									$dayscounter = intval($value);
								}
								if($dayscounter>31){
									$dayscounter = 31;
								}
								for($i=1;$i<16;$i++){
									// Бежим по файлу
									$cellvalue = $excel->getActiveSheet()->getCellByColumnAndRow($i, 1)->getValue();
									$values = explode("-", $cellvalue);
									$carprice = 0;
									$wprice = 0;
									//$seasondaysprice["all"]["carprice"][] = $seasondaysprice[$key]["carprice"];
									//$seasondaysprice["all"]["wprice"][] = $seasondaysprice[$key]["wprice"];
									if(intval($dayscounter)==intval($values[0])){
										$row = $config["cprice_".$key];
										$carprice = $excel->getActiveSheet()->getCellByColumnAndRow($i, $row)->getValue();
										$row = $config["wprice_".$key];
										$wprice = $excel->getActiveSheet()->getCellByColumnAndRow($i, $row)->getValue();
										$carprices = explode("/", $carprice);
										if(count($carprices)>1){
											$seasondaysprice[$key]["carprice"][] = $carprices[0];
											$seasondaysprice["all"]["carprice"][] = $carprices[0];
										}else{
											$seasondaysprice[$key]["carprice"][] = $carprices[0];
											$seasondaysprice["all"]["carprice"][] = $carprices[0];
										}
										$wprices = explode("/", $wprice);
										if(count($wprices)>1){
											$seasondaysprice[$key]["wprice"][] = $wprices[0];
											$seasondaysprice["all"]["wprice"][] = $wprices[0];
										}else{
											$seasondaysprice[$key]["wprice"][] = $wprices[0];
											$seasondaysprice["all"]["wprice"][] = $wprices[0];
										}
									}
									if(intval($dayscounter)==intval($values[1])){
										$row = $config["cprice_".$key];
										$carprice = $excel->getActiveSheet()->getCellByColumnAndRow($i, $row)->getValue();
										$row = $config["wprice_".$key];
										$wprice = $excel->getActiveSheet()->getCellByColumnAndRow($i, $row)->getValue();
										$carprices = explode("/", $carprice);
										if(count($carprices)>1){
											$seasondaysprice[$key]["carprice"][] = $carprices[1];
											$seasondaysprice["all"]["carprice"][] = $carprices[1];
										}else{
											$seasondaysprice[$key]["carprice"][] = $carprices[0];
											$seasondaysprice["all"]["carprice"][] = $carprices[0];
										}
										$wprices = explode("/", $wprice);
										if(count($wprices)>1){
											$seasondaysprice[$key]["wprice"][] = $wprices[1];
											$seasondaysprice["all"]["wprice"][] = $wprices[1];
										}else{
											$seasondaysprice[$key]["wprice"][] = $wprices[0];
											$seasondaysprice["all"]["wprice"][] = $wprices[0];
										}
									}
									//$seasondaysprice["all"]["carprice"][] = $seasondaysprice[$key]["carprice"];
									//$seasondaysprice["all"]["wprice"][] = $seasondaysprice[$key]["wprice"];
								}
							}
							$result = array(
								'carprice'.$suffix => 0,
								'wprice'.$suffix => 0,
								'allprice'.$suffix => 0,
								'allwprice'.$suffix => 0,
								'id'.$suffix => $id
							);
							foreach($seasondaysprice as $key => $value){
								if($key!="all"){
									$result['allprice'.$suffix] += $value['carprice'][0]*$seasonsdays[$key];
								}
								if($key!="all"){
									$result['allwprice'.$suffix] += $value['wprice'][0]*$seasonsdays[$key];
								}
							}
							if(count($seasondaysprice['low']['carprice'])>0&&count($seasondaysprice['low']['wprice'])){
								$result['carprice'.$suffix] = min($seasondaysprice['low']['carprice']);
								$result['wprice'.$suffix] = min($seasondaysprice['low']['wprice']);
							}
							if(count($seasondaysprice['middle']['carprice'])>0&&count($seasondaysprice['middle']['wprice'])){
								if($result['carprice'.$suffix]==0&&$result['wprice']==0){
									$result['carprice'.$suffix] = min($seasondaysprice['middle']['carprice']);
									$result['wprice'.$suffix] = min($seasondaysprice['middle']['wprice']);
								}else{
									if($result['carprice'.$suffix]>min($seasondaysprice['middle']['carprice'])){
										$result['carprice'.$suffix] = min($seasondaysprice['middle']['carprice']);
									}
									if($result['wprice'.$suffix]>min($seasondaysprice['middle']['wprice'])){
										$result['wprice'.$suffix] = min($seasondaysprice['middle']['wprice']);
									}
								}
							}
							if(count($seasondaysprice['big']['carprice'])>0&&count($seasondaysprice['big']['wprice'])){
								if($result['carprice'.$suffix]==0&&$result['wprice']==0){
									$result['carprice'.$suffix] = min($seasondaysprice['big']['carprice']);
									$result['wprice'.$suffix] = min($seasondaysprice['big']['wprice']);
								}else{
									if($result['carprice'.$suffix]>min($seasondaysprice['big']['carprice'])){
										$result['carprice'.$suffix] = $seasondaysprice['big']['carprice'];
									}
									if($result['wprice'.$suffix]>min($seasondaysprice['big']['wprice'])){
										$result['wprice'.$suffix] = min($seasondaysprice['big']['wprice']);
									}
								}
							}
							if($_SESSION["cardata"]["placepickup"]){
								$place = $modx->getObject("modResource", $_SESSION["cardata"]["placepickup"]);
								$result['dprice'.$suffix] = $place->getTVValue('price');
							}

							/* закомментить эти строки, если нужно минимальное значение */
							$result["alldays".$suffix] = $alldays;
							$result['def'.$suffix] = (float) $result["allprice".$suffix]/$result["alldays".$suffix];
							$result['carprice'.$suffix] = round($result['def'.$suffix], 0);
							$result['wprice'.$suffix] = round($result["allwprice".$suffix]/$result["alldays".$suffix], 2);
							/* закомментить эти строки, если нужно минимальное значение */

							//$modx->log(modX::LOG_LEVEL_ERROR, print_r($result,1));
							//$modx->log(modX::LOG_LEVEL_ERROR, print_r($seasondaysprice,1));

							//print_r('<pre>');
							//print_r($_SESSION);
							//print_r('</pre>');

							$modx->setPlaceholders($result);
						}
					}else{
						$modx->log(modX::LOG_LEVEL_ERROR, '[getactualprices:45] неизвестный город!');
					}
				}
			}

		}
	}
}