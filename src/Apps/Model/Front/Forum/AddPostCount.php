<?php


namespace Apps\Model\Front\Forum;


use Apps\ActiveRecord\ForumItem;
use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\App;
use Apps\ActiveRecord\ForumPost;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Interfaces\iUser;

/**
 * Class UpdatePostCount. Update counters in thread, forum and parent forum.
 * @package Apps\Model\Front\Forum
 */
class AddPostCount extends Model
{

    /** @var ForumPost */
    private $_post;
    /** @var ForumThread */
    private $_thread;
    /** @var string */
    private $_lang;
    /** @var iUser */
    private $_user;

    /**
     * UpdatePostCount constructor. Pass post active record, thread object and lang inside
     * @param ForumPost $post
     * @param ForumThread|null $thread
     * @param null $lang
     */
    public function __construct(ForumPost $post, $thread = null, $lang = null)
    {
        $this->_post = $post;
        $this->_thread = $thread;
        $this->_lang = $lang;
        parent::__construct(false);
    }

    /**
     * Get lang & thread obj if not defined
     * @throws SyntaxException
     */
    public function before()
    {
        // set language if not defined
        if ($this->_lang === null) {
            $this->_lang = App::$Request->getLanguage();
        }

        // try to get thread object if not defined
        if (!$this->_thread instanceof ForumThread) {
            $thread = $this->_post->getThread();
            if ($thread === null) {
                throw new SyntaxException('Thread not found');
            }
            $this->_thread = $thread;
        }

        $this->_user = App::$User->identity();
    }

    /**
     * Update post counters for thread, forum, parent forum
     */
    public function make()
    {
        // update thread info
        $this->_thread->post_count += 1;
        $this->_thread->updater_id = $this->_user->getId();
        $this->_thread->save();

        // update forum info by lastpost in thread relation
        /** @var ForumItem $forum */
        $forum = $this->_thread->getForumRelated();
        $forum->post_count += 1;
        $updaterId = Serialize::decode($forum->updater_id);
        $updatedThread = Serialize::decode($forum->updated_thread);
        if ($updaterId === false) {
            $updaterId = [];
        }
        if ($updatedThread === false) {
            $updatedThread = [];
        }

        $updaterId = Arr::merge($updaterId, [$this->_lang => $this->_user->getId()]);
        $updatedThread = Arr::merge($updatedThread, [$this->_lang => (int)$this->_thread->id]);

        $forum->updater_id = Serialize::encode($updaterId);
        $forum->updated_thread = Serialize::encode($updatedThread);

        $forum->save();
        // update parent forum if exists
        $parent = $forum->findParent();
        if ($parent !== null) {
            $parent->post_count += 1;
            $parentUpdaterId = Serialize::decode($parent->updater_id);
            $parentUpdatedThread = Serialize::decode($parent->updated_thread);
            if ($parentUpdaterId === false) {
                $parentUpdaterId = [];
            }
            if ($parentUpdatedThread === false) {
                $parentUpdatedThread = [];
            }

            $parentUpdaterId = Arr::merge($parentUpdaterId, [$this->_lang => $this->_user->getId()]);
            $parentUpdatedThread = Arr::merge($parentUpdatedThread, [$this->_lang => (int)$this->_thread->id]);
            $parent->updater_id = Serialize::encode($parentUpdaterId);
            $parent->updated_thread = Serialize::encode($parentUpdatedThread);
            $parent->save();
        }
    }
}