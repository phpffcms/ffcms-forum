<?php

use Ffcms\Core\Helper\HTML\Form;
use Ffcms\Core\Helper\HTML\Table;
use Ffcms\Core\Helper\Url;

/** @var \Apps\Model\Admin\Forum\FormForumDelete $model */

$this->title = __('Delete forum');

$this->breadcrumbs = [
    Url::to('main/index') => __('Main'),
    Url::to('application/index') => __('Applications'),
    Url::to('forum/index') => __('Forum'),
    __('Delete forum')
];
?>
<h1><?= __('Delete forum') ?></h1>
<hr />
<p><?= __('Attention! You are try to delete forum item. If threads and posts will not moved to new forum they are be finally removed.') ?></p>
<div class="table-responsive">
<?= Table::display([
    'table' => ['class' => 'table table-striped'],
    'tbody' => [
        'items' => [
            [['text' => __('Forum name')], ['text' => $model->name]],
            [['text' => __('Category name')], ['text' => $model->category]],
            [['text' => __('Threads count')], ['text' => $model->threadCount]],
            [['text' => __('Posts count')], ['text' => $model->postCount]],
            [['text' => __('Subforums count')], ['text' => $model->forumsCount]],
        ]
    ]
]) ?>
</div>
<?php $form = new Form($model, ['class' => 'form-horizontal', 'action' => '']) ?>
<?= $form->start() ?>

<?= $form->field('moveTo', 'select', ['class' => 'form-control', 'options' => $model->getForums(), 'optionsKey' => true], __('Select target forum where all threads and posts are moved')) ?>

<div class="col-md-offset-3 col-md-9">
    <?= $form->submitButton(__('Delete'), ['class' => 'btn btn-danger']) ?>
    <?= Url::link(['forum/index'], __('Cancel'), ['class' => 'btn btn-default']) ?>
</div>

<?= $form->finish() ?>
