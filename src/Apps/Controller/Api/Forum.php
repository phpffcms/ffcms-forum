<?php


namespace Apps\Controller\Api;


use Apps\ActiveRecord\ForumItem;
use Apps\ActiveRecord\ForumPost;
use Apps\ActiveRecord\ForumThread;
use Apps\Model\Front\Forum\AddPostCount;
use Extend\Core\Arch\ApiController;
use Ffcms\Core\App;
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
     * @throws JsonException
     * @throws NotFoundException
     */
    public function actionCreatepost($threadId)
    {
        $this->setJsonHeader();
        // check if current user is authorized
        if (!App::$User->isAuth()) {
            throw new JsonException(__('You have no permissions to add forum reply'));
        }

        // get message post param & check length valid
        $message = App::$Security->secureHtml((string)$this->request->request->get('message', null));
        if (Str::likeEmpty($message) || Str::length($message) < 3) {
            throw new JsonException(__('Message is too short'));
        }

        // find post related thread
        /** @var ForumThread $thread */
        $thread = ForumThread::find($threadId);
        if ($thread === null) {
            throw new NotFoundException(__('Thread is not found'));
        }

        // get user object
        $user = App::$User->identity();

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
}