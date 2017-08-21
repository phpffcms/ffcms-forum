<?php

namespace Apps\ActiveRecord;


use Ffcms\Core\Arch\ActiveModel;

/**
 * Class ForumPost. Active record for db table forum_posts
 * @package Apps\ActiveRecord
 * @property int $id
 * @property string $message
 * @property int $thread_id
 * @property int user_id
 * @property string $lang
 * @property int $update_time
 * @property string $created_at
 * @property string $updated_at
 */
class ForumPost extends ActiveModel
{
    protected $casts = [
        'id' => 'integer',
        'message' => 'string',
        'thread_id' => 'integer',
        'user_id' => 'integer',
        'lang' => 'string',
        'update_time' => 'integer'
    ];

    /**
     * Get forum thread object relation for current post
     * @return ForumThread|null
     */
    public function getThread()
    {
        return ForumThread::find($this->thread_id);
    }
}