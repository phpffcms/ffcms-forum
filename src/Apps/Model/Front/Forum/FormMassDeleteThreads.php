<?php


namespace Apps\Model\Front\Forum;


use Apps\ActiveRecord\ForumItem;
use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\Arch\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class FormMassDeleteThreads. Business logic model for mast delete threads action
 * @package Apps\Model\Front\Forum
 */
class FormMassDeleteThreads extends Model
{
    public $data;
    public $postCount;
    public $threadCount;

    private $_threads;
    private $_forumId;

    /**
     * FormMassDeleteThreads constructor. Pass thread collection, count, forumId inside.
     * @param Collection $threads
     * @param $threadCount
     * @param $forumId
     */
    public function __construct(Collection $threads, $threadCount, $forumId)
    {
        $this->_threads = $threads;
        $this->_forumId = $forumId;
        $this->threadCount = $threadCount;
        parent::__construct(true);
    }

    /**
     * Build public display data
     */
    public function before()
    {
        // prepare display data from object record
        foreach ($this->_threads as $thread) {
            /** @var ForumThread $thread */
            $this->data[] = [
                'id' => $thread->id,
                'title' => $thread->title,
                'posts' => $thread->post_count,
                'user_id' => $thread->creator_id,
                'date' => $thread->created_at
            ];

            $this->threadCount += 1;
            $this->postCount += $thread->post_count;
        }
    }

    /**
     * Delete threads and update forum counters & last info
     */
    public function make()
    {
        foreach ($this->_threads as $thread) {
            $thread->delete();
        }
        $forum = ForumItem::find($this->_forumId);
        $forum->thread_count -= $this->threadCount;
        $forum->post_count -= $this->postCount;
        $forum->save();
        $parent = $forum->findParent();
        if ($parent !== null) {
            $parent->thread_count -= $this->threadCount;
            $parent->post_count -= $this->postCount;
            $parent->save();
        }

        $forum->updateLastInfo();
    }

}