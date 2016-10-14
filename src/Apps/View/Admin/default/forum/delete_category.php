<?php

use Ffcms\Core\Helper\HTML\Form;
use Ffcms\Core\Helper\Url;

/** @var \Apps\Model\Admin\Forum\FormCategoryDelete $model */

$this->title = __('Delete category');

$this->breadcrumbs = [
    Url::to('main/index') => __('Main'),
    Url::to('application/index') => __('Applications'),
    Url::to('forum/index') => __('Forum'),
    __('Delete category')
];
?>
<h1><?= __('Delete category') ?></h1>
<hr />
<p><?= __('Attention! You are try to delete forum category. If forums will not be moved to new category they are be finally removed.') ?></p>
<?php $form = new Form($model, ['class' => 'form-horizontal', 'action' => '']) ?>
<?= $form->start() ?>

<?= $form->field('name', 'text', ['class' => 'form-control', 'disabled' => true]) ?>
<?= $form->field('moveTo', 'select', ['class' => 'form-control', 'options' => $model->getCategories(), 'optionsKey' => true], __('Select target category where all forums are moved')) ?>

<div class="col-md-offset-3 col-md-9">
    <?= $form->submitButton(__('Delete'), ['class' => 'btn btn-danger']) ?>
    <?= Url::link(['forum/index'], __('Cancel'), ['class' => 'btn btn-default']) ?>
</div>

<?= $form->finish() ?>
