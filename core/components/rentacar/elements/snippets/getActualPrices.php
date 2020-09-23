<?php
$suffix = '_'.$id;
$rentacar = $modx->getService('rentacar','rentacar',$modx->getOption('rentacar_core_path',null,$modx->getOption('core_path').'components/rentacar/').'model/rentacar/',$scriptProperties);
if (!$rentacar) {
	$modx->log(1, 'Could not load rentacar class!');
}else{
	$rentacar->initialize($modx->context->key);
}

$calc = $rentacar->calculate($id, $_SESSION["cardata"]);

$result["alldays".$suffix] = $calc["alldays"];
$result['def'.$suffix] = $calc["def"];
$result['carprice'.$suffix] = $calc["carprice"];
$result['wprice'.$suffix] = $calc["wprice"];

$_SESSION["cars"][$id] = $calc;

$modx->setPlaceholders($result);