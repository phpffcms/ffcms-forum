<?php

use Ffcms\Core\Helper\Url;
use Ffcms\Core\Helper\HTML\Form;

/** @var \Apps\ActiveRecord\ForumItem $forumRecord */
/** @var \Apps\Model\Front\Forum\FormMoveThread $model */

$this->title = __('Move thread');

$this->breadcrumbs = [
    Url::to('/') => __('Home'),
    Url::to('forum/index') => __('Forum index'),
    Url::to('forum/viewforum', $forumRecord['id']) => $forumRecord['name'][$this->lang],
    __('Move thread')
];

?>
<h1><?= __('Move thread') ?></h1>
<hr />
<?php $form = new Form($model, ['class' => 'form-horizontal', 'action' => '', 'method' => 'post']); ?>
<?= $form->start() ?>

<?= $form->field('title', 'text', ['class' => 'form-control', 'disabled' => true], __('Move thread title')) ?>
<?= $form->field('from', 'select', ['options' => [$model->from], 'class' => 'form-control', 'disabled' => true]) ?>
<?= $form->field('to', 'select', ['options' => $model->getForumsTree(), 'optionsKey' => true, 'class' => 'form-control'], __('Select new forum for this thread')) ?>

<div class="col-md-9 col-md-offset-3">
    <?= $form->submitButton(__('Move'), ['class' => 'btn btn-primary']) ?>
    <?= Url::link(['forum/viewthread', $model->id], __('Cancel'), ['class' => 'btn btn-default']) ?>
</div>

<?= $form->finish(); ?>
