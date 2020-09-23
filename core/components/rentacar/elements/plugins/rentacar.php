<?php
/** @var modX $modx */
switch ($modx->event->name) {
	case 'OnMODXInit':
		$rentacar = $modx->getService('rentacar','rentacar',$modx->getOption('rentacar_core_path',null,$modx->getOption('core_path').'components/rentacar/').'model/rentacar/',$scriptProperties);
		if (!$rentacar) {
			return 'Could not load rentacar class!';
		}
		$modx->loadClass('msOrder');
		// ORDER fields
		$modx->map['msOrder']['fields']['datefrom'] = 0;
		$modx->map['msOrder']['fieldMeta']['datefrom'] = array(
			'dbtype' => 'datetime',
			'phptype' => 'datetime',
			'null' => true
		);
		$modx->map['msOrder']['fields']['dateto'] = 0;
		$modx->map['msOrder']['fieldMeta']['dateto'] = array(
			'dbtype' => 'datetime',
			'phptype' => 'datetime',
			'null' => true,
		);
		$modx->map['msOrder']['fields']['placedropoff'] = 0;
		$modx->map['msOrder']['fieldMeta']['placedropoff'] = array(
			'dbtype' => 'int',
			'precision' => 10,
			'attributes' => 'unsigned',
			'phptype' => 'integer',
			'null' => true,
			'default' => 0,
		);
		$modx->map['msOrder']['fields']['regionpickup'] = 0;
		$modx->map['msOrder']['fieldMeta']['regionpickup'] = array(
			'dbtype' => 'int',
			'precision' => 10,
			'attributes' => 'unsigned',
			'phptype' => 'integer',
			'null' => true,
			'default' => 0,
		);
		$modx->map['msOrder']['fields']['placepickup'] = 0;
		$modx->map['msOrder']['fieldMeta']['placepickup'] = array(
			'dbtype' => 'int',
			'precision' => 10,
			'attributes' => 'unsigned',
			'phptype' => 'integer',
			'null' => true,
			'default' => 0,
		);
		$modx->map['msOrder']['fields']['place'] = 0;
		$modx->map['msOrder']['fieldMeta']['place'] = array(
			'dbtype' => 'int',
			'precision' => 10,
			'attributes' => 'unsigned',
			'phptype' => 'integer',
			'null' => true,
			'default' => 0,
		);
		break;

	case 'OnWebPageInit':
		// Load extensions
		/** @var rentacar $rentacar */
		$rentacar = $modx->getService('rentacar','rentacar',$modx->getOption('rentacar_core_path',null,$modx->getOption('core_path').'components/rentacar/').'model/rentacar/',$scriptProperties);
		if (!$rentacar) {
			return 'Could not load rentacar class!';
		}else{
			$modx->log(1, $modx->context->key);
			$rentacar->initialize($modx->context->key);
		}
		break;

	case 'OnHandleRequest':
		// Handle ajax requests
		$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
		if (empty($_REQUEST['rentacar_action']) || !$isAjax) {
			return;
		}
		/** @var rentacar $rentacar */
		$rentacar = $modx->getService('rentacar','rentacar',$modx->getOption('rentacar_core_path',null,$modx->getOption('core_path').'components/rentacar/').'model/rentacar/',$scriptProperties);
		if (!$rentacar) {
			//$rentacar->initialize($_REQUEST["ctx"]);
			$response = $rentacar->handleRequest($_REQUEST['rentacar_action'], @$_POST);
			@session_write_close();
			exit($response);
		}
		break;
	case 'msOnAddToCart':
		$tmp = $cart->get();
		foreach($tmp as $cartkey => $cartproduct){
			if ($product = $modx->getObject('msProduct', $cartproduct['id'])) {
				if($product->get("parent") == $modx->getOption("carscontainer")) {
					$tmp[$cartkey]['price'] = $_SESSION["cardata"]["options_price"] + $_SESSION["cardata"]["deliverys_price"] + $_SESSION["cars"][$cartproduct['id']]["allprice_".$cartproduct['id']];
					if($_SESSION["cardata"]["warrantys"]["price"] > 0){
						$tmp[$cartkey]['price'] += $_SESSION["cars"][$cartproduct['id']]["allwprice_".$cartproduct['id']];
					}
				}
			}
		}
		$cart->set($tmp);
		break;
	case "msOnCreateOrder":
		unset($_SESSION["cardata"]);
		unset($_SESSION["cars"]);
		break;
}