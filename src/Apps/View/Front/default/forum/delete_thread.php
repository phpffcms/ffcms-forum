<?php

use Ffcms\Core\Helper\Url;
use Ffcms\Core\Helper\HTML\Form;

/** @var \Apps\Model\Front\Forum\FormDeleteThread $model */

$this->title = __('Delete thread');

$this->breadcrumbs = [
    Url::to('/') => __('Home'),
    Url::to('forum/index') => __('Forum index'),
    __('Delete thread')
];

?>

<h1><?= __('Delete thread') ?></h1>
<hr />
<p><?= __('Attention! If you submit delete this thread all related posts will be also removed') ?></p>
<?php $form = new Form($model, ['class' => 'form-horizontal', 'method' => 'post']) ?>
<?= $form->start() ?>

<?= $form->field('title', 'text', ['class' => 'form-control', 'disabled' => true]) ?>
<?= $form->field('postCount', 'text', ['class' => 'form-control', 'disabled' => true]) ?>
<?= $form->field('forum', 'text', ['class' => 'form-control', 'disabled' => true]) ?>

<div class="col-md-9 col-md-offset-3">
    <?= $form->submitButton(__('Delete'), ['class' => 'btn btn-danger']) ?>
    <?= Url::link(['forum/viewthread', $model->id], __('Cancel'), ['class' => 'btn btn-default']) ?>
</div>


<?= $form->finish() ?>
