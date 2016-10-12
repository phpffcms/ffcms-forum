<?php
use Ffcms\Core\Helper\HTML\Table;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Url;

/** @var string $tplPath */
/** @var array $tree */
$this->title = __('Forum structure');

$this->breadcrumbs = [
    Url::to('main/index') => __('Main'),
    Url::to('application/index') => __('Applications'),
    __('Forum')
];

echo $this->render('forum/_tabs', null, $tplPath);
?>
<div class="row">
    <div class="col-md-12">
        <div class="pull-right">
            <?= Url::link(['forum/updatecategory'], __('Add category'), ['class' => 'btn btn-primary']) ?>
            <?= Url::link(['forum/updateforum'], __('Add forum'), ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>

<?php
if (!Obj::isArray($tree)) {
    echo '<p class="alert alert-warning">' . __('No forums ever exists') . '</p>';
    return;
}
?>
<?php foreach ($tree as $category): ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <?= Serialize::getDecodeLocale($category['name']) ?> <sup class="text-success">[<?= $category['order_id'] ?>]</sup>
            <a href="<?= Url::to('forum/updatecategory', $category['id']) ?>"><i class="fa fa-pencil"></i></a>
            <a href="<?= Url::to('forum/deletecategory', $category['id']) ?>"><i class="fa fa-trash-o"></i></a>
        </div>
        <div class="panel-body">
        <?php if (!Obj::isArray($category['forums']) || count($category['forums']) < 1): ?>
            <p class="alert alert-warning"><?= __('Forums in this category is not found') ?></p>
        <?php else: ?>
            <?php foreach ($category['forums'] as $forum): ?>
                <div class="row">
                    <div class="col-md-9">
                        <strong><?= Serialize::getDecodeLocale($forum['name']) ?></strong> <sup class="text-warning">[<?= $forum['order_id'] ?>]</sup>
                        <p><?= Serialize::getDecodeLocale($forum['snippet']) ?></p>
                        <?php if (Obj::isArray($forum['depend']) && count($forum['depend']) > 0): ?>
                            <?php foreach ($forum['depend'] as $depend): ?>
                                <span class="label label-success"><?= Serialize::getDecodeLocale($depend['name']) ?>&nbsp;<sup>[<?= $depend['order_id'] ?>]</sup>
                                    <a href="<?= Url::to('forum/updateforum', $depend['id']) ?>"><i class="fa fa-pencil"></i></a>&nbsp;
                                    <a href="<?= Url::to('forum/deleteforum', $depend['id']) ?>"><i class="fa fa-trash-o"></i></a>
                                </span>&nbsp;
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <a href="<?= Url::to('forum/updateforum', null, null, ['parent' => $forum['id']]) ?>"><i class="fa fa-plus-circle fa-lg"></i></a>&nbsp;
                            <a href="<?= Url::to('forum/updateforum', $forum['id']) ?>"><i class="fa fa-pencil fa-lg"></i></a>&nbsp;
                            <a href="<?= Url::to('forum/deleteforum', $forum['id']) ?>"><i class="fa fa-trash-o fa-lg"></i></a>
                        </div>
                    </div>
                </div>
                <hr />
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>