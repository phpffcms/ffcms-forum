<?php

namespace Apps\Model\Front\Forum;


use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;

/**
 * Class FormUpdateThread. Thread edit business logic model
 * @package Apps\Model\Front\Forum
 */
class FormUpdateThread extends Model
{
    public $id;
    public $title;
    public $message;

    /** @var ForumThread */
    private $_thread;
    /** @var string */
    private $_lang;

    /**
     * FormUpdateThread constructor. Pass thread record object inside
     * @param ForumThread $thread
     * @param null $lang
     */
    public function __construct(ForumThread $thread, $lang = null)
    {
        $this->_thread = $thread;
        $this->_lang = $lang;
        parent::__construct(true);
    }

    /**
     * Set public properties from passed object
     */
    public function before()
    {
        if ($this->_lang === null) {
            $this->_lang = App::$Request->getLanguage();
        }

        $this->id = $this->_thread->id;
        $this->title = $this->_thread->title;
        $this->message = $this->_thread->message;
    }

    /**
     * Declare validation rules
     * @return array
     */
    public function rules()
    {
        return [
            ['title', 'required'],
            ['message', 'required'],
            ['message', 'length_min', 10]
        ];
    }

    /**
     * Set labels for display form
     * @return array
     */
    public function labels()
    {
        return [
            'title' => __('Title'),
            'message' => __('Message')
        ];
    }

    /**
     * Declare property filtering types
     * @return array
     */
    public function types()
    {
        return [
            'title' => 'text',
            'message' => 'html'
        ];
    }

    /**
     * Update db row from user data
     */
    public function make()
    {
        $this->_thread->title = $this->title;
        $this->_thread->message = $this->message;
        $this->_thread->save();
    }


}