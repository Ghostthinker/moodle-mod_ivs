<?php

namespace mod_ivs\ivs_match\timing;

/**
 * Class MatchTake
 */
class MatchTimingTakeResult
{

    public $pointstotal;
    public $pointsuser;
    public $score;

    public $summary = [];

    public static function evaluate_take($matchtimingtypes, $matchquestions, $matchanswers)
    {

        $matchtimingtakeresult = new MatchTimingTakeResult();
        $matchtimingtakeresult->pointstotal = 0;
        $matchtimingtakeresult->pointsuser = 0;
        $matchtimingtakeresult->score = 0;



        foreach ($matchquestions as $matchquestion) {
            $timingtype = self::find_object_by_id($matchquestion['type_data']['timing_type_id'], $matchtimingtypes);


            if (empty($matchtimingtakeresult->summary[$timingtype->id])) {
                $matchtimingtakeresult->summary[$timingtype->id] = [
                    'timing_type' => $timingtype,
                    'num_correct' => 0,
                    'sum_points' => 0
                ];
            }
            $matchtimingtakeresult->pointstotal += $timingtype->score;
        }

        foreach ($matchanswers as $questionid => $matchanswer) {
            $question = $matchquestions[$questionid];


            /** @var MatchTimingType $timingtype */
            $timingtype = self::find_object_by_id($question['type_data']['timing_type_id'], $matchtimingtypes);



            if (empty($timingtype)) {
                continue;
            }
            if ($matchanswer['is_correct']) {

                $matchtimingtakeresult->pointsuser += $timingtype->score;
                $matchtimingtakeresult->summary[$timingtype->id]['num_correct']++;
                $matchtimingtakeresult->summary[$timingtype->id]['sum_points'] += $timingtype->score;
            }
        }

        $matchtimingtakeresult->calculate_score();

        return $matchtimingtakeresult;

    }

    public function calculate_score() {

        if ($this->pointstotal > 0){
            $this->score = round($this->pointsuser / $this->pointstotal * 100, 2);
        }

    }

    public static function find_object_by_id($id, $objectarray) {
        $result =  array_filter($objectarray, function ($item) use ($id) {
            return $item->id == $id;
        });

        return end($result);

    }

}
