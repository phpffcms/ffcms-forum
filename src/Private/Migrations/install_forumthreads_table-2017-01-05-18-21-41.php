<?php

use Ffcms\Core\Migrations\MigrationInterface;
use Ffcms\Core\Migrations\Migration;

/**
 * Class install_forumthreads_table.
 */
class install_forumthreads_table extends Migration implements MigrationInterface
{
    /**
     * Execute actions when migration is up
     * @return void
     */
    public function up()
    {
        $this->getSchema()->create('forum_threads', function ($table) {
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
        $this->getSchema()->dropIfExists('forum_threads');
        parent::down();
    }
}