<?php
// ?tv|country=27
$site_url = "https://".$_SERVER['HTTP_HOST'];
$site = $site_url;
$requestos = $site.$_SERVER['REQUEST_URI'];
$requestos = str_replace("&showactual=1", "", $requestos);

if($_POST["region"]||$_SESSION["cardata"]["region"]){
	if($_POST["region"]){
		$urler = $modx->makeUrl($modx->getOption("carscontainer"), '', array('country' => $_POST["region"]), 'full');
		if($_GET['showactual']){
			$url = $modx->makeUrl($modx->getOption("carscontainer"), '', array('country' => $_POST["region"], 'showactual' => 1), 'full');
		}else{
			$url = $modx->makeUrl($modx->getOption("carscontainer"), '', array('country' => $_POST["region"]), 'full');
		}
	}else{
		$urler = $modx->makeUrl($modx->getOption("carscontainer"), '', array('country' => $_SESSION["cardata"]["region"]), 'full');
		if($_GET['showactual']){
			$url = $modx->makeUrl($modx->getOption("carscontainer"), '', array('country' => $_SESSION["cardata"]["region"], 'showactual' => 1), 'full');
		}else{
			$url = $modx->makeUrl($modx->getOption("carscontainer"), '', array('country' => $_SESSION["cardata"]["region"]), 'full');
		}
	}
	if($_POST["redirect"]=="1"||$modx->resource->get("id")==$modx->getOption("carscontainer")){

		if($urler != $requestos){
			$modx->log(modX::LOG_LEVEL_ERROR, $urler.' != '.$requestos);
			$modx->sendRedirect($url);
		}
	}else{
		$output = array(
			'success' => true,
			'data' => $_SESSION["cardata"]
		);
		return json_decode($output);
	}
}else{
	$url = $modx->makeUrl($modx->getOption("carscontainer"),'','','full');
	if($_POST["redirect"]=="1"||$modx->resource->get("id")==$modx->getOption("carscontainer")){
		if(!$_GET["country"]){
			//$modx->sendRedirect($url);
		}elseif($url != $requestos){
			//$modx->log(modX::LOG_LEVEL_ERROR, $url.' != '.$requestos);
			//$modx->sendRedirect($url);
		}
	}else{
		$output = array(
			'success' => true,
			'data' => $_SESSION["cardata"]
		);
		return json_decode($output);
	}
}