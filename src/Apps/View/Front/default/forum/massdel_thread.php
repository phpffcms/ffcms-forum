<?php

use Ffcms\Core\Helper\Date;
use Ffcms\Core\Helper\Simplify;
use Ffcms\Core\Helper\Url;
use Ffcms\Core\Helper\HTML\Form;
use Ffcms\Core\Helper\HTML\Table;

/** @var \Apps\Model\Front\Forum\FormMassDeleteThreads $model */
/** @var int $forumId */

$this->title = __('Delete threads');

$this->breadcrumbs = [
    Url::to('/') => __('Home'),
    Url::to('forum/index') => __('Forum index'),
    __('Delete thread')
];

?>

<h1><?= __('Delete threads') ?></h1>
<hr />
<p><?= __('Attention! If you submit delete this thread all related posts will be also removed') ?></p>

<?php
$items = [];
foreach ($model->data as $item) {
    $items[] = [
        ['text' => $item['id']],
        ['text' => Url::link(['forum/viewthread', $item['id']], $item['title']), 'html' => true],
        ['text' => $item['posts']],
        ['text' => Simplify::parseUserNick($item['user_id']), 'link' => ['profile/show', $item['user_id']]],
        ['text' => Date::convertToDatetime($item['date'], Date::FORMAT_TO_HOUR)]
    ];
}
?>

<?= Table::display([
    'table' => ['class' => 'table table-bordered'],
    'thead' => ['titles' => [
        ['text' => 'id'],
        ['text' => __('Title')],
        ['text' => __('Posts')],
        ['text' => __('Owner')],
        ['text' => __('Date')]
    ]],
    'tbody' => ['items' => $items]
]); ?>

<?php $form = new Form($model, ['class' => 'form-horizontal', 'method' => 'post']) ?>
<?= $form->start() ?>

<?= $form->submitButton(__('Delete'), ['class' => 'btn btn-danger']) ?>&nbsp;
<?= Url::link(['forum/viewforum', $forumId], __('Cancel'), ['class' => 'btn btn-default']) ?>

<?= $form->finish(false) ?>
