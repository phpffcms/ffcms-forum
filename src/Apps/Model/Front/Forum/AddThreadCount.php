<?php

namespace Apps\Model\Front\Forum;


use Apps\ActiveRecord\ForumItem;
use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Arr;

/**
 * Class UpdateThreadCount. Special "count&update" class for forum structure. Required ForumThread new object to pass inside.
 * @package Apps\Model\Front\Forum
 */
class AddThreadCount extends Model
{
    public $threadId;
    public $userId;

    /** @var ForumThread */
    private $_thread;
    /** @var ForumItem */
    private $_forum;
    /** @var string */
    private $_lang;

    /**
     * UpdateThreadCount constructor. Pass thread record and lang inside.
     * @param ForumThread $thread
     * @param null $lang
     */
    public function __construct(ForumThread $thread, $lang = null)
    {
        $this->_thread = $thread;
        $this->_lang = $lang;
        if ($this->_lang === null) {
            $this->_lang = App::$Request->getLanguage();
        }

        parent::__construct(false);
    }

    /**
     * Declare public thread id, user id and find forum object.
     */
    public function before()
    {
        $this->threadId = $this->_thread->id;
        $this->userId = $this->_thread->creator_id;
        $this->_forum = $this->_thread->getForumRelated();
    }

    /**
     * Update current and parent forum information about new thread
     */
    public function make()
    {
        if ($this->_forum === null) {
            return;
        }

        // update forum thread_count
        $this->_forum->thread_count += 1;
        $this->_forum->save();

        $this->_forum->updateLastInfo($this->_lang);

        // update parent forum record if exist
        $parentRecord = $this->_forum->findParent();
        if ($parentRecord !== null) {
            $parentRecord->thread_count += 1;
            $parentRecord->save();
        }
    }

}