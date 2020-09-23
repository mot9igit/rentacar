<?php

class rentacarCarCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'rentacar_cars';
    public $classKey = 'rentacar_cars';
    public $languageTopics = ['rentacar'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('rentacar_option_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('rentacar_option_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'rentacarCarCreateProcessor';