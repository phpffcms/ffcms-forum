<?php
use Ffcms\Core\Helper\HTML\Bootstrap\Nav;
?>

<?= Nav::display([
    'property' => ['class' => 'nav-tabs nav-justified'],
    'items' => [
        ['type' => 'link', 'text' => __('Structure'), 'link' => ['forum/index']],
        ['type' => 'link', 'text' => __('Settings'), 'link' => ['forum/settings']]
    ],
    'activeOrder' => 'action'
]);?>