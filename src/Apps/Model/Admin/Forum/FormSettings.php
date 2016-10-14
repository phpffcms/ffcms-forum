<?php

namespace Apps\Model\Admin\Forum;


use Ffcms\Core\Arch\Model;

/**
 * Class FormSettings. Business logic model for forum settings action
 * @package Apps\Model\Admin\Forum
 */
class FormSettings extends Model
{
    public $threadsPerPage;
    public $postPerPage;
    public $delay;
    public $cacheSummary;
    public $metaTitle;
    public $metaDescription;
    public $metaKeywords;

    /** @var array|null */
    private $_configs;

    /**
     * FormSettings constructor. Pass configs array inside model
     * @param array|null $configs
     */
    public function __construct(array $configs = null)
    {
        $this->_configs = $configs;
        parent::__construct(true);
    }

    /**
     * Set default properties from passed configs array
     */
    public function before()
    {
        if ($this->_configs === null) {
            return;
        }

        foreach ($this->_configs as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * Validation rules
     * @return array
     */
    public function rules()
    {
        return [
            [['threadsPerPage', 'postPerPage', 'delay', 'cacheSummary'], 'required'],
            [['metaTitle', 'metaDescription', 'metaKeywords'], 'used'],
            ['metaTitle.' . \App::$Request->getLanguage(), 'required'],
            [['threadsPerPage', 'postPerPage', 'delay', 'cacheSummary'], 'int']
        ];
    }

    /**
     * Set form display labels
     * @return array
     */
    public function labels()
    {
        return [
            'threadsPerPage' => __('Threads per page'),
            'postPerPage' => __('Posts per page'),
            'delay' => __('Delay'),
            'cacheSummary' => __('Cache summary'),
            'metaTitle' => __('Forum title'),
            'metaDescription' => __('Forum description'),
            'metaKeywords' => __('Forum keywords')
        ];
    }
}