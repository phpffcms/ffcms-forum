<?php

use Ffcms\Core\Migrations\MigrationInterface;
use Ffcms\Core\Migrations\Migration;

/**
 * Class update_forum_tables.
 */
class update_forum_tables extends Migration implements MigrationInterface
{
    /**
     * Apply database migration for forum 1.0.3 -> 1.0.4
     * @return void
     */
    public function up()
    {
        // use important column for content app
        if (!$this->getSchema()->hasColumn('forum_items', 'update_time')) {
            $this->getSchema()->table('forum_items', function ($table) {
                $table->integer('update_time')->default(0);
            });
        }
        if (!$this->getSchema()->hasColumn('forum_posts', 'update_time')) {
            $this->getSchema()->table('forum_posts', function ($table) {
                $table->integer('update_time')->default(0);
            });
        }
        if (!$this->getSchema()->hasColumn('forum_threads', 'update_time')) {
            $this->getSchema()->table('forum_threads', function ($table) {
                $table->integer('update_time')->default(0);
            });
        }
        parent::up();
    }

    /**
     * Seed created table via up() method with some data
     * @return void
     */
    public function seed() {}

    /**
     * Merge back update migration
     * @return void
     */
    public function down()
    {
        if ($this->getSchema()->hasColumn('forum_items', 'update_time')) {
            $this->getSchema()->table('forum_items', function ($table) {
                $table->dropColumn('update_time');
            });
        }
        if ($this->getSchema()->hasColumn('forum_posts', 'update_time')) {
            $this->getSchema()->table('forum_posts', function ($table) {
                $table->dropColumn('update_time');
            });
        }
        if ($this->getSchema()->hasColumn('forum_threads', 'update_time')) {
            $this->getSchema()->table('forum_threads', function ($table) {
                $table->dropColumn('update_time');
            });
        }
        parent::down();
    }
}