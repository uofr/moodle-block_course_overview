<?php

$functions = array (
    'block_course_overview_add_favourite' => array (
        'classname'     => 'block_course_overview_external',
        'methodname'    => 'add_favourite',
        'classpath'     => 'blocks/course_overview/externallib.php',
        'description'   => 'Add a course to favourites.',
        'type'          => 'write',
        'ajax'          => true
    ),

    'block_course_overview_remove_favourite' => array (
        'classname'     => 'block_course_overview_external',
        'methodname'    => 'remove_favourite',
        'classpath'     => 'blocks/course_overview/externallib.php',
        'description'   => 'Remove a course from favourites.',
        'type'          => 'write',
        'ajax'          => true
    )
);