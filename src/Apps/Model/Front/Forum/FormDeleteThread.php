<?php

namespace Apps\Model\Front\Forum;


use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;

class FormDeleteThread extends Model
{
    public $id;
    public $title;
    public $forum;
    public $postCount = 0;

    private $_thread;
    private $_lang;

    /**
     * FormDeleteThread constructor. Pass db object inside
     * @param ForumThread $thread
     */
    public function __construct(ForumThread $thread, $lang = null)
    {
        $this->_thread = $thread;
        $this->_lang = $lang;
        parent::__construct(true);
    }

    /**
     * Set public title & forum name
     */
    public function before()
    {
        $this->id = $this->_thread->id;
        $this->title = $this->_thread->title;
        $this->postCount = $this->_thread->post_count;
        $forum = $this->_thread->getForumRelated();
        $this->forum = $forum->getLocaled('name');
        if ($this->_lang === null) {
            $this->_lang = App::$Request->getLanguage();
        }
    }

    public function labels()
    {
        return [
            'title' => __('Thread title'),
            'forum' => __('Forum name'),
            'postCount' => __('Post count')
        ];
    }

    /**
     * Delete thread from db & update counters
     */
    public function make()
    {
        // get related forum for this thread to update it after delete thread & posts
        $forum = $this->_thread->getForumRelated();

        // delete posts, removed count stored in $this->postCount
        $this->_thread->getPosts()->delete();
        // delete thread row
        $this->_thread->delete();

        if ($forum === null) {
            return;
        }

        $forum->updateLastInfo($this->_lang);

        // update forum counters & last post info
        if ($this->postCount > 0) {
            $forum->post_count -= $this->postCount;
        }
        $forum->thread_count -= 1;
        $forum->save();

        // try to update parent if exists
        $parent = $forum->findParent();
        if ($parent !== null) {
            if ($this->postCount > 0) {
                $parent->post_count -= $this->postCount;
            }
            $parent->thread_count -= 1;
            $parent->save();
        }
    }


}