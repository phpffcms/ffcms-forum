<?php

namespace Apps\Model\Front\Forum;


use Apps\ActiveRecord\ForumOnline;
use Apps\ActiveRecord\Profile;
use Ffcms\Core\App;
use Ffcms\Core\Arch\Model;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;

/**
 * Class EntityForumSummary. Build forum summary statistics.
 * @package Apps\Model\Front\Forum
 */
class EntityForumSummary extends Model
{
    const USER_COUNT_LIMIT = 100;

    public $onlineUsers = [];
    public $onlineTotalCount = 0;
    public $onlineGuestsCount = 0;
    public $onlineUsersCount = 0;

    public $totalThreads = 0;
    public $totalPosts = 0;

    private $_now;
    private $_cacheTime;

    /**
     * EntityForumSummary constructor. Pass cache time inside
     * @param int $cacheTime
     */
    public function __construct($cacheTime = 60)
    {
        $this->_cacheTime = (int)$cacheTime;
        if ($this->_cacheTime < 15) {
            $this->_cacheTime = 15;
        }
        parent::__construct(false);
    }

    /**
     * Build summary data or get it from cache
     */
    public function before()
    {
        $cached = App::$Cache->get('forum.summary.stats');
        if ($cached !== null && Obj::isArray($cached)) {
            foreach ($cached as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->{$property} = $value;
                }
            }
        } else {
            $this->_now = time();
            $this->buildOnlineList();
            $this->buildOnlineCount();
            App::$Cache->set('forum.summary.stats', get_object_vars($this), $this->_cacheTime);
        }
    }

    /**
     * Build users online list as key-value: user_id-user_name
     */
    private function buildOnlineList()
    {
        $onlineUsersQuery = ForumOnline::where('active_time', '>=', $this->_now)->where('user_id', '>', 0)->get(['user_id'])->toArray();
        $userIds = Arr::pluck('user_id', $onlineUsersQuery);
        $profileRecords = Profile::whereIn('user_id', $userIds)->take(static::USER_COUNT_LIMIT)->get();
        foreach ($profileRecords as $record) {
            /** @var Profile $record */
            $this->onlineUsers[$record->user_id] = $record->getNickname();
        }
    }

    /**
     * Calculate online counts for all, users and guests
     */
    private function buildOnlineCount()
    {
        $this->onlineTotalCount = ForumOnline::where('active_time', '>=', $this->_now)->count();
        $this->onlineUsersCount = ForumOnline::where('active_time', '>=', $this->_now)->where('user_id', '>', 0)->count();
        $this->onlineGuestsCount = $this->onlineTotalCount - $this->onlineUsersCount;
    }

}