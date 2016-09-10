<?php

use Ffcms\Core\Helper\Url;
use Ffcms\Core\Helper\HTML\Form;

/** @var \Apps\ActiveRecord\ForumItem $forumRecord */
/** @var \Apps\Model\Front\Forum\FormCreateThread $model */

$this->title = __('Add new thread');

$this->breadcrumbs = [
    Url::to('/') => __('Home'),
    Url::to('forum/index') => __('Forum index'),
    Url::to('forum/viewforum', $forumRecord['id']) => \Ffcms\Core\Helper\Serialize::getDecodeLocale($forumRecord['name']),
    __('Create thread')
];

echo Ffcms\Widgets\Ckeditor\Ckeditor::widget(['targetClass' => 'wysiwyg', 'config' => 'config-small', 'jsConfig' => ['height' => '300']]);

?>

<h1><?= __('New thread') ?></h1>
<hr />
<?php $form = new Form($model, ['class' => 'form-horizontal', 'action' => '', 'method' => 'post']); ?>
<?= $form->start() ?>

<?= $form->field('title', 'text', ['class' => 'form-control'], __('Print the new title for thread')) ?>
<?= $form->field('message', 'textarea', ['class' => 'form-control wysiwyg', 'rows' => 7, 'html' => true]) ?>

<div class="col-md-9 col-md-offset-3">
    <?= $form->submitButton(__('Post'), ['class' => 'btn btn-primary']) ?>
</div>

<?= $form->finish(); ?>
