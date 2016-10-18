<?php

namespace Apps\Controller\Front;

use Apps\ActiveRecord\ForumCategory;
use Apps\ActiveRecord\ForumItem;
use Apps\ActiveRecord\ForumOnline;
use Apps\ActiveRecord\ForumPost;
use Apps\ActiveRecord\ForumThread;
use Apps\Model\Front\Forum\EntityForumSummary;
use Apps\Model\Front\Forum\FormCreateThread;
use Apps\Model\Front\Forum\FormDeleteThread;
use Apps\Model\Front\Forum\FormMassDeleteThreads;
use Apps\Model\Front\Forum\FormPinThread;
use Apps\Model\Front\Forum\FormStatusThread;
use Apps\Model\Front\Forum\FormUpdateThread;
use Apps\Model\Front\Forum\FormMoveThread;
use Extend\Core\Arch\FrontAppController;
use Ffcms\Core\App;
use Ffcms\Core\Exception\ForbiddenException;
use Ffcms\Core\Exception\NotFoundException;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\HTML\SimplePagination;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Helper\Url;
use Ffcms\Core\I18n\Translate;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class Forum. Front controller of forum architecture.
 * @package Apps\Controller\Front
 */
class Forum extends FrontAppController
{
    private $appRoot;
    private $tplDir;

    /**
     * Initialize app path root, local template and localization
     */
    public function before()
    {
        parent::before();
        // define application src root path
        $this->appRoot = realpath(__DIR__ . '/../../../');
        $this->tplDir = $this->appRoot . '/Apps/View/Front/default';
        // load internalization package for current lang
        $langFile = $this->appRoot . '/I18n/Front/' . App::$Request->getLanguage() . '/Forum.php';
        if (App::$Request->getLanguage() !== 'en' && File::exist($langFile)) {
            App::$Translate->append($langFile);
        }
        // add global css link
        App::$Alias->setCustomLibrary('css', '/vendor/phpffcms/ffcms-forum/src/Apps/View/Front/default/asset/css/forum.css');
        // set online marker cookie for guest's
        if (!App::$User->isAuth()) {
            $token = App::$Request->cookies->get('forum_token', null);
            if ($token === null) {
                $token = Str::randomLatinNumeric(mt_rand(32, 128));
                App::$Response->headers->setCookie(new Cookie('forum_token', $token, strtotime('+1 month')));
            }
            ForumOnline::refresh($token);
        } else {
            $user = App::$User->identity();
            ForumOnline::refresh(null, $user->getId());
        }
    }

    /**
     * Add global translation for profile notifications in boot
     */
    public static function boot()
    {
        $appPath = realpath(__DIR__ . './../../../');
        if (App::$Request->getController() === 'Profile') {
            App::$Translate->append($appPath . '/I18n/Front/' . App::$Request->getLanguage() . '/ProfileGlobal.php');
        }
    }

    /**
     * Index page action
     * @return string
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws \Ffcms\Core\Exception\SyntaxException
     */
    public function actionIndex()
    {
        // get all board categories
        $categories = ForumCategory::all();
        $fullTree = null;

        // build category-forum-subforum tree
        foreach ($categories as $category) {
            $fullTree[$category['order_id']] = $category->toArray();
            /** @var $category ForumCategory */
            $forums = $category->getForumTree();
            if ($forums === null || !Obj::isArray($forums)) {
                continue;
            }

            // add sub forums and post_count/thread_count
            foreach($forums as $forum) {
                $fullTree[$category['order_id']]['forums'][$forum['order_id']] = $forum;
                $updateThread = Serialize::decode($forum['updated_thread']);
                if (Obj::isArray($updateThread) && isset($updateThread[App::$Request->getLanguage()])) {
                    $lastThread = ForumThread::find($updateThread[App::$Request->getLanguage()]);
                    $fullTree[$category['order_id']]['forums'][$forum['order_id']]['lastthread'] = [
                        'title' => $lastThread['title'],
                        'id' => $lastThread['id'],
                        'user_id' => $lastThread['updater_id'] < 1 ? $lastThread['creator_id'] : $lastThread['updater_id']
                    ];
                }
                // sort ASC forums
                ksort($fullTree[$category['order_id']]['forums']);
            }
        }

        // sort in ASC by order_id categories
        if (Obj::isArray($fullTree)) {
            ksort($fullTree);
        }

        $configs = $this->getConfigs();
        // initialize forum summary statistic model
        $summaryModel = new EntityForumSummary($configs['cacheSummary']);

        // render output and pass tree inside
        return $this->view->render('forum/index', [
            'tplDir' => $this->tplDir,
            'tree' => $fullTree,
            'summary' => $summaryModel,
            'configs' => $configs
        ], $this->tplDir);
    }

