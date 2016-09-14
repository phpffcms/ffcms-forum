<?php

use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Url;
use Ffcms\Core\Helper\HTML\Form;

/** @var \Apps\ActiveRecord\ForumItem $forumRecord */
/** @var \Apps\Model\Front\Forum\FormUpdateThread $model */

$this->title = __('Update thread');

$this->breadcrumbs = [
    Url::to('/') => __('Home'),
    Url::to('forum/index') => __('Forum index'),
    Url::to('forum/viewforum', $forumRecord['id']) => Serialize::getDecodeLocale($forumRecord['name']),
    __('Edit thread')
];

echo Ffcms\Widgets\Ckeditor\Ckeditor::widget(['targetClass' => 'wysiwyg', 'config' => 'config-small', 'jsConfig' => ['height' => '300']]);

?>

<h1><?= __('Edit thread') ?></h1>
<hr />
<?php $form = new Form($model, ['class' => 'form-horizontal', 'action' => '', 'method' => 'post']); ?>
<?= $form->start() ?>

<?= $form->field('title', 'text', ['class' => 'form-control'], __('Specify thread title')) ?>
<?= $form->field('message', 'textarea', ['class' => 'form-control wysiwyg', 'rows' => 7, 'html' => true]) ?>

<div class="col-md-9 col-md-offset-3">
    <?= $form->submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
    <?= Url::link(['forum/viewthread', $model->id], __('Cancel'), ['class' => 'btn btn-default']) ?>
</div>

<?= $form->finish(); ?>
