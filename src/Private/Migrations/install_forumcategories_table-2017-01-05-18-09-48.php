<?php

use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Migrations\MigrationInterface;
use Ffcms\Core\Migrations\Migration;

/**
 * Class install_forum_categories.
 */
class install_forumcategories_table extends Migration implements MigrationInterface
{
    /**
     * Execute actions when migration is up
     * @return void
     */
    public function up()
    {
        $this->getSchema()->create('forum_categories', function ($table){
            $table->increments('id');
            $table->string('name', 2048);
            $table->integer('order_id')->unsigned();
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
        $this->getConnection()->table('forum_categories')->insert([
            ['id' => 1, 'name' => Serialize::encode(['en' => 'General', 'ru' => 'Главная']), 'order_id' => '1', 'created_at' => $this->now, 'updated_at' => $this->now]
        ]);
    }

    /**
     * Execute actions when migration is down
     * @return void
     */
    public function down()
    {
        $this->getSchema()->dropIfExists('forum_categories');
        parent::down();
    }
}