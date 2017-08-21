<?php

namespace Apps\Controller\Admin;

use Apps\ActiveRecord\ForumCategory;
use Apps\ActiveRecord\ForumItem;

use Apps\ActiveRecord\Role;
use Apps\Model\Admin\Forum\FormCategoryDelete;
use Apps\Model\Admin\Forum\FormCategoryUpdate;
use Apps\Model\Admin\Forum\FormForumDelete;
use Apps\Model\Admin\Forum\FormForumUpdate;
use Apps\Model\Admin\Forum\FormSettings;
use Extend\Core\Arch\AdminController;
use Apps\ActiveRecord\App as AppRecord;
use Ffcms\Core\App;
use Ffcms\Core\Exception\NotFoundException;
use Ffcms\Core\Helper\Date;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Managers\MigrationsManager;

class Forum extends AdminController
{
    const VERSION = '1.0.4';

    private $appRoot;
    private $tplDir;

    /**
     * Initialize application: set app route path, tpl path, append language translations
     */
    public function before()
    {
        parent::before();
        // define application root diskpath and tpl native directory
        $this->appRoot = realpath(__DIR__ . '/../../../');
        $this->tplDir = realpath($this->appRoot . '/Apps/View/Admin/default/');
        // load internalization package for current lang
        $langFile = $this->appRoot . '/I18n/Admin/' . App::$Request->getLanguage() . '/Forum.php';
        if (App::$Request->getLanguage() !== 'en' && File::exist($langFile)) {
            App::$Translate->append($langFile);
        }
    }

    /**
     * Demo of usage index page - just render viewer with input params
     * @return string
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws \Ffcms\Core\Exception\SyntaxException
     */
    public function actionIndex()
    {
        // get all board categories
        $categories = ForumCategory::all();
        $tree = null;

        // build category-forum-subforum tree
        foreach ($categories as $category) {
            $tree[$category['order_id']] = $category->toArray();
            /** @var $category ForumCategory */
            $forums = $category->getForumTree();
            if ($forums === null || !Obj::isArray($forums)) {
                continue;
            }

            // add sub forums and post_count/thread_count
            foreach($forums as $forum) {
                $tree[$category['order_id']]['forums'][$forum['order_id']] = $forum;
            }
            ksort($tree[$category['order_id']]['forums']);
        }
        ksort($tree);

        // render output view
        return $this->view->render('forum/index', [
            'tplPath' => $this->tplDir,
            'tree' => $tree
        ], $this->tplDir);
    }

    /**
     * Create or edit category
     * @param int|null $id
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     */
    public function actionUpdatecategory($id = null)
    {
        // find forum category object
        $category = ForumCategory::findOrNew($id);
        // initialize model and process submit
        $model = new FormCategoryUpdate($category);
        if ($model->send() && $model->validate()) {
            $model->make();
            App::$Session->getFlashBag()->add('success', __('New category is sucessful added'));
            App::$Response->redirect('forum/index');
        }

        return $this->view->render('forum/update_category', [
            'tplPath' => $this->tplDir,
            'model' => $model
        ], $this->tplDir);
    }

    /**
     * Create or edit forum item
     * @param int|null $id
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     */
    public function actionUpdateforum($id = null)
    {
        $parentForum = (int)App::$Request->query->get('parent', 0);
        // find forum item active record object
        $forum = ForumItem::findOrNew($id);

        // initialize model and process form submit
        $model = new FormForumUpdate($forum, $parentForum);
        if ($model->send() && $model->validate()) {
            $model->make();
            App::$Session->getFlashBag()->add('success', __('Forum are successful updated'));
            App::$Response->redirect('forum/index');
        }

        // render output view
        return $this->view->render('forum/update_forum', [
            'model' => $model
        ], $this->tplDir);
    }

    /**
     * Delete forum item action
     * @param int $id
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws NotFoundException
     */
    public function actionDeleteforum($id)
    {
        // find target record by id
        $forum = ForumItem::find($id);
        if ($forum === null) {
            throw new NotFoundException(__('Forum not found'));
        }

        // initialize working model
        $model = new FormForumDelete($forum);
        if ($model->send() && $model->validate()) {
            $model->make();
            App::$Session->getFlashBag()->add('success', __('Forum are successful removed'));
            App::$Response->redirect('forum/index');
        }

        return $this->view->render('forum/delete_forum', [
            'model' => $model
        ], $this->tplDir);

    }

