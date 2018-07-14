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
 * course_overview block rendrer
 *
 * @package    block_course_overview
 * @copyright  2012 Adam Olley <adam.olley@netspot.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

# cunnintr - Hack by enright - Added to make sure the the forum functions are available
require_once("$CFG->dirroot/mod/forum/lib.php");
require_once("$CFG->dirroot/local/ur_functions.php");
# end Hack

/**
 * Course_overview block rendrer
 *
 * @copyright  2012 Adam Olley <adam.olley@netspot.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_overview_renderer extends plugin_renderer_base {

    /**
     * Construct contents of course_overview block
     *
     * @param array $courses list of courses in sorted order
     * @param array $overviews list of course overviews
     * @return string html to be displayed in course_overview block
     */
    public function course_overview($courses, $overviews) {
        global $DB,$CFG,$USER;
        
        $html = '';
        $config = get_config('block_course_overview');
        if ($config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {
            global $CFG;
            require_once($CFG->libdir.'/coursecatlib.php');
        }
        $ismovingcourse = false;
        $courseordernumber = 0;
        $maxcourses = count($courses);
        $userediting = false;
        // Intialise string/icon etc if user is editing and courses > 1
        if ($this->page->user_is_editing() && (count($courses) > 1)) {
            $userediting = true;
            $this->page->requires->js_init_call('M.block_course_overview.add_handles');

            // Check if course is moving
            $ismovingcourse = optional_param('movecourse', FALSE, PARAM_BOOL);
            $movingcourseid = optional_param('courseid', 0, PARAM_INT);
        }

        // Render first movehere icon.
        if ($ismovingcourse) {
            // Remove movecourse param from url.
            $this->page->ensure_param_not_in_url('movecourse');

            // Show moving course notice, so user knows what is being moved.
            $html .= $this->output->box_start('notice');
            $a = new stdClass();
            $a->fullname = $courses[$movingcourseid]->fullname;
            $a->cancellink = html_writer::link($this->page->url, get_string('cancel'));
            $html .= get_string('movingcourse', 'block_course_overview', $a);
            $html .= $this->output->box_end();

            $moveurl = new moodle_url('/blocks/course_overview/move.php',
                        array('sesskey' => sesskey(), 'moveto' => 0, 'courseid' => $movingcourseid));
            // Create move icon, so it can be used.
            $name = $courses[$movingcourseid]->fullname;
            $movetofirsticon = $this->output->pix_icon('movehere', get_string('movetofirst', 'block_course_overview', $name));
            $moveurl = html_writer::link($moveurl, $movetofirsticon);
            $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
        }

        foreach ($courses as $key => $course) {
            // If moving course, then don't show course which needs to be moved.
            if ($ismovingcourse && ($course->id == $movingcourseid)) {
                continue;
            }
            
            // UR Hack: cunnintr - hack to push ur css classes
        	// specify colour for course
        	$urclass = '';
        	$courserec = $DB->get_record('course',array('id'=>$course->id));
        	$categoryrec = $DB->get_record('course_categories',array('id'=>$courserec->category));
            
            
        	if (!empty($categoryrec->theme)) {
        		$categorytheme = explode('_',$categoryrec->theme);
        		$urclass = ' '.$categorytheme[count($categorytheme)-1];
        	}
        	if (!empty($courserec->theme)) {
        		$coursetheme = explode('_',$courserec->theme);
        		if (count($coursetheme)>0 && $coursetheme[0]=='urcourses') $urclass = ' '.$coursetheme[count($coursetheme)-1];
        	}
            
            /*
            $html .= '<pre>'.print_r($courserec,1).'</pre>';
            $html .= '<pre>'.print_r($categoryrec,1).'</pre>';
            $html .= '<pre>'.print_r($urclass,1).'</pre>';
            */
            
            $html .= $this->output->box_start('coursebox'.$urclass, "course-{$course->id}");
            //$html .= $this->output->box_start('coursebox', "course-{$course->id}");
            // end hack
            
            $html .= html_writer::start_tag('div', array('class' => 'course_title'));
            // If user is editing, then add move icons.
            if ($userediting && !$ismovingcourse) {
                $moveicon = $this->output->pix_icon('t/move', get_string('movecourse', 'block_course_overview', $course->fullname));
                $moveurl = new moodle_url($this->page->url, array('sesskey' => sesskey(), 'movecourse' => 1, 'courseid' => $course->id));
                $moveurl = html_writer::link($moveurl, $moveicon);
                $html .= html_writer::tag('div', $moveurl, array('class' => 'move'));

            }

            // No need to pass title through s() here as it will be done automatically by html_writer.
            $attributes = array('title' => $course->fullname);
            if ($course->id > 0) {
                if (empty($course->visible)) {
                    $attributes['class'] = 'dimmed';
                }
                $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
                $coursefullname = format_string(get_course_display_name_for_list($course), true, $course->id);
                $link = html_writer::link($courseurl, $coursefullname, $attributes);
                $html .= $this->output->heading($link, 2, 'title');
            } else {
                $html .= $this->output->heading(html_writer::link(
                    new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                    format_string($course->shortname, true), $attributes) . ' (' . format_string($course->hostname) . ')', 2, 'title');
            }
            
			// hide old course alerts now
            //$html .= get_course_alerts($course);
            
            $html .= $this->output->container('', 'flush');
            $html .= html_writer::end_tag('div');
            
            
            // UR HACK: Change display of course teachers
            $ci_context = context_course::instance($course->id);

            $ci_content = '';

            if (!empty($CFG->coursecontact)) {
                $namesarray = array();
                $coursecontactroles = explode(',', $CFG->coursecontact);
                foreach ($coursecontactroles as $roleid) {
                    if ($users = get_role_users($roleid, $ci_context, true)) {
                        foreach ($users as $teacher) {
                            $role = new stdClass();
                            $role->id = $teacher->roleid;
                            $role->name = $teacher->rolename;
                            $role->shortname = $teacher->roleshortname;
                            $role->coursealias = $teacher->rolecoursealias;
                            $fullname = fullname($teacher, has_capability('moodle/site:viewfullnames', $ci_context));
                            $namesarray[role_get_name($role, $ci_context)][] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.
                                $teacher->id.'&amp;course='.SITEID.'">'.$fullname.'</a>';
                        }
                    }
                }

                if (!empty($namesarray)) {
                    foreach($namesarray as $role=>$members) {
                        $ci_content .= '<div class="teachers">';
                        //$ci_content .= "<ul class=\"teachers\">\n<li>";
                        //$isplural = count($members) > 1 ? 's' : '';
                        //$ci_content .= $role.$isplural.': '.implode(', ', $members);
                        $ci_content .= implode(', ', $members);
                        //$ci_content .= "</li></ul>";
                        $ci_content .= '</div>';
                    }
                }
            }

            $ci_content .= $this->output->box_start('generalbox');

            $ci_summary = file_rewrite_pluginfile_urls($courserec->summary, 'pluginfile.php', $ci_context->id, 'course', 'summary', null);
            $ci_content .= format_text($ci_summary, $courserec->summaryformat, array('overflowdiv'=>true), $course->id);

            $ci_content .= $this->output->box_end();

            $html .= $ci_content;
            // end hack
            
            
            if (!empty($config->showchildren) && ($course->id > 0)) {
                // List children here.
                if ($children = block_course_overview_get_child_shortnames($course->id)) {
                    $html .= html_writer::tag('span', $children, array('class' => 'coursechildren'));
                }
            }
            
            $html .= $this->page->user_is_editing() ? '<div class="myedit_offset" style="padding-left: 1.5em">' : '';
            
            // If user is moving courses, then down't show overview.
            if (isset($overviews[$course->id]) && !$ismovingcourse) {
                // UR HACK: provide some context to display
                $isplural = count($overviews[$course->id]) > 1 ? 's' : '';
				
				// will use if we can fins way to expand all notifications
				$isexpanded = 0;
                $html .= '<div id="mya_course-'.$course->id.'" class="myactivity_notice_head" style="">You have '.($isplural?'new':'a new').' notification'.$isplural.' for the following: (<a href="#" class="mya_trigger" onclick="M.block_course_overview.expandregions(this.parentNode.parentNode.getAttribute(\'id\')); return false;">'.($isexpanded?'Hide':'Show').' Details</a>)</div>';
				
				//$html .= '<div class="myactivity_notice_head" style="">You have '.($isplural?'new':'a new').' notification'.$isplural.' for the following:</div>';
				
				//$html .= '<pre>'.print_r($overviews[$course->id],1).'</pre>';
				                
                $html .= $this->activity_display($course->id, $overviews[$course->id]);
    
            } else {
                $html .= '<div class="myactivity_notice_head" style="">You have no notifications at this time.</div>'; 
                
            }
            $html .= $this->page->user_is_editing() ? '</div>' : ''; 

            if ($config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {
                // List category parent or categories path here.
                $currentcategory = coursecat::get($course->category, IGNORE_MISSING);
                if ($currentcategory !== null) {
                    $html .= html_writer::start_tag('div', array('class' => 'categorypath'));
                    if ($config->showcategories == BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_FULL_PATH) {
                        foreach ($currentcategory->get_parents() as $categoryid) {
                            $category = coursecat::get($categoryid, IGNORE_MISSING);
                            if ($category !== null) {
                                $html .= $category->get_formatted_name().' / ';
                            }
                        }
                    }
                    $html .= $currentcategory->get_formatted_name();
                    $html .= html_writer::end_tag('div');
                }
            }

            $html .= $this->output->container('', 'flush');
            $html .= $this->output->box_end();
            $courseordernumber++;
            if ($ismovingcourse) {
                $moveurl = new moodle_url('/blocks/course_overview/move.php',
                            array('sesskey' => sesskey(), 'moveto' => $courseordernumber, 'courseid' => $movingcourseid));
                $a = new stdClass();
                $a->movingcoursename = $courses[$movingcourseid]->fullname;
                $a->currentcoursename = $course->fullname;
                $movehereicon = $this->output->pix_icon('movehere', get_string('moveafterhere', 'block_course_overview', $a));
                $moveurl = html_writer::link($moveurl, $movehereicon);
                $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
            }
        }
        // Wrap course list in a div and return.
        return html_writer::tag('div', $html, array('class' => 'course_list'));
    }

    /**
     * Coustuct activities overview for a course
     *
     * @param int $cid course id
     * @param array $overview overview of activities in course
     * @return string html of activities overview
     */
    protected function activity_display($cid, $overview) {
        $output = html_writer::start_tag('div', array('class' => 'activity_info'));
        foreach (array_keys($overview) as $module) {
			// start .activity_overview
            $output .= html_writer::start_tag('div', array('class' => 'activity_overview'));
            $url = new moodle_url("/mod/$module/index.php", array('id' => $cid));
            $modulename = get_string('modulename', $module);
            
            // UR HACK: no need to link icon, it's wrapped in a link
            //$icontext = html_writer::link($url, $this->output->pix_icon('icon', $modulename, 'mod_'.$module, array('class'=>'iconlarge')));
            $icontext = $this->output->image_icon('icon', $modulename, 'mod_'.$module, array('class'=>'iconlarge'));

            if (get_string_manager()->string_exists("activityoverview", $module)) {
                $icontext .= get_string("activityoverview", $module);
            } else {
                $icontext .= get_string("activityoverview", 'block_course_overview', $modulename);
            }

			if ($modulename == 'Course Email')  {
				$output .= html_writer::start_tag('div', array('class' => 'email_overview'));
				$output .= $overview[$module];
				$output .= html_writer::end_tag('div');
			} else {
				// Add collapsible region with overview text in it.
				$output .= $this->collapsible_region($overview[$module], '', 'region_'.$cid.'_'.$module, $icontext, '', true);
			}
			
			// close .activity_overview
			$output .= html_writer::end_tag('div');
        }
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Constructs header in editing mode
     *
     * @param int $max maximum number of courses
     * @return string html of header bar.
     */
    public function editing_bar_head($max = 0) {
        $output = $this->output->box_start('notice');

        $options = array('0' => get_string('alwaysshowall', 'block_course_overview'));
        for ($i = 1; $i <= $max; $i++) {
            $options[$i] = $i;
        }
        $url = new moodle_url('/my/index.php', ['sesskey' => sesskey()]);
        $select = new single_select($url, 'mynumber', $options, block_course_overview_get_max_user_courses(), array());
        $select->set_label(get_string('numtodisplay', 'block_course_overview'));
        $output .= $this->output->render($select);
        
        
        // ur sort by date hack
        //$sortbydate_url = new moodle_url('/blocks/course_overview/sortbydate.php',
        //    array('sesskey' => sesskey()));
        //$sortbydate_url = html_writer::link($sortbydate_url, get_string('sortby'));

        //$output .= $sortbydate_url;

        $options = array('custom'=>'Custom','startdate'=>'Start Date','timecreated'=>'Creation Date','fullname'=>'Alphabetical');


        $url = new moodle_url('/blocks/course_overview/sortby.php');
        $select = new single_select($url, 'sortby', $options, block_course_overview_get_sortby(), array());
        $select->set_label(get_string('sortby').':');
        $select->class = 'singleselect overview_sortby';
        $output .= $this->output->render($select);

        // end hack
        
        
        $output .= $this->output->box_end();
        return $output;
    }

    /**
     * Show hidden courses count
     *
     * @param int $total count of hidden courses
     * @return string html
     */
    public function hidden_courses($total) {
        if ($total <= 0) {
            return;
        }
        $output = $this->output->box_start('notice');
        $plural = $total > 1 ? 'plural' : '';
        $config = get_config('block_course_overview');
        // Show view all course link to user if forcedefaultmaxcourses is not empty.
        if (!empty($config->forcedefaultmaxcourses)) {
            $output .= get_string('hiddencoursecount'.$plural, 'block_course_overview', $total);
        } else {
            $a = new stdClass();
            $a->coursecount = $total;
            $a->showalllink = html_writer::link(new moodle_url('/my/index.php', array('mynumber' => block_course_overview::SHOW_ALL_COURSES)),
                    get_string('showallcourses'));
            $output .= get_string('hiddencoursecountwithshowall'.$plural, 'block_course_overview', $a);
        }
        
        // UR HACK: output link to display all courses
        $url = new moodle_url('/my/index.php');
        $output .= '. '.html_writer::tag('a', get_string('alwaysshowall', 'block_course_overview'), array('href' => $url.'?mynumber=0'));
        // end hack
                
        $output .= $this->output->box_end();
        return $output;
    }

    /**
     * Creates collapsable region
     *
     * @param string $contents existing contents
     * @param string $classes class names added to the div that is output.
     * @param string $id id added to the div that is output. Must not be blank.
     * @param string $caption text displayed at the top. Clicking on this will cause the region to expand or contract.
     * @param string $userpref the name of the user preference that stores the user's preferred default state.
     *      (May be blank if you do not wish the state to be persisted.
     * @param bool $default Initial collapsed state to use if the user_preference it not set.
     * @return bool if true, return the HTML as a string, rather than printing it.
     */
    protected function collapsible_region($contents, $classes, $id, $caption, $userpref = '', $default = false) {
            $output  = $this->collapsible_region_start($classes, $id, $caption, $userpref, $default);
            $output .= $contents;
            $output .= $this->collapsible_region_end();

            return $output;
        }

    /**
     * Print (or return) the start of a collapsible region, that has a caption that can
     * be clicked to expand or collapse the region. If JavaScript is off, then the region
     * will always be expanded.
     *
     * @param string $classes class names added to the div that is output.
     * @param string $id id added to the div that is output. Must not be blank.
     * @param string $caption text displayed at the top. Clicking on this will cause the region to expand or contract.
     * @param string $userpref the name of the user preference that stores the user's preferred default state.
     *      (May be blank if you do not wish the state to be persisted.
     * @param bool $default Initial collapsed state to use if the user_preference it not set.
     * @return bool if true, return the HTML as a string, rather than printing it.
     */
    protected function collapsible_region_start($classes, $id, $caption, $userpref = '', $default = false) {
        // Work out the initial state.
        if (!empty($userpref) and is_string($userpref)) {
            user_preference_allow_ajax_update($userpref, PARAM_BOOL);
            $collapsed = get_user_preferences($userpref, $default);
        } else {
            $collapsed = $default;
            $userpref = false;
        }

        if ($collapsed) {
            $classes .= ' collapsed';
        }

        $output = '';
        $output .= '<div id="' . $id . '" class="collapsibleregion ' . $classes . '">';
        $output .= '<div id="' . $id . '_sizer">';
        $output .= '<div id="' . $id . '_caption" class="collapsibleregioncaption">';
        $output .= $caption . ' ';
        $output .= '</div><div id="' . $id . '_inner" class="collapsibleregioninner">';
        $this->page->requires->js_init_call('M.block_course_overview.collapsible', array($id, $userpref, get_string('clicktohideshow')));

        return $output;
    }

    /**
     * Close a region started with print_collapsible_region_start.
     *
     * @return string return the HTML as a string, rather than printing it.
     */
    protected function collapsible_region_end() {
        $output = '</div></div></div>';
        return $output;
    }

    /**
     * Cretes html for welcome area
     *
     * @param int $msgcount number of messages
     * @return string html string for welcome area.
     */
    public function welcome_area($msgcount) {
        global $CFG, $USER;
        $output = $this->output->box_start('welcome_area');

        $picture = $this->output->user_picture($USER, array('size' => 75, 'class' => 'welcome_userpicture'));
        $output .= html_writer::tag('div', $picture, array('class' => 'profilepicture'));

        $output .= $this->output->box_start('welcome_message');
        $output .= $this->output->heading(get_string('welcome', 'block_course_overview', $USER->firstname));

        if (!empty($CFG->messaging)) {
            $plural = 's';
            if ($msgcount > 0) {
                $output .= get_string('youhavemessages', 'block_course_overview', $msgcount);
                if ($msgcount == 1) {
                    $plural = '';
                }
            } else {
                $output .= get_string('youhavenomessages', 'block_course_overview');
            }
            $output .= html_writer::link(new moodle_url('/message/index.php'),
                    get_string('message'.$plural, 'block_course_overview'));
        }
        $output .= $this->output->box_end();
        $output .= $this->output->container('', 'flush');
        $output .= $this->output->box_end();

        return $output;
    }
}
