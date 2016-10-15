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

class Forum extends AdminController
{
    const VERSION = 0.1;

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

        $query->update([
            'name' => Serialize::encode($appData->name),
            'configs' => Serialize::encode($appData->configs),
            'disabled' => 0
        ]);

        // create forum categories table
        App::$Database->schema()->create('forum_categories', function ($table) {
            $table->increments('id');
            $table->string('name', 2048);
            $table->integer('order_id')->unsigned();
            $table->timestamps();
        });

        // create forum items table schema
        App::$Database->schema()->create('forum_items', function ($table) {
            $table->increments('id');
            $table->string('name', 2048);
            $table->text('snippet')->nullable();
            $table->integer('order_id')->unsigned()->default(1);
            $table->integer('category_id')->unsigned();
            $table->integer('depend_id')->unsigned()->default(0);
            $table->integer('thread_count')->unsigned()->default(0);
            $table->integer('post_count')->unsigned()->default(0);
            $table->string('updater_id', 1024)->nullable();
            $table->string('updated_thread', 1024)->nullable();
            $table->timestamps();
        });

        // create forum threads table schema
        App::$Database->schema()->create('forum_threads', function ($table) {
            $table->increments('id');
            $table->string('title', 2048);
            $table->text('message');
            $table->integer('creator_id')->unsigned();
            $table->integer('updater_id')->unsigned();
            $table->integer('forum_id')->unsigned();
            $table->string('lang', 16)->defaunt('en');
            $table->integer('post_count')->unsigned();
            $table->integer('view_count')->unsigned();
            $table->boolean('important')->default(false);
            $table->boolean('closed')->default(false);
            $table->timestamps();
        });

        // create forum posts table schema
        App::$Database->schema()->create('forum_posts', function ($table) {
            $table->increments('id');
            $table->text('message');
            $table->integer('thread_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('lang', 16)->default('en');
            $table->timestamps();
        });

        App::$Database->schema()->create('forum_onlines', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(0);
            $table->string('token', 1024)->nullable();
            $table->integer('active_time')->default(0);
        });

        App::$Database->schema()->table('profiles', function ($table) {
            $table->integer('forum_post')->unsigned()->default(0);
        });

        $now = Date::convertToDatetime(time(), Date::FORMAT_SQL_DATE);
        // insert default forum data
        App::$Database->connection()->table('forum_categories')->insert([
            ['id' => 1, 'name' => Serialize::encode(['en' => 'General', 'ru' => 'Главная']), 'order_id' => '1', 'created_at' => $now, 'updated_at' => $now]
        ]);

        // add default forums
        App::$Database->connection()->table('forum_items')->insert([
            [
                'id' => 1,
                'name' => Serialize::encode(['en' => 'News', 'ru' => 'Новости']),
                'category_id' => 1,
                'depend_id' => 0,
                'order_id' => 1
            ],
            [
                'id' => 2,
                'name' => Serialize::encode(['en' => 'Subforum', 'ru' => 'Подфорум']),
                'category_id' => 1,
                'depend_id' => 1,
                'order_id' => 2
            ]
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
        /** use downgrade switch logic without break's. Example: db version is 0.1, but script version is 0.3
        * so when this function will be runned for 0.1 version will be applied cases 0.1, 0.2, 0.3 */
        switch($dbVersion) {
            case 0.1:
                // actions for 0.1 version without break (!!!) will also apply next
            case 0.2:
                // actions for 0.2 version aren't take 0.1 but take next ;D
            case 0.3:
                // and next..
            break;
            default:
                // some default actions
                break;

        }
    }
}