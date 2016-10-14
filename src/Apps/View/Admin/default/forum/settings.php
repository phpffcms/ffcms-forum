<?php

/** @var \Ffcms\Core\Arch\View $this */
/** @var Apps\Model\Admin\Content\FormSettings $model */
/** @var string $tplPath */

use Ffcms\Core\Helper\HTML\Bootstrap\Nav;
use Ffcms\Core\Helper\HTML\Form;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Helper\Url;

$this->title = __('Settings');

$this->breadcrumbs = [
    Url::to('main/index') => __('Main'),
    Url::to('application/index') => __('Applications'),
    Url::to('forum/index') => __('Forum'),
    __('Settings')
];

?>

<?= $this->render('forum/_tabs', null, $tplPath) ?>
<h1><?= __('Forum settings') ?></h1>
<hr />

<?php $form = new Form($model, ['class' => 'form-horizontal', 'action' => '']); ?>

<?= $form->start() ?>

<?= $form->field('threadsPerPage', 'text', ['class' => 'form-control'], __('Set count of topic threads per page')) ?>
<?= $form->field('postPerPage', 'text', ['class' => 'form-control'], __('Set posts count to display on 1 page')) ?>
<?= $form->field('delay', 'text', ['class' => 'form-control'], __('Specify delay between 2 messages from user in seconds')) ?>
<?= $form->field('cacheSummary', 'text', ['class' => 'form-control'], __('Specify cache time for summary statistics users online')) ?>

<?php
$items = [];
foreach (\App::$Properties->get('languages') as $lang) {
    $items[] = [
        'type' => 'tab',
        'text' => __('Lang') . ': ' . Str::upperCase($lang),
        'content' => $form->field('metaTitle.' . $lang, 'text', ['class' => 'form-control'], __('Set forum main page title')) .
            $form->field('metaDescription.' . $lang, 'text', ['class' => 'form-control'], __('Set forum meta description')) .
            $form->field('metaKeywords.' . $lang, 'text', ['class' => 'form-control'], __('Set forum keywords separeted by comma')),
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

<div class="col-md-9 col-md-offset-3">
    <?= $form->submitButton(__('Save'), ['class' => 'btn btn-primary']) ?>
</div>

<?= $form->finish() ?>