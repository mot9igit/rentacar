<?php
define('MODX_API_MODE', true);
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php')) {
	/** @noinspection PhpIncludeInspection */
	require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';
} else {
	require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/index.php';
}

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');

if (!empty($_REQUEST['pageId'])) {
	if ($resource = $modx->getObject('modResource', (int)$_REQUEST['pageId'])) {
		if ($resource->get('context_key') != 'web') {
			$modx->switchContext($resource->get('context_key'));
		}
		$modx->resource = $resource;
	}
}


$rentacar = $modx->getService('rentacar','rentacar',$modx->getOption('rentacar_core_path',null,$modx->getOption('core_path').'components/rentacar/').'model/rentacar/',$scriptProperties);
if (!$rentacar) {
	return 'Could not load rentacar class!';
}else{
	$rentacar->initialize($modx->context->key);
}

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
	$modx->sendRedirect($modx->makeUrl($modx->getOption('site_start'), '', '', 'full'));
} else {
	$out = $rentacar->handleRequest($_REQUEST['action'], @$_POST);
	if(is_array ($out)){
		echo $response = json_encode($out);
	}else{
		echo $response = $out;
	}

}

@session_write_close();