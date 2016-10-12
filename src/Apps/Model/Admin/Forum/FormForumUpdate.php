<?php

namespace Apps\Model\Admin\Forum;


use Apps\ActiveRecord\ForumCategory;
use Apps\ActiveRecord\ForumItem;
use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class FormForumUpdate. Business logic of create/update forum items.
 * @package Apps\Model\Admin\Forum
 */
class FormForumUpdate extends Model
{
    public $name;
    public $snippet;
    public $orderId;
    public $categoryId;
    public $dependId;

    /** @var ForumItem */
    private $_forum;
    private $_parent;

    /**
     * FormForumUpdate constructor. Pass forum item record inside
     * @param ForumItem $record
     * @param int $parent
     */
    public function __construct(ForumItem $record, $parent = 0)
    {
        $this->_forum = $record;
        $this->_parent = $parent;
        parent::__construct(true);
    }

    /**
     * Set default model attributes from passed record
     */
    public function before()
    {
        $this->name = Serialize::decode($this->_forum->name);
        $this->snippet = Serialize::decode($this->_forum->snippet);
        $this->orderId = $this->_forum->order_id;
        $this->categoryId = $this->_forum->category_id;
        $this->dependId = $this->_forum->depend_id;
        if ($this->dependId === null && $this->_parent > 0) {
            $this->dependId = (int)$this->_parent;
        }
    }

    /**
     * Validation rules for attributes
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'snippet', 'dependId'], 'used'],
            ['name.' . App::$Request->getLanguage(), 'required'],
            [['orderId', 'categoryId'], 'required'],
            [['orderId', 'categoryId'], 'int'],
            ['orderId', '\Apps\Model\Admin\Forum\FormForumUpdate::checkOrder', $this->_forum->order_id],
            ['categoryId', '\Apps\Model\Admin\Forum\FormForumUpdate::checkCategory']
        ];
    }

    /**
     * Form display labels
     * @return array
     */
    public function labels()
    {
        return [
            'name' => __('Title'),
            'snippet' => __('Snippet'),
            'orderId' => __('Sort order'),
            'categoryId' => __('Category'),
            'dependId' => __('Parent forum')
        ];
    }

    /**
     * Get id->category data as array
     * @return \Generator
     */
    public function getIdCategoryArray()
    {
        foreach (ForumCategory::all() as $category) {
            yield $category->id => Serialize::getDecodeLocale($category->name);
        }
    }

    /**
     * Get id->forum data as array
     * @return \Generator
     */
    public function getParentForumsArray()
    {
        $query = ForumCategory::all();
        yield 0 => '-';
        foreach ($query as $cat) {
            $forums = $cat->getForums()->get();
            yield mt_rand(100, 100000) => '=== [' . $cat->getLocaled('name') . '] ===';
            foreach ($forums as $forum) {
                if ($forum->id === $this->_forum->id) {
                    continue;
                }
                yield $forum->id => ' > ' . $forum->getLocaled('name');
            }
        }
    }

    /**
     * Check if forum order_id is unique
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
        return ForumItem::where('order_id', $newOrder)->count() === 0;
    }

    /**
     * Check if category with id $categoryId is exists
     * @param int $categoryId
     * @return bool
     */
    public static function checkCategory($categoryId = null)
    {
        return ForumCategory::where('id', $categoryId)->count() === 1;
    }

    /**
     * Save data to database
     */
    public function make()
    {
        // check if order id is defined or set random numeric
        if ($this->orderId === null || !Obj::isLikeInt($this->orderId)) {
            $this->orderId = mt_rand(100, 1000000);
        }

        // check if parent forum is not defined and set 0
        if ($this->dependId === null || !Obj::isLikeInt($this->dependId)) {
            $this->dependId = 0;
        }

        // save properties to database
        $this->_forum->name = Serialize::encode($this->name);
        $this->_forum->snippet = Serialize::encode($this->snippet);
        $this->_forum->order_id = $this->orderId;
        $this->_forum->category_id = $this->categoryId;
        $this->_forum->depend_id = $this->dependId;
        $this->_forum->save();
    }
}