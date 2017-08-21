<?php

use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Migrations\MigrationInterface;
use Ffcms\Core\Migrations\Migration;

/**
 * Class install_forumitems_table.
 */
class install_forumitems_table extends Migration implements MigrationInterface
{
    /**
     * Execute actions when migration is up
     * @return void
     */
    public function up()
    {
        $this->getSchema()->create('forum_items', function ($table) {
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
            $table->integer("update_time")->default(0);
            $table->timestamps();
        });
        parent::up();
    }

    /**
     * Seed created table via up() method with some data
     * @return void
     */
    public function seed()
    {
        $this->getConnection()->table('forum_items')->insert([
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
    }

    /**
     * Execute actions when migration is down
     * @return void
     */
    public function down()
    {
        $this->getSchema()->dropIfExists('forum_items');
        parent::down();
    }
}