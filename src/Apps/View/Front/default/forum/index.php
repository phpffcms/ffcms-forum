<?php
/** @var string $tplDir */
use Ffcms\Core\Helper\Date;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Simplify;
use Ffcms\Core\Helper\Text;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Helper\Url;

/** @var array $tree */
/** @var array $configs */
/** @var \Apps\Model\Front\Forum\EntityForumSummary $summary */

echo $this->render('forum/_tabs', [], $tplDir);

$title = Serialize::getDecodeLocale($configs['metaTitle']);
if (Str::likeEmpty($title)) {
    $title = __('Forum');
}

$this->title = $title;

$this->breadcrumbs = [
    Url::to('/') => __('Home'),
    __('Forum index')
];

if (!Obj::isArray($tree) || count($tree) < 1) {
    echo '<p>' . __('No forums found yet') . '</p>';
    return;
}
?>

<div class="forum-index">
<?php foreach ($tree as $category): ?>
    <div class="panel panel-default forum-panel panel-major">
        <div class="panel-heading"><?= Serialize::getDecodeLocale($category['name']) ?></div>

        <div class="panel-body category-body">
            <div class="category-meta">
                <div class="col-md-5 col-sm-5 col-xs-8 forum-name"><?= __('Forum') ?></div>
                <div class="col-md-2 col-sm-2 hidden-xs forum-stats"><?= __('Topics') ?></div>
                <div class="col-md-2 col-sm-2 hidden-xs forum-stats"><?= __('Posts') ?></div>
                <div class="col-md-3 col-sm-3 col-xs-4 forum-last-post"><?= __('Last Post') ?></div>
            </div>

        <?php if (!Obj::isArray($category['forums']) || count($category['forums']) < 1): ?>
            <p class="alert alert-warning"><?= __('Forums in this category is not found') ?></p>
        <?php else: ?>
            <?php foreach ($category['forums'] as $forum): ?>
                <div class="row category-row">
                    <div class="col-md-5 col-sm-5 col-xs-8 forum-info">
                        <div class="row">
                            <div class="col-md-1 col-sm-2 col-xs-2 forum-status">
                                <i class="fa fa-comments-o forum-read"></i>
                            </div>
                            <div class="col-md-11 col-sm-10 col-xs-10">
                                <!-- forum title link-name -->
                                <div class="forum-name">
                                    <?= Url::link(['forum/viewforum', $forum['id']], Serialize::getDecodeLocale($forum['name'])) ?>
                                </div>

                                <!-- Forum Description -->
                                <div class="forum-description">
                                    <p><?= Serialize::getDecodeLocale($forum['snippet']) ?></p>
                                </div>

                                <!-- sub forums list -->
                                <?php if (Obj::isArray($forum['depend']) && count($forum['depend']) > 0): ?>
                                <div>
                                    <ul class="list-inline forum-sublist">
                                        <?php foreach ($forum['depend'] as $sub): ?>
                                        <li><i class="fa fa-folder-open-o"></i> <?= Url::link(['forum/viewforum', $sub['id']], Serialize::getDecodeLocale($sub['name'])) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
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

                    <!-- Last Post -->
                    <div class="col-md-3 col-sm-3 col-xs-4 forum-last-post">
                        <div class="last-post-title">
                            <?php if (isset($forum['lastthread'])) {
                                echo Url::link(['forum/lastpost', $forum['lastthread']['id']], Text::snippet($forum['lastthread']['title'], 15));
                            } else {
                                echo '-';
                            } ?>
                        </div>
                        <div class="last-post-time">
                            <?php if (isset($forum['lastthread'])) {
                                echo Date::humanize($forum['updated_at']);
                            } ?>
                        </div>
                        <div class="last-post-author">
                            <?php if (isset($forum['lastthread'])) {
                                echo __('by %user%', ['user' => Simplify::parseUserLink($forum['lastthread']['user_id'])]);
                            } ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

    <div class="panel panel-default panel-forum-stats">
        <div class="panel-heading page-head">
            <?= __('Forum online: %total%, users: %users%, guests: %guests%', [
                'total' => $summary->onlineTotalCount,
                'users' => $summary->onlineUsersCount,
                'guests' => $summary->onlineGuestsCount
            ]); ?>
        </div>
        <div class="panel-body page-body">
            <div class="row page-row">
                <div class="col-md-12">
                    <?php if (count($summary->onlineUsers) < 1): ?>
                        <?= __('no data') ?>
                    <?php endif; ?>
                    <?php foreach ($summary->onlineUsers as $userId => $nick) {
                        echo Url::link(['profile/show', $userId], $nick);
                        echo (next($summary->onlineUsers) ? ", " : " ");
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>