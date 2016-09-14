<?php


namespace Apps\Controller\Api;


use Apps\ActiveRecord\App as AppModel;
use Apps\ActiveRecord\ForumPost;
use Apps\ActiveRecord\ForumThread;
use Apps\Model\Front\Forum\AddPostCount;
use Extend\Core\Arch\ApiController;
use Ffcms\Core\App;
use Ffcms\Core\Exception\ForbiddenException;
use Ffcms\Core\Exception\JsonException;
use Ffcms\Core\Exception\NotFoundException;
use Ffcms\Core\Helper\Date;
use Ffcms\Core\Helper\Simplify;
use Ffcms\Core\Helper\Type\Str;

class Forum extends ApiController
{
    /**
     * Add user post in database for $threadId
     * @param int $threadId
     * @return string
     * @throws \Ffcms\Core\Exception\ForbiddenException
     * @throws JsonException
     * @throws NotFoundException
     */
    public function actionCreatepost($threadId)
    {
        $this->setJsonHeader();
        // check if current user is authorized
        if (!App::$User->isAuth() || !App::$User->identity()->getRole()->can('forum/post')) {
            throw new JsonException(__('You have no permissions to add forum reply'));
        }

        // get message post param & check length valid
        $message = App::$Security->secureHtml((string)$this->request->request->get('message', null));
        if (Str::likeEmpty($message) || Str::length($message) < 3) {
            throw new JsonException(__('Message is too short'));
        }

        $configs = AppModel::getConfigs('app', 'Forum');
        $delay = (int)$configs['delay'];
        if ($delay < 10) {
            $delay = 10;
        }

        // find post related thread
        /** @var ForumThread $thread */
        $thread = ForumThread::find($threadId);
        if ($thread === null) {
            throw new NotFoundException(__('Thread is not found'));
        }

        // get user object
        $user = App::$User->identity();
        // check last post time
        $lastUserPost = ForumPost::where('user_id', $user->getId())->orderBy('created_at', 'DESC')->first();
        if ($lastUserPost !== null) {
            $postTime = Date::convertToTimestamp($lastUserPost->created_at);
            $diff = time() - $postTime;
            if ($diff < $delay) {
                throw new ForbiddenException(__('You sending messages to fast! Please wait %time%sec', ['time' => ($delay - $diff)]));
            }
        }

        // add new row in post table
        $post = new ForumPost();
        $post->message = $message;
        $post->thread_id = (int)$threadId;
        $post->user_id = $user->getId();
        $post->lang = App::$Request->getLanguage();
        $post->save();

        $updateCounter = new AddPostCount($post, $thread);
        $updateCounter->make();

        // render response
        return json_encode(['status' => 1, 'data' => [
            'id' => $post->id,
            'message' => $post->message,
            'created_at' => Date::humanize($post->created_at),
            'user' => [
                'link' => Simplify::parseUserLink($user->getId()),
                'nick' => Simplify::parseUserNick($user->getId()),
                'avatar' => $user->getProfile()->getAvatarUrl('small'),
                'group' => $user->getRole()->name,
                'created_at' => Date::convertToDatetime($user->created_at, Date::FORMAT_TO_DAY),
                'posts' => (int)$user->getProfile()->forum_post
            ]
        ]]);
    }

    /**
     * Delete forum post api method
     * @param int $id
     * @return string
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function actionDeletepost($id)
    {
        $this->setJsonHeader();
        if (!App::$User->isAuth() || !App::$User->identity()->getRole()->can('forum/delete')) {
            throw new ForbiddenException(__('You have no permissions to delete post'));
        }

        $post = ForumPost::find($id);
        if ($post === null) {
            throw new NotFoundException(__('Post not found'));
        }

        // update thread, forum, parent-forum counters
        /** @var ForumThread $thread */
        $thread = $post->getThread();
        $post->delete();
        $thread->post_count -= 1;
        $thread->save();

        $forum = $thread->getForumRelated();
        $forum->post_count -= 1;
        $forum->save();
        $forum->updateLastInfo();

        $parent = $forum->findParent();
        if ($parent !== null) {
            $parent->post_count -= 1;
            $parent->save();
        }

        return json_encode(['status' => 1, 'message' => 'post sucessful removed']);
    }

    /**
     * Edit post api method
     * @param int $id
     * @return string
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function actionEditpost($id)
    {
        // set headers & check if user have permissions to edit posts
        $this->setJsonHeader();
        if (!App::$User->isAuth() || !App::$User->identity()->getRole()->can('forum/edit')) {
            throw new ForbiddenException(__('You have no permissions to delete post'));
        }

        // find post object
        $post = ForumPost::find($id);
        if ($post === null) {
            throw new NotFoundException(__('Post not found'));
        }

        // get new message for this post
        $message = (string)App::$Request->request->get('message', null);
        $message = App::$Security->secureHtml($message);
        if (Str::length($message) < 10) {
            throw new ForbiddenException(__('Message is too short'));
        }

        $post->message = $message;
        $post->save();

        return json_encode(['status' => 1, 'data' => [
            'message' => $message
        ]]);
    }
}