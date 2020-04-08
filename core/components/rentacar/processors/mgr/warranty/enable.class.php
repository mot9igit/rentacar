<?php

class rentacarWarrantyEnableProcessor extends modObjectProcessor
{
    public $objectType = 'rentacar_cars_warranty';
    public $classKey = 'rentacar_cars_warranty';
    public $languageTopics = ['rentacar'];
    //public $permission = 'save';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('rentacar_warranty_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var rentacarItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('rentacar_warranty_err_nf'));
            }

            $object->set('active', true);
            $object->save();
        }

        return $this->success();
    }

}

return 'rentacarWarrantyEnableProcessor';
