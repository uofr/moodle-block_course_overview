<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Sort courses by date functionality for course_overview block.
 *
 * @package    block_course_overview
 * @copyright  2015 Trevor Cunningham <trevor.cunningham@uregina.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$sortby = required_param('sortby', PARAM_TEXT);

//require_sesskey();
require_login();

//$coursetomove = required_param('courseid', PARAM_INT);
//$moveto = required_param('moveto', PARAM_INT);

if ($sortby == 'custom') {
    $value = get_user_preferences('course_overview_course_sortorder_custom');
    $courses = array_flip(explode(',', $value));
    $iscustom = true;
    //die(print_r(array_flip($courses),1));
} else {
    list($courses, $sitecourses, $coursecount) = block_course_overview_get_sorted_courses();
    $iscustom = false;
   // die(print_r(array_keys($courses),1));
}

$sortedcourses = array_keys($courses);

// Create neworder list for courses.
$neworder = array();

if ($sortby == 'custom') {
    $neworder = $sortedcourses;
} else {
    $select = 'id in ('.implode(',',array_keys($courses)).')';
    $sort = $sortby.' ASC, id ASC';

    $recs = $DB->get_records_select('course', $select, null, $sort, '*');

    $neworder = array_keys($recs);    
}


/*
echo '<h1>sortby: '.$sortby.'</h1>';
echo '<h2>original</h2>';
echo print_r($sortedcourses,1);
echo '<h2>sorted</h2>';
echo print_r(array_values($neworder),1);
*/

block_course_overview_update_myorder(array_values($neworder),$iscustom);
set_user_preference('course_overview_course_sortby', $sortby);
//exit;
redirect(new moodle_url('/my/index.php'));