<?php

namespace Apps\Model\Front\Forum;


use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\Arch\Model;

/**
 * Class FormPinThread. Business logic of thread status changes - pin & close
 * @package Apps\Model\Front\Forum
 */
class FormStatusThread extends Model
{
    public $id;
    public $title;
    public $pinned;
    public $closed;

    private $_thread;

    /**
     * FormPinThread constructor. Pass thread object inside
     * @param ForumThread $thread
     */
    public function __construct(ForumThread $thread)
    {
        $this->_thread = $thread;
        parent::__construct(true);
    }

    /**
     * Validation rules
     * @return array
     */
    public function rules()
    {
        return [
            [['pinned', 'closed'], 'required'],
            ['pinned', 'in', ['0', '1']],
            ['closed', 'in', ['0', '1']]
        ];
    }

    /**
     * Form display labels
     * @return array
     */
    public function labels()
    {
        return [
            'title' => __('Thread title'),
            'pinned' => __('Pinned'),
            'closed' => __('Closed')
        ];
    }

    /**
     * Build default model properties from passed object
     */
    public function before()
    {
        $this->id = $this->_thread->id;
        $this->title = $this->_thread->title;
        $this->pinned = (bool)$this->_thread->important;
        $this->closed = (bool)$this->_thread->closed;
    }

    /**
     * Update thread important/close status on submit
     */
    public function make()
    {
        $this->_thread->important = (bool)$this->pinned;
        $this->_thread->closed = (bool)$this->closed;
        $this->_thread->save();
    }


}