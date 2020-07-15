<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 30.08.2018
 * Time: 16:41
 */

namespace mod_ivs\ivs_match;

class AssessmentConfig {

    const TAKES_SIMULATE = 'TAKES_SIMULATE';
    const TAKES_LEFT_NEW = 'TAKES_LEFT_NEW';
    const TAKES_LEFT_PROGRESS = 'TAKES_LEFT_PROGRESS';
    const TAKES_LEFT_COMPLETED_SUCCESS = 'TAKES_LEFT_COMPLETED';
    const NO_TAKES_LEFT_COMPLETED_SUCCESS = 'NO_TAKES_LEFT_COMPLETED_SUCCESS';
    const NO_TAKES_LEFT_COMPLETED_FAILED = 'NO_TAKES_LEFT_COMPLETED_FAILED';

    const ASSESSMENT_TYPE_TAKES = 'TAKES';   //multiple takes, takes count as score unit, one take at a time
    const ASSESSMENT_TYPE_FORMATIVE = 'FORMATIVE'; //no takes, a formative training

    public $context_id;
    public $context_label;
    public $takes_left;
    public $status;

    /** @var MatchConfig */
    public $matchConfig;

    /** @var \mod_ivs\ivs_match\MatchTake[] */
    public $takes;
    public $status_description;

}
