<?php

namespace Apps\Model\Front\Forum;


use Apps\ActiveRecord\ForumItem;
use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Helper\Serialize;

/**
 * Class FormMoveThread. Forum thread move business logic model
 * @package Apps\Model\Front\Forum
 */
class FormMoveThread extends Model
{
    public $id;
    public $title;
    public $from;
    public $to;

    private $_thread;
    private $_forum;
    private $_lang;
    private $_allowedIds;

    /**
     * FormMoveThread constructor. Pass objects inside the model
     * @param ForumThread $thread
     * @param ForumItem $forum
     * @param string|null $lang
     */
    public function __construct(ForumThread $thread, ForumItem $forum, $lang = null)
    {
        $this->_thread = $thread;
        $this->_forum = $forum;
        $this->_lang = $lang;
        parent::__construct(true);
    }

    /**
     * Set model property values from passed objects
     */
    public function before()
    {
        if ($this->_lang === null) {
            $this->_lang = App::$Request->getLanguage();
        }

        $this->id = $this->_thread->id;
        $this->title = $this->_thread->title;
        $this->from = $this->_forum->getLocaled('name');
        $this->_allowedIds = $this->getAllForumsIds();
    }

    /**
     * Validation rules
     * @return array
     */
    public function rules()
    {
        return [
            ['to', 'required'],
            ['to', 'int'],
            ['to', 'in', $this->_allowedIds]
        ];
    }

    /**
     * Forum display labels
     * @return array
     */
    public function labels()
    {
        return [
            'title' => __('Thread title'),
            'from' => __('From forum'),
            'to' => __('To forum')
        ];
    }

    /**
     * Move thread item to new forum, update counters & info's
     */
    public function make()
    {
        // update thread forum_id depend
        $this->_thread->forum_id = (int)$this->to;
        $this->_thread->save();
        // update current forum item
        $this->_forum->post_count -= $this->_thread->post_count;
        $this->_forum->thread_count -= 1;
        $this->_forum->save();
        $this->_forum->updateLastInfo($this->_lang);
        // try to update parent if exists
        $parent = $this->_forum->findParent();
        if ($parent !== null) {
            $parent->post_count -= $this->_thread->post_count;
            $parent->thread_count -= 1;
            $parent->save();
        }

        // update acceptor forum
        $acceptor = ForumItem::find($this->to);
        $acceptor->post_count += $this->_thread->post_count;
        $acceptor->thread_count += 1;
        $acceptor->save();
        $acceptor->updateLastInfo();
    }

    /**
     * Get forum tree as array id=>name
     * @return array
     */
    public function getForumsTree()
    {
        $tree = $this->_forum->buildFullTree();
        $options = [];
        foreach ($tree as $item) {
            if ($item['id'] === $this->_forum->id) {
                if (isset($item['depend'])) {
                    foreach ($item['depend'] as $depend) {
                        $options[$depend['id']] = '-- ' . Serialize::getDecodeLocale($depend['name']);
                    }
                }
                continue;
            }
            $options[$item['id']] = '- ' . Serialize::getDecodeLocale($item['name']);
            if (isset($item['depend']) && $item['depend']['id'] !== $this->_forum->id) {
                foreach ($item['depend'] as $depend) {
                    $options[$depend['id']] = '-- ' . Serialize::getDecodeLocale($depend['name']);
                }
            }
        }

        return $options;
    }

    /**
     * Build all forum ids to array
     * @return array
     */
    private function getAllForumsIds()
    {
        $ids = [];
        $records = ForumItem::where('id', '!=', $this->id)->get(['id']);
        foreach ($records as $item) {
            $ids[] = (int)$item->id;
        }

        return $ids;
    }

}