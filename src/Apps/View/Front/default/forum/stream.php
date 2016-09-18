<?php

use Ffcms\Core\Helper\Date;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Simplify;
use Ffcms\Core\Helper\Text;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Helper\Url;

/** @var string $tplDir */
/** @var \Apps\ActiveRecord\ForumThread[] $records */

echo $this->render('forum/_tabs', [], $tplDir);

$this->title = __('Forum latest messages');

$this->breadcrumbs = [
    Url::to('/') => __('Home'),
    Url::to('forum/index') => __('Forum index'),
    __('Latest messages')
];

if ($records === null || $records->count() < 1) {
    echo '<p class="alert alert-warning">' . __('There is no threads in any forums added yet') . '</p>';
    return;
}
?>
<div class="row">
    <div class="col-md-12">
        <?php foreach ($records as $record): ?>
            <?php if ((int)$record->post_count < 1): // thread have no answers, display as new thread ?>
                <div class="forum-stream stream-newthread">
                    <span class="label label-primary"><?= Date::humanize($record->created_at) ?></span>
                    <?= __('User %user% create the new thread: %thread%', [
                        'user' => Simplify::parseUserLink($record->creator_id),
                        'thread' => Url::link(['forum/viewthread', $record->id], Text::snippet(\App::$Security->strip_tags($record->title), 50))
                    ]); ?>
                </div>
            <?php else: ?>
                <div class="forum-stream stream-newpost">
                    <span class="label label-success"><?= Date::humanize($record->created_at) ?></span>
                    <?= __('User %user% add new post answer in thread: %thread%', [
                        'user' => Simplify::parseUserLink($record->updater_id),
                        'thread' => Url::link(['forum/lastpost', $record->id], Text::snippet(\App::$Security->strip_tags($record->title), 50))
                    ]); ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>