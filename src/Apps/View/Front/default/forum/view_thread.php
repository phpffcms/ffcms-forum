<?php

use Ffcms\Core\Helper\Date;
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
    $breads[Url::to('forum/viewforum', $parentRecord->id)] = App::$Translate->getLocaleText($parentRecord->name);
}

$breads[Url::to('forum/viewforum', $forumRecord->id)] = App::$Translate->getLocaleText($forumRecord->name);

$breads[] = __('Topic: %title%', ['title' => $threadRecord->title]);

$this->breadcrumbs = $breads;

?>

<?= $pagination->display(['class' => 'pagination pagination-sm']) ?>

<div class="panel topic-panel">
    <div class="panel-heading topic-head">
        <?= (bool)$threadRecord->important ? '<i class="glyphicon glyphicon-star"></i>' : null ?>
        <?= (bool)$threadRecord->closed ? '<i class="glyphicon glyphicon-remove-circle"></i>' : null ?>
        <?= $threadRecord->title ?>
    </div>
    <div class="panel-body topic-body">
        <?php if ($page < 1): ?>
            <div class="row post-row clearfix" id="post-0">
                <div class="author col-md-2 col-sm-3 col-xs-12">
                    <?php $user = \App::$User->identity($threadRecord->creator_id) ?>
                    <div class="author-name">
                        <div class="h4"><?= Simplify::parseUserLink($threadRecord->creator_id) ?></div>
                    </div>
                    <div class="author-title">
                        <div class="h5">
                            <span style="color: <?= $user->getRole()->color ?>"><?= $user->getRole()->name ?></span>
                        </div>
                    </div>
                    <div class="author-avatar"><img src="<?= $user->getProfile()->getAvatarUrl('small') ?>" alt="User avatar"></div>
                    <div class="author-registered"><?= __('Joined: %date%', ['date' => Date::convertToDatetime($user->created_at, 'm.Y')]) ?></div>
                    <div class="author-posts"><?= __('Posts: %post%', ['post' => (int)$user->getProfile()->forum_post]) ?></div>
                    <div class="author-pm">
                        <?php if (\App::$User->isAuth() && $user->getId() !== \App::$User->identity()->getId()): ?>
                            <a href="<?= Url::to('profile/messages', null, null, ['newdialog' => $user->getId()]) ?>"><i class="glyphicon glyphicon-envelope"></i></a>
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

                    <div class="post-content post-message clearfix">
                        <?= $threadRecord->message ?>
                    </div>

                    <div class="post-footer clearfix">
                        <div class="pull-left" style="padding-left: 10px;">
                            <a href="#fanswer" class="label label-primary make-quote" id="quote-post-0">
                                <i class="glyphicon glyphicon-forward"></i>
                            </a>
                        </div>
                        <!-- Report/Edit/Delete/Quote Post-->
                        <div class="post-menu pull-right">
                            <?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/delete')): ?>
                                <?= Url::link(['forum/deletethread', $threadRecord->id], __('Delete'), ['class' => 'label label-danger']) ?>
                            <?php endif; ?>
                            <?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/edit')): ?>
                                <?= Url::link(['forum/updatethread', $threadRecord->id], __('Edit'), ['class' => 'label label-warning']) ?>
                            <?php endif; ?>
                            <?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/move')): ?>
                                <?= Url::link(['forum/movethread', $threadRecord->id], __('Move'), ['class' => 'label label-info']) ?>
                            <?php endif; ?>
                            <?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/pin')): ?>
                                <?= Url::link(['forum/statusthread', $threadRecord->id], ((bool)$threadRecord->important ? __('Unpin') : __('Pin')), ['class' => 'label label-primary']) ?>
                            <?php endif; ?>
                            <?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/close')): ?>
                                <?= Url::link(['forum/statusthread', $threadRecord->id], ((bool)$threadRecord->closed ? __('Open') : __('Close')), ['class' => 'label label-default']) ?>
                            <?php endif; ?>
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
                    <div class="author-title">
                        <div class="h5"><span style="color: <?= $user->getRole()->color ?>"><?= $user->getRole()->name ?></span></div>
                    </div>
                    <div class="author-avatar"><img src="<?= $user->getProfile()->getAvatarUrl('small') ?>" alt="User avatar"></div>
                    <div class="author-registered"><?= __('Joined: %date%', ['date' => Date::convertToDatetime($user->created_at, Date::FORMAT_TO_DAY)]) ?></div>
                    <div class="author-posts"><?= __('Posts: %post%', ['post' => (int)$user->getProfile()->forum_post]) ?></div>
                    <div class="author-pm">
                        <?php if (\App::$User->isAuth() && $user->getId() !== \App::$User->identity()->getId()): ?>
                            <a href="<?= Url::to('profile/messages', null, null, ['newdialog' => $user->getId()]) ?>"><i class="glyphicon glyphicon-envelope"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="post-box col-md-10 col-sm-9 col-xs-12">
                    <div class="post-meta clearfix">
                        <div class="pull-left">
                            <!-- Creation date / Date modified -->
                            <span class="text-info">
                                <?= Date::humanize($post->created_at) ?>
                            </span>
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

                    <div class="post-content post-message clearfix" id="post-message-<?= $post->id ?>">
                        <?= $post->message ?>
                    </div>
                    <?php if ((string)$post->created_at !== (string)$post->updated_at): ?>
                        <div class="label label-warning" style="margin: 5px;">
                            <?= __('Been edited: %date%', ['date' => Date::humanize($post->updated_at)]) ?>
                        </div>
                    <?php endif ?>

                    <div class="post-footer clearfix">
                        <!-- Report/Edit/Delete/Quote Post-->
                        <div class="post-menu">
                            <div class="pull-left" style="padding-left: 10px;">
                                <?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/post')): ?>
                                    <a href="#fanswer" class="label label-default make-quote" id="quote-post-<?= $post->id ?>">
                                        <i class="glyphicon glyphicon-forward"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="pull-right">
                                <?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/delete')): ?>
                                    <a href="javascript:void(0)" class="label label-danger delete-post-trigger" id="delete-post-<?= $post->id ?>"><?= __('Delete') ?></a>
                                <?php endif; ?>
                                <?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/edit')): ?>
                                    <a href="javascript:void(0)" class="label label-warning edit-post-trigger" id="edit-post-<?= $post->id ?>"><?= __('Edit') ?></a>
                                <?php endif; ?>
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
<?php if (!\App::$User->isAuth() || !\App::$User->identity()->getRole()->can('forum/post')): ?>
    <p class="alert alert-warning"><?= __('You should register or authorize on website to add reply') ?></p>
