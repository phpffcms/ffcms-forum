<?php

use Ffcms\Core\Helper\Date;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Simplify;
use Ffcms\Core\Helper\Url;

/** @var \Apps\ActiveRecord\ForumThread $threadRecord */
/** @var \Apps\ActiveRecord\ForumPost $postRecord */
/** @var \Apps\ActiveRecord\ForumItem|null $parentRecord */
/** @var \Apps\ActiveRecord\ForumItem $forumRecord */
/** @var \Ffcms\Core\Helper\HTML\SimplePagination $pagination */
/** @var int $page */
/** @var int $offset */
/** @var bool $isLastPage */

$this->title = $threadRecord->title;
if ($page > 0) {
    $this->title .= ' - ' . ($page+1);
}
$breads = [];
$breads[Url::to('/')] = __('Home');
$breads[Url::to('forum/index')] = __('Forum index');

if ($parentRecord !== null) {
    $breads[Url::to('forum/viewforum', $parentRecord->id)] = Serialize::getDecodeLocale($parentRecord->name);
}

$breads[Url::to('forum/viewforum', $forumRecord->id)] = Serialize::getDecodeLocale($forumRecord->name);

$breads[] = __('Topic: %title%', ['title' => $threadRecord->title]);

$this->breadcrumbs = $breads;

?>

<?= $pagination->display(['class' => 'pagination pagination-sm']) ?>

<div class="panel topic-panel">
    <div class="panel-heading topic-head">
        <?= $threadRecord->title ?>
    </div>
    <div class="panel-body topic-body">
        <?php if ($page < 1): ?>
        <div class="row post-row clearfix">
            <div class="author col-md-2 col-sm-3 col-xs-12">
                <?php $user = \App::$User->identity($threadRecord->creator_id) ?>
                <div class="author-name">
                    <div class="h4"><?= Simplify::parseUserLink($threadRecord->creator_id) ?></div>
                </div>
                <div class="author-title"><div class="h5"><?= $user->getRole()->name ?></div></div>
                <div class="author-avatar"><img src="<?= $user->getProfile()->getAvatarUrl('small') ?>" alt="User avatar"></div>
                <div class="author-registered"><?= __('Joined: %date%', ['date' => Date::convertToDatetime($user->created_at, 'm.Y')]) ?></div>
                <div class="author-posts"><?= __('Posts: %post%', ['post' => (int)$user->getProfile()->forum_post]) ?></div>
                <div class="author-pm">
                    <?php if (\App::$User->isAuth() && $user->getId() !== \App::$User->identity()->getId()): ?>
                        <a href="<?= Url::to('profile/messages', null, null, ['newdialog' => $user->getId()]) ?>"><i class="fa fa-envelope"></i></a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="post-box col-md-10 col-sm-9 col-xs-12">
                <div class="post-meta clearfix">
                    <div class="pull-left">
                        <!-- Creation date / Date modified -->
                        <span class="text-info"><?= __('Created: %date%', ['date' => Date::humanize($threadRecord->created_at)]) ?></span>
                    </div>
                    <!-- Post number -->
                    <div class="pull-right">
                        <strong>#<?= $offset ?></strong>
                    </div>
                </div>

                <div class="post-content clearfix" id="pid137">
                    <?= $threadRecord->message ?>
                </div>

                <div class="post-footer clearfix">
                    <!-- Report/Edit/Delete/Quote Post-->
                    <div class="post-menu pull-right">
                        <?= Url::link(['forum/deletethread', $threadRecord->id], __('Delete'), ['class' => 'label label-danger']) ?>
                        <?= Url::link(['forum/updatethread', $threadRecord->id], __('Edit'), ['class' => 'label label-warning']) ?>
                        <?= Url::link(['forum/movethread', $threadRecord->id], __('Move'), ['class' => 'label label-info']) ?>
                    </div> <!-- end post-menu -->
                </div> <!-- end footer -->

            </div>
        </div>
        <?php $offset++; ?>
        <?php endif; ?>
        <?php foreach ($postRecord as $post): ?>
            <div class="row post-row clearfix" id="post-<?= $post->id ?>">
                <div class="author col-md-2 col-sm-3 col-xs-12">
                    <?php $user = \App::$User->identity($post->user_id) ?>
                    <div class="author-name">
                        <div class="h4"><?= Simplify::parseUserLink($user->getId()) ?></div>
                    </div>
                    <div class="author-title"><div class="h5"><?= $user->getRole()->name ?></div></div>
                    <div class="author-avatar"><img src="<?= $user->getProfile()->getAvatarUrl('small') ?>" alt="User avatar"></div>
                    <div class="author-registered"><?= __('Joined: %date%', ['date' => Date::convertToDatetime($user->created_at, Date::FORMAT_TO_DAY)]) ?></div>
                    <div class="author-posts"><?= __('Posts: %post%', ['post' => (int)$user->getProfile()->forum_post]) ?></div>
                    <div class="author-pm">
                        <?php if (\App::$User->isAuth() && $user->getId() !== \App::$User->identity()->getId()): ?>
                            <a href="<?= Url::to('profile/messages', null, null, ['newdialog' => $user->getId()]) ?>"><i class="fa fa-envelope"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="post-box col-md-10 col-sm-9 col-xs-12">
                    <div class="post-meta clearfix">
                        <div class="pull-left">
                            <!-- Creation date / Date modified -->
                            <span class="text-info"><?= Date::humanize($post->created_at) ?></span>
                        </div>
                        <!-- Post number -->
                        <div class="pull-right">
                            <strong>
                                <?php if($page > 0): ?>
                                    <?= Url::link(['forum/viewthread', $threadRecord->id, null, ['page' => $page, '#' => '#post-' . $post->id]], '#' . $offset) ?>
                                <?php else: ?>
                                    <?= Url::link(['forum/viewthread', $threadRecord->id, null, ['#' => '#post-' . $post->id]], '#' . $offset) ?>
                                <?php endif; ?>
                            </strong>
                        </div>
                    </div>

                    <div class="post-content clearfix" id="pid137">
                        <div class="pull-right"><a href="#fanswer" class="label label-default make-quote" id="quote-post-<?= $post->id ?>"><i class="fa fa-quote-right fa-lg"></i></a></div>
                        <div class="post-message"><?= $post->message ?></div>
                    </div>

                    <div class="post-footer clearfix">
                        <!-- Report/Edit/Delete/Quote Post-->
                        <div class="post-menu">
                            <div class="pull-left" style="padding-left: 10px;">

                            </div>
                            <div class="pull-right">
                                <?= Url::link(['forum/deletepost', $post->id], __('Delete'), ['class' => 'label label-danger']) ?>
                                <?= Url::link(['forum/updatepost', $post->id], __('Edit'), ['class' => 'label label-warning']) ?>
                                <?= Url::link(['forum/movepost', $post->id], __('Move'), ['class' => 'label label-info']) ?>
                            </div>
                        </div> <!-- end post-menu -->
                    </div> <!-- end footer -->
                </div>
            </div>
        <?php $offset++ ?>
        <?php endforeach; ?>
        <div id="new-post-object"></div>
    </div>
