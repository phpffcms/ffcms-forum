<?php
use Ffcms\Core\Helper\HTML\Bootstrap\Nav;
use Ffcms\Core\Helper\HTML\Form;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Helper\Url;

/** @var \Apps\Model\Admin\Forum\FormForumUpdate $model */

$this->title = __('Update forum');

$this->breadcrumbs = [
    Url::to('main/index') => __('Main'),
    Url::to('application/index') => __('Applications'),
    Url::to('forum/index') => __('Forum'),
    __('Edit forum')
];
?>
<h1><?= __('Edit or add forum') ?></h1>
<hr />
<?php $form = new Form($model, ['class' => 'form-horizontal', 'action' => '']) ?>
<?= $form->start() ?>
<?php
$items = [];
foreach (\App::$Properties->get('languages') as $lang) {
    $items[] = [
        'type' => 'tab',
        'text' => __('Lang') . ': ' . Str::upperCase($lang),
        'content' => $form->field('name.' . $lang, 'text', ['class' => 'form-control'], __('Specify forum name')) .
            $form->field('snippet.' . $lang, 'text', ['class' => 'form-control'], __('Specify forum description')),
        'html' => true,
        'active' => $lang === \App::$Request->getLanguage(),
        '!secure' => true
    ];
}
?>

<?= Nav::display([
    'property' => ['class' => 'nav-pills'],
    'blockProperty' => ['class' => 'nav-locale-block nav-border'],
    'tabAnchor' => 'category-update-locale',
    'items' => $items
]) ?>

<?= $form->field('orderId', 'text', ['class' => 'form-control'], __('Set forum sorting order id. Must be unique value or empty field')) ?>
<?= $form->field('categoryId', 'select', ['class' => 'form-control', 'options' => $model->getIdCategoryArray(), 'optionsKey' => true], __('Set forum parent category')) ?>
<?= $form->field('dependId', 'select', ['class' => 'form-control', 'options' => $model->getParentForumsArray(), 'optionsKey' => true], __('If you want to use this forum as subforum, specify parent forum')) ?>

<div class="col-md-offset-3 col-md-9">
    <?= $form->submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
    <?= Url::link(['forum/index'], __('Cancel'), ['class' => 'btn btn-default']) ?>
</div>

<?= $form->finish() ?>
