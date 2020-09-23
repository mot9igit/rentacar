<?php

class rentacarResourceGetListProcessor extends modObjectGetListProcessor
{
	public $objectType = 'modResource';
	public $classKey = 'modResource';
	public $defaultSortField = 'pagetitle';
	public $defaultSortDirection = 'ASC';
	//public $permission = 'list';


	/**
	 * We do a special check of permissions
	 * because our objects is not an instances of modAccessibleObject
	 *
	 * @return boolean|string
	 */
	public function beforeQuery()
	{
		if (!$this->checkPermissions()) {
			return $this->modx->lexicon('access_denied');
		}

		return true;
	}


	/**
	 * @param xPDOQuery $c
	 *
	 * @return xPDOQuery
	 */
	public function prepareQueryBeforeCount(xPDOQuery $c)
	{
		$where = array();
		$query = trim($this->getProperty('query'));
		if ($query) {
			$where['name:LIKE'] = "%{$query}%";
			$where['OR:description:LIKE'] = "%{$query}%";
		}
		$parent = trim($this->getProperty('parent'));
		if ($parent) {
			$where['parent'] = $parent;
		}
		$class_key = trim($this->getProperty('class_key'));
		if ($class_key) {
			$where['class_key'] = $class_key;
		}
		if($where){
			$c->where($where);
		}

		return $c;
	}
}

return 'rentacarResourceGetListProcessor';