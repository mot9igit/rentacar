<?php

class rentacarCarGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'rentacar_cars';
    public $classKey = 'rentacar_cars';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'DESC';
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
		$c->leftJoin('modResource', 'Resource');
		$c->leftJoin('modResource', 'Region');
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
            ]);
        }
		$c->select(
			$this->modx->getSelectColumns('rentacar_cars', 'rentacar_cars', '', array('resource'), true) .
			', Resource.pagetitle as resource_n, Region.pagetitle as region_n'
		);
        $p = $c;
		$p->prepare();
		$this->modx->log(1,  $p->toSQL());
        return $c;
    }


    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        $array['actions'] = [];

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('rentacar_car_update'),
            //'multiple' => $this->modx->lexicon('rentacar_cars_update'),
            'action' => 'updateCar',
            'button' => true,
            'menu' => true,
        ];

        if (!$array['active']) {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('rentacar_car_enable'),
                'multiple' => $this->modx->lexicon('rentacar_cars_enable'),
                'action' => 'enableCar',
                'button' => true,
                'menu' => true,
            ];
        } else {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('rentacar_car_disable'),
                'multiple' => $this->modx->lexicon('rentacar_cars_disable'),
                'action' => 'disableCar',
                'button' => true,
                'menu' => true,
            ];
        }

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('rentacar_car_remove'),
            'multiple' => $this->modx->lexicon('rentacar_cars_remove'),
            'action' => 'removeCar',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'rentacarCarGetListProcessor';