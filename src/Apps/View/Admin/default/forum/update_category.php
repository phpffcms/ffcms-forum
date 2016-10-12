<?php
use Ffcms\Core\Helper\HTML\Bootstrap\Nav;
use Ffcms\Core\Helper\HTML\Form;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Helper\Url;

/** @var string $tplPath */
/** @var \Apps\Model\Admin\Forum\FormCategoryUpdate $model */

$this->title = __('Edit category');

$this->breadcrumbs = [
    Url::to('main/index') => __('Main'),
    Url::to('application/index') => __('Applications'),
    Url::to('forum/index') => __('Forum'),
    __('Edit category')
];

echo $this->render('_tabs', null, $tplPath);
?>
<h1><?= __('Edit or add category') ?></h1>
<hr />
<?php $form = new Form($model, ['class' => 'form-horizontal', 'action' => '']) ?>
<?= $form->start() ?>
<?php
$items = [];
foreach (\App::$Properties->get('languages') as $lang) {
    $items[] = [
        'type' => 'tab',
        'text' => __('Lang') . ': ' . Str::upperCase($lang),
        'content' => $form->field('name.' . $lang, 'text', ['class' => 'form-control'], __('Enter category title, visible for users')),
        'html' => true,
        'active' => $lang === \App::$Request->getLanguage(),
        '!secure' => true
    ];
}
?>

<?= Nav::display([
    'property' => ['class' => 'nav-pills'],
    'blockProperty' => ['class' => 'nav-locale-block'],
    'tabAnchor' => 'category-update-locale',
    'items' => $items
]) ?>

<?= $form->field('orderId', 'text', ['class' => 'form-control'], __('Set category sorting order id. Must be unique value or empty field')) ?>

<div class="col-md-offset-3 col-md-9">
    <?= $form->submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
</div>

<?= $form->finish() ?>
