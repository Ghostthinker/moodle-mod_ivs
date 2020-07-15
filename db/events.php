<?php

$observers = array(

    //Annotation created
        array(
                'eventname' => '\mod_ivs\event\annotation_created',
                'callback' => 'ivs_annotation_event_created',
                'includefile' => '/mod/ivs/lib.php'
        ),

    //Annotation updated
        array(
                'eventname' => '\mod_ivs\event\annotation_updated',
                'callback' => 'annotation_event_updated',
                'includefile' => '/mod/ivs/lib.php'
        ),

    //Annotation deleted
        array(
                'eventname' => '\mod_ivs\event\annotation_deleted',
                'callback' => 'ivs_annotation_event_deleted',
                'includefile' => '/mod/ivs/lib.php'
        )
);
