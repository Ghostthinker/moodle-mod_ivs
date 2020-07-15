<?php

$tasks = array(
        array(
                'classname' => 'mod_ivs\task\cockpit_report_daily',
                'blocking' => 0,        //do not change to 1. Other tasks will be blocked
                'minute' => '0',
                'hour' => '0',
                'day' => '*'            //Every Day
        ),

        array(
                'classname' => 'mod_ivs\task\cockpit_report_weekly',
                'blocking' => 0,        //do not change to 1. Other tasks will be blocked
                'minute' => '0',
                'hour' => '0',
                'dayofweek' => '0'      //Every Sunday  (0 ... 6)
        ),

        array(
                'classname' => 'mod_ivs\task\cockpit_report_monthly',
                'blocking' => 0,        //do not change to 1. Other tasks will be blocked
                'minute' => '0',
                'hour' => '0',
                'month' => '*'          //Every month
        ),
        array(
                'classname' => 'mod_ivs\task\ivs_plugin_usage',
                'blocking' => 0,        //do not change to 1. Other tasks will be blocked
                'minute' => '0',
                'hour' => '0',
                'day' => '*',         //Every day
        )
);
