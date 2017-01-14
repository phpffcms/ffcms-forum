<?php

use Ffcms\Core\Migrations\MigrationInterface;
use Ffcms\Core\Migrations\Migration;

/**
 * Class install_forumonlines_table.
 */
class install_forumonlines_table extends Migration implements MigrationInterface
{
    /**
     * Execute actions when migration is up
     * @return void
     */
    public function up()
    {
        $this->getSchema()->create('forum_onlines', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(0);
            $table->string('token', 1024)->nullable();
            $table->integer('active_time')->default(0);
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
        $this->getSchema()->dropIfExists('forum_onlines');
        parent::down();
    }
}