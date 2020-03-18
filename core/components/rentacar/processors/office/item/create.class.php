<?php

class rentacarOfficeItemCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'rentacarItem';
    public $classKey = 'rentacarItem';
    public $languageTopics = ['rentacar'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('rentacar_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('rentacar_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'rentacarOfficeItemCreateProcessor';