    /**
     * Delete forum category action
     * @param int $id
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws NotFoundException
     */
    public function actionDeletecategory($id)
    {
        // find target category by id
        $category = ForumCategory::find($id);
        if ($category === null) {
            throw new NotFoundException(__('Category not found'));
        }

        // initialize delete model
        $model = new FormCategoryDelete($category);
        if ($model->send() && $model->validate()) {
            $model->make();
            App::$Session->getFlashBag()->add('success', __('Category are successful removed'));
            App::$Response->redirect('forum/index');
        }

        return $this->view->render('forum/delete_category', [
            'model' => $model
        ], $this->tplDir);
    }

    /**
     * Show forum settings
     * @return string
     * @throws \Ffcms\Core\Exception\SyntaxException
     * @throws \Ffcms\Core\Exception\NativeException
     */
    public function actionSettings()
    {
        // initialize settings model with default configs
        $model = new FormSettings($this->getConfigs());
        if ($model->send() && $model->validate()) {
            $this->setConfigs($model->getAllProperties());
            App::$Session->getFlashBag()->add('success', __('Settings is successful updated'));
            App::$Response->redirect('forum/index');
        }

        // render view
        return $this->view->render('forum/settings', [
            'model' => $model,
            'tplPath' => $this->tplDir
        ], $this->tplDir);
    }

    /**
     * Install function callback
     */
    public static function install()
    {
        // prepare application information to extend inserted before row to table apps
        $appData = new \stdClass();
        $appData->configs = [
            'threadsPerPage' => 10,
            'postPerPage' => 10,
            'delay' => 60,
            'cacheSummary' => 60,
            'metaTitle' => Serialize::encode(['en' => 'Website forum', 'ru' => 'Форум сайта']),
            'metaDescription' => '',
            'metaKeywords' => ''
        ];

        $appData->name = [
            'ru' => 'Форум',
            'en' => 'Forum'
        ];

        // get current app row from db (like SELECT ... WHERE type='app' and sys_name='Demoapp')
        $query = AppRecord::where('type', '=', 'app')->where('sys_name', '=', 'Forum');
        if ($query->count() !== 1) {
            return;
        }

        // enable application and set name, configs, version
        $query->update([
            'name' => Serialize::encode($appData->name),
            'configs' => Serialize::encode($appData->configs),
            'disabled' => 0,
            'version' => static::VERSION
        ]);

        // apply migrations
        $root = realpath(__DIR__ . '/../../../');
        $migrations = new MigrationsManager($root . '/Private/Migrations');
        $migrations->makeUp([
            'altercolumn_forumprofile_table-2017-01-05-18-25-39.php',
            'install_forumcategories_table-2017-01-05-18-09-48.php',
            'install_forumitems_table-2017-01-05-18-19-22.php',
            'install_forumonlines_table-2017-01-05-18-24-21.php',
            'install_forumposts_table-2017-01-05-18-23-20.php',
            'install_forumthreads_table-2017-01-05-18-21-41.php'
        ]);

        // add user permissions
        App::$Properties->updateConfig('Permissions', ['forum/post', 'forum/thread', 'forum/edit', 'forum/delete', 'forum/move', 'forum/pin', 'forum/close']);
        // update user default role
        $userRole = Role::find(2);
        if ($userRole !== null) {
            $userRole->permissions .= ';forum/post;forum/thread';
            $userRole->save();
        }
        $moderRole = Role::find(3);
        if ($moderRole !== null) {
            $moderRole->permissions .= ';forum/post;forum/thread;forum/edit;forum/delete;forum/close';
            $moderRole->save();
        }
        // add admin permissions
        App::$Properties->updateConfig('Permissions', [
            'Admin/Forum/Index',
            'Admin/Forum/Settings',
            'Admin/Forum/Updatecategory',
            'Admin/Forum/Deletecategory',
            'Admin/Forum/Updateforum',
            'Admin/Forum/Deleteforum',
        ]);
    }

    public static function update($dbVersion)
    {
        $root = realpath(__DIR__ . '/../../../');
        $migrations = new MigrationsManager($root . '/Private/Migrations');

        switch($dbVersion) {
            case '1.0.2':
            case '1.0.3':
                $migrations->makeUp([
                    'update_forum_tables-2017-08-21-07-10-08.php'
                ]);
            break;
            default:
                // some default actions
                break;

        }
    }
}