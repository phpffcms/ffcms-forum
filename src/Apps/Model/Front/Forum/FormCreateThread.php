<?php


namespace Apps\Model\Front\Forum;


use Apps\ActiveRecord\ForumItem;
use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Exception\ForbiddenException;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Interfaces\iUser;

class FormCreateThread extends Model
{
    public $title;
    public $message;

    public $threadId = 0;

    /** @var ForumItem */
    private $_record;
    private $_lang;
    /** @var iUser */
    private $_user;

    /**
     * FormCreateThread constructor. Pass forum active record object inside.
     * @param ForumItem $forumRecord
     * @param string|null $lang
     */
    public function __construct(ForumItem $forumRecord, $lang = null)
    {
        $this->_record = $forumRecord;
        $this->_lang = $lang;
        parent::__construct(true);
    }

    /**
     * Store user identity in class property
     * @throws ForbiddenException
     */
    public function before()
    {
        if (!App::$User->isAuth()) {
            throw new ForbiddenException(__('Auth required'));
        }
        $this->_user = App::$User->identity();
        if ($this->_lang === null) {
            $this->_lang = App::$Request->getLanguage();
        }
    }

    /**
     * Validation rules
     * @return array
     */
    public function rules()
    {
        return [
            [['title', 'message'], 'required'],
            ['title', 'length_min', 3],
            ['title', 'length_max', 100]
        ];
    }

    public function labels()
    {
        return [
            'title' => __('Title'),
            'message' => __('Message')
        ];
    }

    /**
     * Save post in database and set new threadId
     */
    public function make()
    {
        // save new thread in forum_thread table
        $record = new ForumThread();
        $record->title = App::$Security->strip_tags($this->title);
        $record->message = App::$Security->secureHtml($this->message);
        $record->creator_id = $this->_user->getId();
        $record->is_important = false;
        $record->forum_id = $this->_record->id;
        $record->lang = $this->_lang;
        $record->save();

        // set model new tread ID
        $this->threadId = $record->id;

        $updateCounters = new AddThreadCount($record, $this->_lang);
        $updateCounters->make();
    }
}