</div>

<?= $pagination->display(['class' => 'pagination pagination-sm']) ?>

<div class="row" style="padding-top: 10px;">
    <div class="col-md-10 col-sm-9 col-xs-12 col-md-offset-2 col-sm-offset-3">
        <!-- todo: fix smiley in ckeditor, replace to emoji utf8 -->
    <?= Ffcms\Widgets\Ckeditor\Ckeditor::widget(['targetClass' => 'wysiwyg', 'config' => 'config-small', 'jsConfig' => ['height' => '150']]) ?>
        <textarea class="form-control wysiwyg" id="fanswer"></textarea>
        <button class="btn btn-success" id="send-push"><i class="fa fa-reply"></i> <?= __('Reply') ?></button>
    </div>
</div>

<div id="post-template" class="hidden">
    <div class="row post-row clearfix" id="post-new-id">
        <div class="author col-md-2 col-sm-3 col-xs-12">
            <div class="author-name">
                <div class="h4" id="post-user-name">User</div>
            </div>
            <div class="author-title"><div class="h5" id="post-user-group">Group</div></div>
            <div class="author-avatar"><img src="" alt="User avatar" id="post-user-avatar"></div>
            <div class="author-registered" id="post-user-joindate">Joined: 0.0.0000</div>
            <div class="author-posts" id="post-user-posts">Posts: 0</div>
        </div>

        <div class="post-box col-md-10 col-sm-9 col-xs-12">
            <div class="post-meta clearfix">
                <div class="pull-left">
                    <!-- Creation date / Date modified -->
                    <span class="text-info" id="post-created-at"></span>
                </div>
                <!-- Post number -->
                <div class="pull-right">
                    <strong>#new</strong>
                </div>
            </div>

            <div class="post-content clearfix">
                <div class="pull-right"><a href="#fanswer" class="label label-default make-quote" id="quote-post-new-id"><i class="fa fa-quote-right fa-lg"></i></a></div>
                <div class="post-message"></div>
            </div>
        </div>
    </div>
</div>

<script>
    window.jQ.push(function() {
        $(function(){
            var threadId = <?= $threadRecord->id ?>;
            var isLastPage = <?= (int)$isLastPage ?>;
            var tpl = $('#post-template').clone().removeClass('hidden');

            $('#send-push').click(function(){
                var msg = $('#fanswer').val();
                if (msg.length < 10) {
                    alert('<?= __('Message is too short') ?>');
                    return;
                }

                $.post(script_url+'/api/forum/createpost/'+threadId+'?lang='+script_lang, {message: msg}, function(resp){
                    if (resp.status !== 1) {
                        alert(resp.message);
                        return;
                    }

                    if (isLastPage == 0) {
                        window.location.replace('<?= Url::to('forum/lastpost', $threadRecord->id) ?>');
                        return;
                    }

                    var answer = tpl.clone();
                    answer.find('#post-new-id').attr('id', 'post-' + resp.data.id);
                    answer.find('#quote-post-new-id').attr('id', 'quote-post-'+resp.data.id);
                    answer.find('#post-user-name').html(resp.data.user.link);
                    answer.find('#post-user-group').text(resp.data.user.group);
                    answer.find('#post-user-joindate').text('<?= __('Joined') ?>: '+resp.data.user.created_at);
                    answer.find('#post-user-avatar').attr('src', resp.data.user.avatar);
                    answer.find('#post-user-posts').text('<?= __('Posts') ?>: '+resp.data.user.posts);
                    answer.find('#post-created-at').text(resp.data.created_at);
                    answer.find('.post-message').html(resp.data.message);

                    $('#new-post-object').append(answer.html());

                    CKEDITOR.instances.fanswer.setData('');
                }, 'json');
            });

            $(document).on("click", ".make-quote", function() {
                var postId = $(this).attr('id').replace('quote-post-', '');
                var quoteMsg = $('#post-'+postId).find('.post-message').html();
                quoteMsg = '<blockquote>' + quoteMsg + '</blockquote><p>&nbsp;</p>';
                CKEDITOR.instances.fanswer.insertHtml(quoteMsg);
            });
        });
    });
</script>