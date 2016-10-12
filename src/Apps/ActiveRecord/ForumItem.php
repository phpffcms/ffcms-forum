<?php

namespace Apps\ActiveRecord;


use Ffcms\Core\App as AppMain;
use Ffcms\Core\Arch\ActiveModel;
use Ffcms\Core\Cache\MemoryObject;
use Ffcms\Core\Helper\Date;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;

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
                $tree[$forum->depend_id]['depend'][$forum->order_id] = $forum->toArray();
                ksort($tree[$forum->depend_id]['depend']);
            } else {
                $tree[$forum->id] = $forum->toArray();
            }
        }
        if (Obj::isArray($tree)) {
            ksort($tree);
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
     * @return ForumThread|null
     */
    public function getLastThread($lang = null)
    {
        if ($lang === null) {
            $lang = AppMain::$Request->getLanguage();
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
        return ForumCategory::find($this->category_id);
    }

    /**
     * Build full forum tree
     * @return array|null
     */
    public function buildFullTree()
    {
        $records = self::all();
        $tree = null;
        foreach ($records as $forum) {
            // look's like sub-forum item
            if ((int)$forum->depend_id !== 0) {
                $tree[$forum->depend_id]['depend'][$forum->order_id] = $forum->toArray();
                ksort($tree[$forum->depend_id]['depend']);
            } else {
                $tree[$forum->id] = $forum->toArray();
            }
        }
        if (Obj::isArray($tree)) {
            ksort($tree);
        }

        return $tree;
    }

    /**
     * Update last post info for current forum. Some "magic" inside, keep calm!
     * @param string|null $lang
     */
    public function updateLastInfo($lang = null)
    {
        if ($lang === null) {
            $lang = AppMain::$Request->getLanguage();
        }
        // get last thread info for this forum
        $lastThread = $this->getLastThread($lang);
        $updaterId = Serialize::decode($this->updater_id);
        $updatedThread = Serialize::decode($this->updated_thread);

        if ($lastThread !== null) {
            $updaterId = Arr::merge($updaterId, [$lang => $lastThread->updater_id]);
            $updatedThread = Arr::merge($updatedThread, [$lang => $lastThread->id]);
        } else {
            if ($updaterId !== false) {
                unset($updaterId[$lang]);
            }
            if ($updatedThread !== false) {
                unset($updatedThread[$lang]);
            }
        }

        $this->updater_id = Serialize::encode($updaterId);
        $this->updated_thread = Serialize::encode($updatedThread);

        $this->save();

        // update parent lastpost forum if exist
        $parent = $this->findParent();
        if ($parent !== null) {
            $parentUpdaterId = Serialize::decode($parent->updater_id);
            $parentUpdatedThread = Serialize::decode($parent->updated_thread);

            $lastParentThread = $parent->getLastThread($lang);
            // check if child thread is newest them parent
            if (Date::convertToTimestamp($lastThread->updated_at) >= Date::convertToTimestamp($lastParentThread->updated_at)) {
                $lastParentThread = $lastThread;
            }
            if ($lastParentThread !== null) {
                $parentUpdaterId = Arr::merge($parentUpdaterId, [$lang => $lastParentThread->updater_id]);
                $parentUpdatedThread = Arr::merge($parentUpdatedThread, [$lang => $lastParentThread->id]);
            } else {
                if ($parentUpdaterId !== false) {
                    unset($parentUpdaterId[$lang]);
                }

                if ($parentUpdatedThread !== false) {
                    unset($parentUpdatedThread[$lang]);
                }
            }

            $parent->updater_id = Serialize::encode($parentUpdaterId);
            $parent->updated_thread = Serialize::encode($parentUpdatedThread);

            $parent->save();
        }
    }



}