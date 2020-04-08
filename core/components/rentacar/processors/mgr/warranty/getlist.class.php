<?php

class rentacarWarrantyGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'rentacar_cars_warranty';
    public $classKey = 'rentacar_cars_warranty';
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
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
            ]);
        }

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
            'title' => $this->modx->lexicon('rentacar_warranty_update'),
            //'multiple' => $this->modx->lexicon('rentacar_options_update'),
            'action' => 'updateWarranty',
            'button' => true,
            'menu' => true,
        ];

        if (!$array['active']) {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('rentacar_warranty_enable'),
                'multiple' => $this->modx->lexicon('rentacar_warrantys_enable'),
                'action' => 'enableWarranty',
                'button' => true,
                'menu' => true,
            ];
        } else {
            $array['actions'][] = [
                'cls' => '',
                'icon' => 'icon icon-power-off action-gray',
                'title' => $this->modx->lexicon('rentacar_warranty_disable'),
                'multiple' => $this->modx->lexicon('rentacar_warrantys_disable'),
                'action' => 'disableWarranty',
                'button' => true,
                'menu' => true,
            ];
        }

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('rentacar_warranty_remove'),
            'multiple' => $this->modx->lexicon('rentacar_warrantys_remove'),
            'action' => 'removeWarranty',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }

}

return 'rentacarWarrantyGetListProcessor';