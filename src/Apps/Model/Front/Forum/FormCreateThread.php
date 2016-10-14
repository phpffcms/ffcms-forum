<?php


namespace Apps\Model\Front\Forum;


use Apps\ActiveRecord\ForumItem;
use Apps\ActiveRecord\ForumThread;
use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Exception\ForbiddenException;
use Ffcms\Core\Helper\Date;
use Ffcms\Core\Interfaces\iUser;

/**
 * Class FormCreateThread. New thread create form model
 * @package Apps\Model\Front\Forum
 */
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
            ['title', 'length_max', 100],
            ['message', 'length_max', 10000],
            ['threadId', '\Apps\Model\Front\Forum\FormCreateThread::checkDelay']
        ];
    }

    /**
     * Form display labels
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
     * Save post in database and set new threadId
     */
    public function make()
    {
        // save new thread in forum_thread table
        $record = new ForumThread();
        $record->title = App::$Security->strip_tags($this->title);
        $record->message = App::$Security->secureHtml($this->message);
        $record->creator_id = $this->_user->getId();
        $record->forum_id = $this->_record->id;
        $record->lang = $this->_lang;
        $record->save();

        // set model new tread ID
        $this->threadId = $record->id;

        $updateCounters = new AddThreadCount($record, $this->_lang);
        $updateCounters->make();
    }

    /**
     * Check if user can create new thread based on time delay between 2 threads
     * @param mixed $object
     * @return bool
     */
    public static function checkDelay($object)
    {
        // check if user is auth
        if (!App::$User->isAuth()) {
            return false;
        }
        $user = App::$User->identity();
        // get delay time from configs
        $delay = (int)\Apps\ActiveRecord\App::getConfig('app', 'Forum', 'delay');
        if ($delay < 10) {
            $delay = 10;
        }

        // try to get latest thread for this user
        $lastUserThrad = ForumThread::where('creator_id', $user->getId())->orderBy('created_at', 'DESC')->first();
        if ($lastUserThrad === null) {
            return true;
        }

        // calc last user thread time to timestamp and check diff
        $threadTime = Date::convertToTimestamp($lastUserThrad->created_at);
        $diff = time() - $threadTime;
        if ($diff < $delay) {
            App::$Session->getFlashBag()->add('error', __('You are creating threads too fast. Please wait %time%sec', ['time' => ($delay-$diff)]));
            return false;
        }

        return true;
    }
}