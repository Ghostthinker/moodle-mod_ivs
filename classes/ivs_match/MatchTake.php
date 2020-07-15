<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 15.08.2018
 * Time: 11:36
 */

namespace mod_ivs\ivs_match;

/**
 * Class MatchTake
 *
 * @package all\modules\features\ivs_match\inc\ivs_match\exception
 */
class MatchTake {

    const STATUS_NEW = 'new';
    const STATUS_PROGRESS = 'progress';
    const STATUS_PASSED = 'passed';
    const STATUS_FAILED = 'failed';

    public $id;
    public $user_id;
    public $context_id;
    public $video_id;
    public $created;
    public $changed;
    public $completed;
    public $score;
    public $status = MatchTake::STATUS_NEW;
    public $evaluated = false;

}
