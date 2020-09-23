<?php

class rentacarCarRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'rentacar_cars';
    public $classKey = 'rentacar_cars';
    public $languageTopics = ['rentacar'];
    //public $permission = 'remove';


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
            return $this->failure($this->modx->lexicon('rentacar_car_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var rentacarItem $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('rentacar_car_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'rentacarCarRemoveProcessor';