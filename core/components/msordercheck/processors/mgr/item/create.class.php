<?php

class msOrderCheckItemCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'msOrderCheckItem';
    public $classKey = 'msOrderCheckItem';
    public $languageTopics = ['msordercheck'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('msordercheck_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('msordercheck_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'msOrderCheckItemCreateProcessor';