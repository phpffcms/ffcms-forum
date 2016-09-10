<?php
use Ffcms\Core\Helper\HTML\Table;
use Ffcms\Core\Helper\Url;

/** @var $tplPath string */
/** @var $appRoute string */
/** @var $scriptsVersion string */
/** @var $dbVersion string */
// set global meta-title as apply dynamic property for {$this} object
$this->title = __('Demo app');

$this->breadcrumbs = [
    Url::to('main/index') => __('Main'),
    Url::to('application/index') => __('Applications'),
    __('Demo app')
];

echo $this->render('_tabs', null, $tplPath);

?>

<h1><?= __('Demo app') ?></h1>
<hr />
<p>
    <?= __('Hello! This is demonstration of usage FFCMS application package system!') ?>.
    <?= __('Below you can see some information about this package') ?>:
</p>

<?= Table::display([
    'table' => ['class' => 'table table-bordered'],
    'thead' => [
        'titles' => [
            ['text' => __('Param')],
            ['text' => __('Value')]
        ]
    ],
    'tbody' => [
        'items' => [
            [
                ['type' => 'text', 'text' => 'Application source'],
                ['type' => 'text', 'text' => $appRoute]
            ],
            [
                ['type' => 'text', 'text' => 'Scripts version'],
                ['type' => 'text', 'text' => $scriptsVersion]
            ],
            [
                ['type' => 'text', 'text' => 'Database version'],
                ['type' => 'text', 'text' => $dbVersion]
            ]
        ]
    ]
]);
?>