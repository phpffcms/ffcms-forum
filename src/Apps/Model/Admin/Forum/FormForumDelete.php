<?php

namespace Apps\Model\Admin\Forum;


use Apps\ActiveRecord\ForumItem;
use Apps\ActiveRecord\ForumPost;
use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\Arch\Model;

/**
 * Class FormForumDelete. Business logic for forum delete action
 * @package Apps\Model\Admin\Forum
 */
class FormForumDelete extends Model
{
    public $name;
    public $category;
    public $threadCount;
    public $postCount;
    public $forumsCount;

    public $moveTo;

    /** @var ForumItem */
    private $_forum;

    /**
     * FormForumDelete constructor. Pass forum object inside
     * @param ForumItem $forum
     */
    public function __construct(ForumItem $forum)
    {
        $this->_forum = $forum;
        parent::__construct(true);
    }

    /**
     * Set default model attributes from forum object
     */
    public function before()
    {
        $this->name = $this->_forum->getLocaled('name');
        $this->category = $this->_forum->getCategory()->getLocaled('name');
        $this->threadCount = $this->_forum->thread_count;
        $this->postCount = $this->_forum->post_count;
        $this->forumsCount = ForumItem::where('depend_id', $this->_forum->id)->count();
        if ($this->moveTo === null) {
            $this->moveTo = 0;
        }
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
            ['moveTo', '\Apps\Model\Admin\Forum\FormForumDelete::checkForumId', $this->_forum->id]
        ];
    }

    public function labels()
    {
        return [
            'moveTo' => __('Move to')
        ];
    }

    /**
     * Remove forum item & move/delete threads and posts inside it
     */
    public function make()
    {
        $ids = [];
        $ids[] = $this->_forum->id;
        foreach ($this->_forum->getDependItems() as $forum) {
            $ids[] = $forum->id;
            $forum->delete();
        }
        $this->_forum->delete();

        if ((int)$this->moveTo > 0) { // move threads to new acceptor forum
            ForumThread::whereIn('forum_id', $ids)->update(['forum_id' => (int)$this->moveTo]);
            $acceptor = ForumItem::find($this->moveTo);
            $acceptor->thread_count += $this->threadCount;
            $acceptor->post_count += $this->postCount;
            $acceptor->updateLastInfo();
        } else { // remove all threads
            $threads = ForumThread::whereIn('forum_id', $ids)->get();
            $threadIds = [];
            foreach ($threads as $thread) {
                $threadIds[] = $thread->id;
                $thread->delete();
            }
            ForumPost::whereIn('thread_id', $threadIds)->delete();
        }
    }

    /**
     * Check if target move to forum is exists
     * @param int $forumId
     * @param int $currentId
     * @return bool
     */
    public static function checkForumId($forumId, $currentId)
    {
        if ((int)$forumId === 0) {
            return true;
        }
        if ((int)$currentId === (int)$forumId) {
            return false;
        }

        return ForumItem::where('id', $forumId)->count() === 1;
    }

    /**
     * Get forums id->name list
     * @return \Generator
     */
    public function getForums()
    {
        yield 0 => '--- ' . __('delete all content') . ' ---';
        $records = ForumItem::where('id', '!=', $this->_forum->id)->where('depend_id', '!=', $this->_forum->id)->get();
        foreach ($records as $record) {
            yield $record->id => $record->getLocaled('name');
        }
    }

}