    /**
     * Forum thread and sub-forum listing
     * @param int $id
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws ForbiddenException
     */
    public function actionViewforum($id)
    {
        $page = (int)$this->request->query->get('page', 0);
        $configs = $this->getConfigs();
        $threadPerPage = (int)$configs['threadsPerPage'];
        $forumRecord = ForumItem::find($id);
        if ($forumRecord === null) {
            throw new ForbiddenException(__('Forum with id %id% is not found', ['id' => (int)$id]));
        }

        // get threads in this forum
        $threadQuery = ForumThread::where('forum_id', $id)->where('lang', App::$Request->getLanguage());
        $pagination = new SimplePagination([
            'url' => ['forum/viewforum', $id],
            'page' => $page,
            'step' => $threadPerPage,
            'total' => $threadQuery->count()
        ]);

        // build currently limited treads to result object
        $threadRecords = $threadQuery->skip($page * $threadPerPage)->take($threadPerPage)->orderBy('important', 'DESC')->orderBy('updated_at', 'DESC')->get();

        return $this->view->render('forum/view_forum', [
            'tplDir' => $this->tplDir,
            'forumRecord' => $forumRecord,
            'threadRecords' => $threadRecords,
            'pagination' => $pagination
        ], $this->tplDir);
    }

    /**
     * Create new thread action
     * @param int $forumId
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function actionCreatethread($forumId)
    {
        // check if user is authorized
        if (!App::$User->isAuth() || !App::$User->identity()->getRole()->can('forum/thread')) {
            throw new ForbiddenException(__('You have no permissions to create thread'));
        }

        // try to find forum by id
        $forumRecord = ForumItem::find($forumId);
        if ($forumRecord === null) {
            throw new NotFoundException(__('Forum with id %id% is not found', ['id' => (int)$forumId]));
        }

        // initialize post-operate model and catch form.send
        $model = new FormCreateThread($forumRecord, App::$Request->getLanguage());
        if ($model->send() && $model->validate()) {
            $model->make();
            App::$Session->getFlashBag()->add('success', __('Your thread are successful created!'));
            App::$Response->redirect('forum/viewthread/' . $model->threadId);
        }

        return $this->view->render('forum/create_thread', [
            'forumRecord' => $forumRecord,
            'model' => $model
        ], $this->tplDir);
    }

    /**
     * View exists thread
     * @param int $id
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws NotFoundException
     */
    public function actionViewthread($id)
    {
        // get page offset & configs
        $page = (int)$this->request->query->get('page', 0);
        $configs = $this->getConfigs();
        $postPerPage = (int)$configs['postPerPage'];
        $offset = $page * $postPerPage;
        // try to find thread record in db
        $threadRecord = ForumThread::find($id);
        if ($threadRecord === null) {
            throw new NotFoundException(__('Topic with id %id% is not found', ['id' => (int)$id]));
        }
        // update view count
        $threadRecord->view_count += 1;
        $threadRecord->save();

        // get posts for current thread
        $postQuery = ForumPost::where('thread_id', $id)->where('lang', App::$Request->getLanguage());
        $postCount = $postQuery->count();
        $pagination = new SimplePagination([
            'url' => ['forum/viewthread', $id],
            'page' => $page,
            'step' => $postPerPage,
            'total' => $postCount
        ]);
        $postRecord = $postQuery->skip($offset)->take($postPerPage)->orderBy('created_at', 'ASC')->get();
        // get forum-owner for this thread and parent forum if exists
        $forumRecord = ForumItem::find($threadRecord->forum_id);
        $parentRecord = $forumRecord->findParent();

        // render output view
        return $this->view->render('forum/view_thread', [
            'threadRecord' => $threadRecord,
            'postRecord' => $postRecord,
            'forumRecord' => $forumRecord,
            'parentRecord' => $parentRecord,
            'pagination' => $pagination,
            'page' => $page,
            'offset' => $offset,
            'isLastPage' => (($page+1)*$postPerPage) >= $postCount
        ], $this->tplDir);
    }

    /**
     * Find last post in thread if exists and build redirect
     * @param int $threadId
     * @throws NotFoundException
     */
    public function actionLastpost($threadId)
    {
        // get configs and thread object
        $configs = $this->getConfigs();
        $postPerPage = (int)$configs['postPerPage'];
        $threadRecord = ForumThread::find($threadId);
        if ($threadRecord === null) {
            throw new NotFoundException(__('Topic with id %id% is not found', ['id' => (int)$threadId]));
        }

        // parse last page and redirect url
        $lastPage = (int)(($threadRecord->post_count-1)/$postPerPage);
        $lastPost = $threadRecord->getLastPost();
        if ($lastPost === null) {
            $url = Url::to('forum/viewthread', $threadRecord->id);
        } elseif ($lastPage === 0) {
            $url = Url::to('forum/viewthread', $threadRecord->id, null, ['#' => '#post-' . $lastPost->id]);
        } else {
            $url = Url::to('forum/viewthread', $threadRecord->id, null, ['page' => $lastPage, '#' => '#post-' . $lastPost->id]);
        }

        App::$Response->redirect($url, true);
        return;
    }