<?php elseif ((bool)$threadRecord->closed): ?>
    <p class="alert alert-warning"><?= __('This thread is closed from new answers') ?></p>
<?php else: ?>
    <div class="row" style="padding-top: 10px;">
        <div class="col-md-10 col-sm-9 col-xs-12 col-md-offset-2 col-sm-offset-3">
            <?= Ffcms\Widgets\Ckeditor\Ckeditor::widget(['targetClass' => 'wysiwyg', 'config' => 'config-small', 'jsConfig' => ['height' => '150']]) ?>
            <textarea class="form-control wysiwyg" id="fanswer"></textarea>
            <button class="btn btn-success" id="send-push"><i class="glyphicon glyphicon-fire"></i> <?= __('Reply') ?></button>
        </div>
    </div>
<?php endif; ?>

<!-- new post template for ajax add -->
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
                <div class="post-message"></div>
            </div>

            <div class="post-footer clearfix">
                <div class="post-menu" style="padding-left: 10px;">
                    <a href="#fanswer" class="label label-default make-quote" id="quote-post-new-id">
                        <i class="glyphicon glyphicon-forward"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="post-edit-modal" tabindex="-1" role="dialog" aria-labelledby="post-edit-modal-title">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="post-edit-modal-title"><?= __('Edit post') ?></h4>
            </div>
            <div class="modal-body">
                <textarea class="wysiwyg" id="fedit"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= __('Cancel') ?></button>
                <button type="button" class="btn btn-primary" id="save-edit-post"><?= __('Save') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // post add/delete vars
        var threadId = <?= $threadRecord->id ?>;
        var isLastPage = <?= (int)$isLastPage ?>;
        var tpl = $('#post-template').clone().removeClass('hidden');

        // post edit vars
        var msg;
        var postId = 0;

        // add new post
        $('#send-push').click(function () {
            var msg = $('#fanswer').val();
            if (msg.length < 10) {
                alert('<?= __('Message is too short') ?>');
                return;
            }

            $.post(script_url + '/api/forum/createpost/' + threadId + '?lang=' + script_lang, {message: msg}, function (resp) {
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
                answer.find('#quote-post-new-id').attr('id', 'quote-post-' + resp.data.id);
                answer.find('#post-user-name').html(resp.data.user.link).removeAttr('id');
                answer.find('#post-user-group').text(resp.data.user.group).removeAttr('id');
                answer.find('#post-user-joindate').text('<?= __('Joined') ?>: ' + resp.data.user.created_at).removeAttr('id');
                answer.find('#post-user-avatar').attr('src', resp.data.user.avatar).removeAttr('id');
                answer.find('#post-user-posts').text('<?= __('Posts') ?>: ' + resp.data.user.posts).removeAttr('id');
                answer.find('#post-created-at').text(resp.data.created_at).removeAttr('id');
                answer.find('.post-message').html(resp.data.message);

                $('#new-post-object').append(answer.html());

                CKEDITOR.instances.fanswer.setData('');
            }, 'json');
        });

        // quote exist post
        $(document).on("click", ".make-quote", function () {
            var postId = $(this).attr('id').replace('quote-post-', '');
            var quoteMsg = $('#post-' + postId).find('.post-message').html();
            quoteMsg = '<blockquote>' + quoteMsg + '</blockquote><p>&nbsp;</p>';
            CKEDITOR.instances.fanswer.insertHtml(quoteMsg);
        });

        // delete post via ajax
        $('.delete-post-trigger').click(function () {
            if (!confirm('<?= __('Are you sure to delete this post?') ?>')) {
                return false;
            }
            var postId = $(this).attr('id').replace('delete-post-', '');
            $.getJSON(script_url + '/api/forum/deletepost/' + postId + '?lang=' + script_lang, function (resp) {
                if (resp.status !== 1) {
                    alert(resp.message);
                    return;
                }

                $('#post-' + postId).fadeOut(400, function () {
                    $(this).remove();
                })
            });
        });

        // edit post - show edit form on pop-up modal
        $('.edit-post-trigger').click(function () {
            postId = $(this).attr('id').replace('edit-post-', '');
            msg = $('#post-message-' + postId);
            CKEDITOR.instances.fedit.setData(msg.html());
            $('#post-edit-modal').modal('show');
        });

        // save edited post data via ajax
        $('#save-edit-post').on('click', function () {
            var editedMsg = $('#fedit').val();
            if (editedMsg === null || editedMsg.length < 10) {
                return;
            }
            $.post(script_url + '/api/forum/editpost/' + postId + '?lang=' + script_lang, {message: editedMsg}, function (resp) {
                if (resp.status !== 1) {
                    alert(resp.message);
                    return null;
                }

                // update displayed msg
                msg.html(resp.data.message);
                msg.fadeOut(400).fadeIn(400).fadeOut(400).fadeIn(400);
                $('#post-edit-modal').modal('hide');
            });
        });
    });
</script>