<?= \Ffcms\Core\Helper\HTML\Listing::display([
    'type' => 'ul',
    'property' => ['class' => 'nav nav-tabs'],
    'activeOrder' => 'action',
    'items' => [
        ['link' => ['forum/index'], 'text' => __('Forum list')],
        ['link' => ['forum/stream'], 'text' => __('Latest messages')]
    ]
]); ?>