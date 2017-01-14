<?php

use Ffcms\Core\Migrations\MigrationInterface;
use Ffcms\Core\Migrations\Migration;

/**
 * Class altercolumn_forumprofile_table.
 */
class altercolumn_forumprofile_table extends Migration implements MigrationInterface
{
    /**
     * Execute actions when migration is up
     * @return void
     */
    public function up()
    {
        $this->getSchema()->table('profiles', function ($table) {
            $table->integer('forum_post')->unsigned()->default(0);
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
        $this->getSchema()->table('profiles', function ($table) {
            $table->dropColumn('forum_post');
        });
        parent::down();
    }
}