<?php
/** @var modX $modx */
switch ($modx->event->name) {
	case 'OnMODXInit':
		// Load extensions
		/** @var rentacar $rentacar */
		$rentacar = $modx->getService('rentacar','rentacar',$modx->getOption('rentacar_core_path',null,$modx->getOption('core_path').'components/rentacar/').'model/rentacar/',$scriptProperties);
		if (!$rentacar) {
			return 'Could not load rentacar class!';
		}else{
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
			$response = $rentacar->handleRequest($_REQUEST['rentacar_action'], @$_POST);
			@session_write_close();
			exit($response);
		}
		break;
}