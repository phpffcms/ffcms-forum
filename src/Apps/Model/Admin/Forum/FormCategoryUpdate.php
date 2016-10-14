<?php

namespace Apps\Model\Admin\Forum;


use Apps\ActiveRecord\ForumCategory;
use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

class FormCategoryUpdate extends Model
{
    public $name;
    public $orderId;

    /** @var ForumCategory */
    private $_record;

    /**
     * FormCategoryUpdate constructor. Pass category object inside
     * @param ForumCategory $record
     */
    public function __construct(ForumCategory $record)
    {
        $this->_record = $record;
        parent::__construct(true);
    }

    /**
     * Set default properties from object data
     */
    public function before()
    {
        $this->name = $this->_record->name;
        $this->orderId = $this->_record->order_id;
    }

    /**
     * Validation rules
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'orderId'], 'used'],
            ['name.' . App::$Request->getLanguage(), 'required'],
            ['orderId', '\Apps\Model\Admin\Forum\FormCategoryUpdate::checkOrder', $this->_record->order_id]
        ];
    }

    /**
     * Form display labels
     * @return array
     */
    public function labels()
    {
        return [
            'name' => __('Name'),
            'orderId' => __('Sort order')
        ];
    }

    /**
     * Save new data in object
     */
    public function make()
    {
        if ($this->orderId === null || !Obj::isLikeInt($this->orderId)) {
            $this->orderId = mt_rand(100, 100000);
        }
        $this->_record->name = $this->name;
        $this->_record->order_id = $this->orderId;
        $this->_record->save();
    }

    /**
     * Check order_id is valid and not exists
     * @param int|null $newOrder
     * @param int|null $defaultOrder
     * @return bool
     */
    public static function checkOrder($newOrder = null, $defaultOrder = null)
    {
        // do not check if not changed or empty
        if (Str::likeEmpty($newOrder) || (int)$defaultOrder === (int)$newOrder) {
            return true;
        }
        return ForumCategory::where('order_id', $newOrder)->count() === 0;
    }
}