<?php

namespace Apps\Model\Admin\Demoapp;

use Ffcms\Core\Arch\Model;

class FormDemo extends Model
{
    public $textProp;
    public $checkboxProp;
    public $selectProp;

    private $_factory;

    /**
     * DemoModel constructor. Example factory pattern - pass string inside (in working app should be object instance of ..)
     * @param string $factory
     */
    public function __construct($factory)
    {
        $this->_factory = $factory;
        parent::__construct();
    }

    public function before()
    {
        $this->textProp = (string)$this->_factory;
    }

    public function rules()
    {
        return [
            [['textProp', 'selectProp'], 'required'],
            ['textProp', 'length_min', 20],
            ['checkboxProp', 'checked'],
            ['checkboxProp', 'Apps\Model\Admin\Demoapp\FormDemo::demoCallbackFilter']
        ];
    }

    public function labels()
    {
        return [
            'textProp' => __('Text'),
            'checkboxProp' => __('Checkbox'),
            'selectProp' => __('Select options')
        ];
    }

    /**
     * Demo of local filter according callback. In $objectValue will be $this->checkboxProp value passed by filter system
     * @param string $objectValue
     * @return bool
     */
    public static function demoCallbackFilter($objectValue)
    {
        // just for fun :D Here can be conditions to validate value of filtered object
        return 0 == 'false'; // lazy comparing is sucks! Never use it like this!
    }
}