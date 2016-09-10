<?php

namespace Apps\ActiveRecord;


use Ffcms\Core\Arch\ActiveModel;

/**
 * Class ForumThread. Active record model for table forum_threads
 * @package Apps\ActiveRecord
 * @property int $id
 * @property string $title
 * @property string $message
 * @property int $creator_id
 * @property int $updater_id
 * @property bool $is_important
 * @property int $forum_id
 * @property string $lang
 * @property int $post_count
 * @property int $view_count
 * @property string $created_at
 * @property string $updated_at
 */
class ForumThread extends ActiveModel
{

    /**
     * Get thread post count
     * @return int
     */
    public function getPostsCount()
    {
        return ForumPost::where('thread_id', $this->id)->count();
    }

    /**
     * Get latest post for thread
     * @return ForumPost|null
     */
    public function getLastPost()
    {
        return ForumPost::where('thread_id', $this->id)->orderBy('created_at', 'DESC')->first();
    }

    /**
     * Get related forum object for current thread
     * @return ActiveModel|null
     */
    public function getForumRelated()
    {
        return ForumItem::find($this->forum_id);
    }
}