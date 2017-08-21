<?php

use Ffcms\Core\Helper\Date;
use Ffcms\Core\Helper\Simplify;
use Ffcms\Core\Helper\Text;
use Ffcms\Core\Helper\Url;

/** @var string $tplDir */
/** @var \Apps\ActiveRecord\ForumItem $forumRecord */
/** @var \Apps\ActiveRecord\ForumThread $threadRecords */
/** @var \Ffcms\Core\Helper\HTML\SimplePagination $pagination */

$this->title = \App::$Translate->getLocaleText($forumRecord['name']);

$bread = [];
$bread[Url::to('/')] = __('Home');
$bread[Url::to('forum/index')] = __('Forum index');
$parentRecord = $forumRecord->findParent();
if ($parentRecord !== null) {
    $bread[Url::to('forum/viewforum', $parentRecord['id'])] = \App::$Translate->getLocaleText($parentRecord['name']);
}

$bread[] = $this->title;
$this->breadcrumbs = $bread;
?>

<?php
    $subforumRecords = $forumRecord->getDependItems();
?>
<?php if ($subforumRecords !== null && $subforumRecords->count() > 0): ?>
    <div class="panel forum-panel panel-major">
        <div class="panel-heading"><?= __('Subforums for: %name%', ['name' => \App::$Translate->getLocaleText($forumRecord['name'])]) ?></div>

        <div class="panel-body category-body">
            <div class="category-meta">
                <div class="col-md-5 col-sm-5 col-xs-8 forum-name"><?= __('Subforum') ?></div>
                <div class="col-md-2 col-sm-2 hidden-xs forum-stats"><?= __('Topics') ?></div>
                <div class="col-md-2 col-sm-2 hidden-xs forum-stats"><?= __('Posts') ?></div>
                <div class="col-md-3 col-sm-3 col-xs-4 forum-last-post"><?= __('Last Post') ?></div>
            </div>

            <?php foreach ($subforumRecords as $forum): ?>
                <div class="row category-row">
                    <div class="col-md-5 col-sm-5 col-xs-8 forum-info">
                        <div class="row">
                            <div class="col-md-1 col-sm-2 col-xs-2 forum-status">
                                <i class="glyphicon glyphicon-comment forum-read"></i>
                            </div>
                            <div class="col-md-11 col-sm-10 col-xs-10">
                                <!-- forum title link-name -->
                                <div class="forum-name">
                                    <?= Url::link(['forum/viewforum', $forum['id']], App::$Translate->getLocaleText($forum['name'])) ?>
                                </div>

                                <!-- Forum Description -->
                                <div class="forum-description">
                                    <p><?= App::$Translate->getLocaleText($forum['snippet']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- end forum-info -->

                    <!-- Post Cunt -->
                    <div class="col-md-2 col-sm-2 hidden-xs forum-topics">
                        <?= $forum['thread_count'] ?>
                    </div>

                    <!-- Topic Count -->
                    <div class="col-md-2 col-sm-2 hidden-xs forum-posts">
                        <?= $forum['post_count'] ?>
                    </div>

                    <?php
                    $lastThread = $forum->getLastThread();
                    ?>

                    <!-- Last Post -->
                    <div class="col-md-3 col-sm-3 col-xs-4 forum-last-post">
                        <div class="last-post-title">
                            <?php if ($lastThread !== null) {
                                echo Url::link(['forum/lastpost', $lastThread['id']], Text::snippet($lastThread['title'], 50));
                            } else {
                                echo '-';
                            } ?>
                        </div>
                        <div class="last-post-time">
                            <?php if ($lastThread !== null) {
                                echo Date::humanize($lastThread['update_time']);
                            } ?>
                        </div>
                        <div class="last-post-author">
                            <?php if ($lastThread !== null) {
                                echo __('by %user%', ['user' => Simplify::parseUserLink((int)$lastThread['updater_id'] > 0 ? $lastThread['updater_id'] : $lastThread['creator_id'])]);
                            } ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/thread')): ?>
<div class="row">
    <div class="col-md-6">
        <div class="pull-left">
            <?= $pagination->display(['class' => 'pagination', 'style' => 'margin: 0;']) ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="pull-right">
            <a href="<?= Url::to('forum/createthread', $forumRecord['id']) ?>" class="btn btn-success btn-sm">
                <i class="glyphicon glyphicon-plus"></i> <?= __('New topic') ?>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
    <p class="alert alert-warning"><?= __('You should register or authorize on website to create new threads') ?></p>
<?php endif; ?>

<form action="" method="get">
<div class="panel forum-panel">
    <div class="panel-heading">
        <?= $this->title ?>&nbsp;
        <small style="font-weight: lighter"><?= App::$Translate->getLocaleText($forumRecord['snippet']) ?></small>
    </div>

    <div class="panel-body forum-body">
        <div class="forum-meta">
            <div class="col-md-5 col-sm-5 col-xs-8 topic-name"><?= __('Topic') ?></div>
            <div class="col-md-2 col-sm-2 hidden-xs topic-stats"><?= __('Posts') ?></div>
            <div class="col-md-2 col-sm-2 hidden-xs topic-stats"><?= __('Views') ?></div>
            <div class="col-md-3 col-sm-3 col-xs-4 topic-last-post"><?= __('Last Post') ?></div>
        </div>

        <?php if ($threadRecords !== null && $threadRecords->count() > 0): ?>
            <?php foreach ($threadRecords as $thread): ?>
                <div class="row forum-row clearfix">
                    <div class="col-md-5 col-sm-5 col-xs-8 topic-info">
                        <div class="row">
                            <div class="col-md-1 col-sm-2 col-xs-2 topic-status">
                                <i class="glyphicon <?= ((bool)$thread->important ? 'glyphicon-star' : ((bool)$thread->closed ? 'glyphicon-remove-circle' : 'glyphicon-comment')); ?> topic-read"></i>
                            </div>
                            <div class="col-md-11 col-sm-10 col-xs-10">
                                <div class="topic-name">
                                    <?= Url::link(['forum/viewthread', $thread['id']], $thread['title']) ?>
                                </div>

                                <div class="topic-author">
                                    <?php
                                    $creator = App::$User->identity($thread['creator_id']);
                                    echo __('by %user%, %date%', [
                                        'user' => Simplify::parseUserLink($thread['creator_id']),
                                        'date' => Date::humanize($thread['created_at'])
                                    ]);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-2 hidden-xs topic-stats">
                        <?= $thread['post_count'] ?>
                    </div>

                    <div class="col-md-2 col-sm-2 hidden-xs topic-stats">
                        <?= $thread['view_count'] ?>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-4 topic-last-post">
                        <?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/delete')): ?>
                        <div class="pull-right">
                            <input type="checkbox" name="selected[]" value="<?= $thread['id'] ?>" id="select-<?= $thread['id'] ?>" />
                        </div>
                        <?php endif; ?>
                        <?php if ((int)$thread['updater_id'] > 0) {
                            echo Url::link(['forum/lastpost', $thread['id']], Date::humanize($thread['update_time']));
                        } else {
                            echo '-';
                        } ?>
                        <br>
                        <div class="topic-author">
                            <?php if ((int)$thread['updater_id'] > 0) {
                                echo __('by %user%', ['user' => Simplify::parseUserLink($thread['updater_id'])]);
                            } ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="alert alert-warning" style="margin: 5px;"><?= __('Topics are not found yet') ?></p>
        <?php endif; ?>
    </div>
</div>
<?php if (\App::$User->isAuth() && \App::$User->identity()->getRole()->can('forum/delete') && $threadRecords !== null && $threadRecords->count() > 0): ?>
<div class="pull-right">
    <input type="hidden" name="forum_id" value="<?= $forumRecord->id ?>" />
    <input type="submit" class="btn btn-danger" value="<?= __('Delete') ?>" formaction="<?= Url::to('forum/massdelete') ?>"/>
</div>
<?php endif; ?>
</form>


<?= $pagination->display(['class' => 'pagination']) ?>