    /**
     * Show delete thread form and process submit
     * @param int $id
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function actionDeletethread($id)
    {
        // check user permissions
        if (!App::$User->isAuth() || !App::$User->identity()->getRole()->can('forum/delete')) {
            throw new ForbiddenException(__('You have no permissions to delete thread'));
        }

        // check if thread exists
        $record = ForumThread::find($id);
        if ($record === null) {
            throw new NotFoundException(__('Thread is not found'));
        }

        // build delete model
        $model = new FormDeleteThread($record, App::$Request->getLanguage());
        if ($model->send() && $model->validate()) {
            $forumId = $record->forum_id;
            $model->make();
            App::$Session->getFlashBag()->add('success', __('Thread are successful removed'));
            App::$Response->redirect(Url::to('forum/viewforum', $forumId), true);
        }

        return $this->view->render('forum/delete_thread', [
            'model' => $model
        ], $this->tplDir);
    }

    /**
     * Show update form and process submit
     * @param int $id
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function actionUpdatethread($id)
    {
        // check user permissions
        if (!App::$User->isAuth() || !App::$User->identity()->getRole()->can('forum/edit')) {
            throw new ForbiddenException(__('You have no permissions to edit thread'));
        }

        $record = ForumThread::find($id);
        if ($record === null) {
            throw new NotFoundException(__('Thread is not found'));
        }

        $model = new FormUpdateThread($record, App::$Request->getLanguage());
        if ($model->send() && $model->validate()) {
            $model->make();
            App::$Session->getFlashBag()->add('success', __('Thread are successful updated'));
            App::$Response->redirect(Url::to('forum/viewthread', $model->id), true);
        }

        return $this->view->render('forum/update_thread', [
            'model' => $model,
            'forumRecord' => $record->getForumRelated()
        ], $this->tplDir);
    }

    /**
     * Move thread to new forum
     * @param int $id
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function actionMovethread($id)
    {
        // check user permissions
        if (!App::$User->isAuth() || !App::$User->identity()->getRole()->can('forum/delete')) {
            throw new ForbiddenException(__('You have no permissions to move thread'));
        }

        $record = ForumThread::find($id);
        if ($record === null) {
            throw new NotFoundException(__('Thread is not found'));
        }
        $forum = $record->getForumRelated();

        // initialize move model & process submit
        $model = new FormMoveThread($record, $forum, App::$Request->getLanguage());
        if ($model->send() && $model->validate()) {
            $model->make();
            App::$Session->getFlashBag()->add('success', __('Thread are successful moved to new forum'));
            App::$Response->redirect(Url::to('forum/viewthread', $model->id), true);
        }

        return $this->view->render('forum/move_thread', [
            'model' => $model,
            'forumRecord' => $forum
        ], $this->tplDir);
    }

    /**
     * Pin and unpin thread
     * @param int $id
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function actionStatusthread($id)
    {
        // check user permissions
        if (!App::$User->isAuth() || !App::$User->identity()->getRole()->can('forum/pin')) {
            throw new ForbiddenException(__('You have no permissions to pin thread'));
        }

        // find thread record in db
        $record = ForumThread::find($id);
        if ($record === null) {
            throw new NotFoundException(__('Thread is not found'));
        }
        $forum = $record->getForumRelated();

        // initialize model & process submit action
        $model = new FormStatusThread($record);
        if ($model->send() && $model->validate()) {
            $model->make();
            App::$Session->getFlashBag()->add('success', __('Thread status is changed'));
            App::$Response->redirect(Url::to('forum/viewthread', $id), true);
        }

        return $this->view->render('forum/status_thread', [
            'model' => $model,
            'forum' => $forum
        ], $this->tplDir);
    }

    /**
     * Process mass delete of forum threads
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function actionMassdelete()
    {
        // get thread ids and forum id
        $ids = App::$Request->query->get('selected', null);
        $forumId = App::$Request->query->getInt('forum_id');
        if (!Obj::isArray($ids) || !Arr::onlyNumericValues($ids)) {
            throw new ForbiddenException(__('Bad attributes'));
        }

        // make query & check items count
        $query = ForumThread::where('forum_id', $forumId)->whereIn('id', $ids);
        $count = $query->count();
        if ($count < 1) {
            throw new NotFoundException(__('Thread is not found'));
        }

        // initialize model and process post action
        $model = new FormMassDeleteThreads($query->get(), $count, $forumId);
        if ($model->send()) {
            $model->make();
            App::$Session->getFlashBag()->add('success', __('Threads are successful removed'));
            App::$Response->redirect(Url::to('forum/viewforum', $forumId), true);
        }

        return $this->view->render('forum/massdel_thread', [
            'model' => $model,
            'forumId' => $forumId
        ], $this->tplDir);
    }

    /**
     * Show latest updated threads
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     */
    public function actionStream()
    {
        // get last updated threads as object
        $records = ForumThread::where('lang', App::$Request->getLanguage())->orderBy('updated_at', 'DESC')->take(20)->get();

        // render response
        return $this->view->render('forum/stream', [
            'tplDir' => $this->tplDir,
            'records' => $records
        ], $this->tplDir);
    }

}