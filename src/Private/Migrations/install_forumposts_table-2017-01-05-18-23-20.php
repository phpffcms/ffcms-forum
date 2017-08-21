<?php

use Ffcms\Core\Migrations\MigrationInterface;
use Ffcms\Core\Migrations\Migration;

/**
 * Class install_forumposts_table.
 */
class install_forumposts_table extends Migration implements MigrationInterface
{
    /**
     * Execute actions when migration is up
     * @return void
     */
    public function up()
    {
        $this->getSchema()->create('forum_posts', function ($table) {
            $table->increments('id');
            $table->text('message');
            $table->integer('thread_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('lang', 16)->default('en');
            $table->integer('update_time')->default(0);
            $table->timestamps();
        });
        parent::up();
    }

    /**
     * Seed created table via up() method with some data
     * @return void
     */
    public function seed() {}

    /**
     * Execute actions when migration is down
     * @return void
     */
    public function down()
    {
        $this->getSchema()->dropIfExists('forum_posts');
        parent::down();
    }
}