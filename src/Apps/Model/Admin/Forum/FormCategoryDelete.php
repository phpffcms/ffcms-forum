<?php


namespace Apps\Model\Admin\Forum;


use Apps\ActiveRecord\ForumCategory;
use Apps\ActiveRecord\ForumPost;
use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;

/**
 * Class FormCategoryDelete. Business logic of forum category remove action
 * @package Apps\Model\Admin\Forum
 */
class FormCategoryDelete extends Model
{
    public $name;
    public $moveTo;

    /** @var ForumCategory */
    private $_category;

    /**
     * FormCategoryDelete constructor. Pass category object inside model
     * @param ForumCategory $category
     */
    public function __construct(ForumCategory $category)
    {
        $this->_category = $category;
        parent::__construct(true);
    }

    /**
     * Set category name as model attribute
     */
    public function before()
    {
        $this->name = $this->_category->name[App::$Request->getLanguage()];
    }

    /**
     * Validation rules
     * @return array
     */
    public function rules()
    {
        return [
            ['moveTo', 'required'],
            ['moveTo', 'int'],
            ['moveTo', '\Apps\Model\Admin\Forum\FormCategoryDelete::checkCategoryId', $this->_forum->id]
        ];
    }

    public function labels()
    {
        return [
            'moveTo' => __('Move to'),
            'name' => __('Category name')
        ];
    }

    /**
     * Submit delete category - remove category, move/delete forums,threads
     */
    public function make()
    {
        $forums = [];
        foreach ($this->_category->getForums()->get() as $forum) {
            $forums[] = $forum->id;
            if ((int)$this->moveTo === 0) {
                $forum->delete();
            } else {
                $forum->category_id = (int)$this->moveTo;
                $forum->save();
            }
        }

        // delete all threads and posts
        if ((int)$this->moveTo === 0) {
            $threadsIds = [];
            $threads = ForumThread::whereIn('forum_id', $forums);
            foreach ($threads->get() as $thread) {
                $threadsIds[] = $thread->id;
            }
            $threads->delete();
            ForumPost::whereIn('thread_id', $threadsIds)->delete();
        }

        $this->_category->delete();
    }

    /**
     * Get available categories to move as array id->name
     * @return \Generator
     */
    public function getCategories()
    {
        yield 0 => '--- ' . __('delete all content') . ' ---';
        $records = ForumCategory::where('id', '!=', $this->_category->id)->get();
        foreach ($records as $record) {
            yield $record->id => $record->getLocaled('name');
        }
    }

    /**
     * Check new category id is valid
     * @param int|null $newId
     * @param int|null $oldId
     * @return bool
     */
    public static function checkCategoryId($newId = null, $oldId = null)
    {
        if ((int)$newId === 0) {
            return true;
        }
        if ((int)$newId === (int)$oldId) {
            return false;
        }

        return ForumCategory::where('id', $newId)->count() === 1;
    }

}