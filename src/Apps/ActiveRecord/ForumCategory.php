<?php

namespace Apps\ActiveRecord;


use Ffcms\Core\Arch\ActiveModel;
use Ffcms\Core\App as AppMain;
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
    const CACHE_FULLTABLE_NAME = 'activerecord.forumcategory.all';

    public static function all($columns = ['*'])
    {
        if (AppMain::$Memory->get(static::CACHE_FULLTABLE_NAME) !== null) {
            return AppMain::$Memory->get(static::CACHE_FULLTABLE_NAME);
        }
        $records = parent::all($columns);
        AppMain::$Memory->set(static::CACHE_FULLTABLE_NAME, $records);
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