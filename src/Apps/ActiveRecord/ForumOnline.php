<?php

namespace Apps\ActiveRecord;


use Ffcms\Core\Arch\ActiveModel;

/**
 * Class ForumOnline. Active record for table forum_online
 * @package Apps\ActiveRecord
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property int $active_time
 */
class ForumOnline extends ActiveModel
{
    public $timestamps = false;

    /**
     * Update forum online status for $token or $userId
     * @param string|null $token
     * @param int $userId
     */
    public static function refresh($token = null, $userId = 0)
    {
        $record = null;
        if ($token === null && $userId === 0) {
            return;
        } elseif ($token !== null) {
            $record = self::where('token', '=', $token)->first();
        } elseif ($userId !== 0) {
            $record = self::where('user_id', $userId)->first();
        }

        if ($record === null || $record->count() < 1) {
            $record = new self();
            $record->user_id = $userId;
            $record->token = $token;
        }

        $record->active_time = time() + 900; // + 15 min from present time
        $record->save();
    }
}