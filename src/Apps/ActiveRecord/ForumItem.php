<?php

namespace Apps\ActiveRecord;


use Ffcms\Core\App;
use Ffcms\Core\Arch\ActiveModel;
use Ffcms\Core\Cache\MemoryObject;

/**
 * Class ForumItem. Active record model for db table forum_items
 * @package Apps\ActiveRecord
 * @property int $id
 * @property string $name
 * @property string $snippet
 * @property int $order_id
 * @property int $category_id
 * @property int $depend_id
 * @property int $thread_count
 * @property int $post_count
 * @property int $updater_id
 * @property int $updated_thread
 * @property string $created_at
 * @property string $updated_at
 */
class ForumItem extends ActiveModel
{
    /**
     * Get full table data as object
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|mixed|static[]
     */
    public static function all($columns = ['*'])
    {
        $cacheName = 'activerecord.forumitems.all.' . implode('.', $columns);
        $records = MemoryObject::instance()->get($cacheName);
        if ($records === null) {
            $records = parent::all($columns);
            MemoryObject::instance()->set($cacheName, $records);
        }

        return $records;
    }

    /**
     * Build full forum array tree for category_id
     * @param int $categoryId
     * @return null|array
     */
    public static function getTreeArray($categoryId)
    {
        $records = self::all();
        $tree = null;
        foreach ($records as $forum) {
            // work only with current category
            if ($forum->category_id !== $categoryId) {
                continue;
            }

            // look's like sub-forum item
            if ((int)$forum->depend_id !== 0) {
                $tree[$forum->depend_id]['depend'][] = $forum->toArray();
            } else {
                $tree[$forum->id] = $forum->toArray();
            }
        }
        return $tree;
    }

    /**
     * Get depended forums for current forum_id
     * @return object
     */
    public function getDependItems()
    {
        return self::where('depend_id', $this->id)->get();
    }

    /**
     * Get last thread object for current forum_id
     * @param string|null $lang
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getLastThread($lang = null)
    {
        if ($lang === null) {
            $lang = App::$Request->getLanguage();
        }
        return ForumThread::where('forum_id', $this->id)->where('lang', $lang)->orderBy('updated_at', 'DESC')->first();
    }

    /**
     * Try to find parent forum object of current forum
     * @return ForumItem|null
     */
    public function findParent()
    {
        return self::find($this->depend_id);
    }

    /**
     * Get forum category belongs to
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getCategory()
    {
        return $this->belongsTo('Apps\ActiveRecord\ForumCategory');
    }



}