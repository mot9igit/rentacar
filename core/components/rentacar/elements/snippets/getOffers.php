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
$sortby = $modx->getOption('sortby', $scriptProperties, 'msOrder.id');
$sortdir = $modx->getOption('sortbir', $scriptProperties, 'DESC');
$limit = $modx->getOption('limit', $scriptProperties, 5);
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, "\n");
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);



// Build query
$c = $modx->newQuery('msOrder', 'msOrder');
$c->sortby($sortby, $sortdir);
$total = $modx->getCount("msOrder", $q);
$c->leftJoin('rentacar_cars_avaible','rentacar_cars_avaible', array('rentacar_cars_avaible.order_id = msOrder.id'));
$c->select($modx->getSelectColumns('msOrder', 'msOrder'));
$c->select(array(
	"rentacar_cars_avaible.order_id",
	"rentacar_cars_avaible.date_on",
	"rentacar_cars_avaible.date_off",
	"rentacar_cars_avaible.region_on",
	"rentacar_cars_avaible.region_off",
	"rentacar_cars_avaible.description",
	"rentacar_cars_avaible.active",
	"rentacar_cars_avaible.car_id"
));
$totalVar = $modx->getOption('totalVar', $scriptProperties, 'total');
//$c->where();
$limit = $modx->getOption('limit', $scriptProperties, 10);
$offset = $modx->getOption('offset', $scriptProperties, 0);
$c->limit($limit, $offset);
$c->prepare();
$modx->log(1, $c->toSQL());
$items = $modx->getCollection('msOrder', $c);
$modx->setPlaceholder($totalVar,$total);

$data = array();
foreach ($items as $item) {
	$data_t = array();
	$data_t = $item->toArray();
	$products = $item->getMany('Products');
	$status = $modx->getObject("msOrderStatus", $data_t["status"]);
	$data_t['statuses'] = $status->toArray();
	foreach($products as $product){
		$data_t['products'][] = $product->toArray();
	}
	if ($data_t['car_id']){
		$car = $modx->getObject("rentacar_cars", $data_t['car_id']);
		if($car){
			$data_t['car'] = $car->toArray();
		}
	}
	$data_t['address'] = $item->getOne('Address')->toArray();
	$data['offers'][] = $data_t;
}
if (!empty($toPlaceholder)) {
	// If using a placeholder, output nothing and set output to specified placeholder
	$modx->setPlaceholder($toPlaceholder, $output);

	return '';
}else{
	$output .= $pdoFetch->getChunk($tpl, $data);
}
return $output;