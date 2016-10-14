<?php

use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Url;
use Ffcms\Core\Helper\HTML\Form;

/** @var \Apps\ActiveRecord\ForumItem $forum */
/** @var \Apps\Model\Front\Forum\FormStatusThread $model */

$this->title = __('Thread status');

$this->breadcrumbs = [
    Url::to('/') => __('Home'),
    Url::to('forum/index') => __('Forum index'),
    Url::to('forum/viewforum', $forum['id']) => \App::$Translate->getLocaleText($forum['name']),
    __('Thread status')
];
?>
<h1><?= __('Change thread status') ?></h1>
<hr />
<?php $form = new Form($model, ['class' => 'form-horizontal', 'action' => '', 'method' => 'post']); ?>
<?= $form->start() ?>

<?= $form->field('title', 'text', ['class' => 'form-control', 'disabled' => true]) ?>
<?= $form->field('pinned', 'checkbox', null, __('Pin thread in forum and mark as important?')) ?>
<?= $form->field('closed', 'checkbox', null, __('Close thread from newest posts?')) ?>

<div class="col-md-9 col-md-offset-3">
    <?= $form->submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
    <?= Url::link(['forum/viewthread', $model->id], __('Cancel'), ['class' => 'btn btn-default']) ?>
</div>

<?= $form->finish(); ?>
