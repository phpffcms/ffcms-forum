<?php

namespace Apps\ActiveRecord;


use Ffcms\Core\Arch\ActiveModel;
use Ffcms\Core\App as AppMain;
use Ffcms\Core\Cache\MemoryObject;

/**
 * Class ForumCategory. Active record model for table forum_categories
 * @package Apps\ActiveRecord
 * @property int $id
 * @property string $name
 * @property int $order_id
 * @property string $created_at
 * @property string $updated_at
 */
class ForumCategory extends ActiveModel
{

    protected $casts = [
        'name' => 'serialize',
        'order_id' => 'integer'
    ];

    /**
     * Get all rows
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|mixed|static[]
     */
    public static function all($columns = ['*'])
    {
        $cacheName = 'activerecord.forumcategories.all.' . implode('.', $columns);
        $records = MemoryObject::instance()->get($cacheName);
        if ($records === null) {
            $records = parent::all($columns);
            MemoryObject::instance()->set($cacheName, $records);
        }

        return $records;
    }

    /**
     * Get related forums to this category as object relation one-to-many
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getForums()
    {
        return $this->hasMany('Apps\ActiveRecord\ForumItem', 'category_id');
    }

    /**
     * @return array|null
     */
    public function getForumTree()
    {
        return ForumItem::getTreeArray($this->id);
    }
}