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
        // prepare forum thread lastpost info
        $threadUpd = Serialize::decode($this->_forum->updated_thread);
        $updaterUpd = Serialize::decode($this->_forum->updater_id);
        if ($threadUpd === false) {
            $threadUpd = [];
        }
        if ($updaterUpd === false) {
            $updaterUpd = [];
        }
        $threadUpd = Arr::merge($threadUpd, [$this->_lang => $this->threadId]);
        $updaterUpd = Arr::merge($updaterUpd, [$this->_lang => $this->userId]);

        // update forum thread_count
        $this->_forum->thread_count += 1;
        $this->_forum->updated_thread = Serialize::encode($threadUpd);
        $this->_forum->updater_id = Serialize::encode($updaterUpd);
        $this->_forum->save();

        // update parent forum record if exist depend of language locale (see serialized 2 columns)
        $parentRecord = $this->_forum->findParent();
        if ($parentRecord !== null) {
            $parentRecord->thread_count += 1;
            $parentThreadUpd = Serialize::decode($parentRecord->updated_thread);
            $parentUpdaterUpd = Serialize::decode($parentRecord->updater_id);
            if ($parentThreadUpd === false) {
                $parentThreadUpd = [];
            }
            if ($parentUpdaterUpd === false) {
                $parentUpdaterUpd = [];
            }
            $parentThreadUpd = Arr::merge($parentThreadUpd, [$this->_lang => $this->threadId]);
            $parentUpdaterUpd = Arr::merge($parentUpdaterUpd, [$this->_lang, $this->userId]);

            $parentRecord->updated_thread = Serialize::encode($parentThreadUpd);
            $parentRecord->updater_id = Serialize::encode($parentUpdaterUpd);
            $parentRecord->save();
        }
    }

}