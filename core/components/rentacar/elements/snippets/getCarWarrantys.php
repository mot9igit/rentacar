<?php
$rentacar = $modx->getService('rentacar','rentacar',$modx->getOption('rentacar_core_path',null,$modx->getOption('core_path').'components/rentacar/').'model/rentacar/',$scriptProperties);
if (!$rentacar) {
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not load rentacar class!');
}else{
	$rentacar->initialize($modx->context->key);
}

$criteria = array(
	'active' => 1
);
$options = $modx->getCollection("rentacar_cars_warranty", $criteria);
if (!$modx->loadClass('pdofetch', MODX_CORE_PATH . 'components/pdotools/model/pdotools/', false, true)) {
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not load pdoFetch class!');
	return false;
}
$pdoFetch = new pdoFetch($modx, $scriptProperties);
if($pdoFetch){
	foreach($options as $option){
		$data['options'][] = $option->toArray();
		if($offer_warrantys){
			$data['offer_warrantys'] = $offer_warrantys;
		}
		if($offer_cars){
			$data['offer_cars'] = $offer_cars;
		}
	}
	echo $pdoFetch->getChunk($tpl, $data);
}else{
	$modx->log(modX::LOG_LEVEL_ERROR, 'Could not load pdoFetch class!');
}