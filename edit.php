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
 * @package   local_metagroup
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/metagroup/forms/edit_form.php');
require_once ($CFG->dirroot.'/group/lib.php');

$courseid   = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);
//require_capability();

$PAGE->set_url($CFG->wwwroot . '/local/metagroup/edit.php', array('courseid' => $course->id));
$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/local/metagroup/edit.php', array('courseid' => $course->id));

if (!enrol_is_enabled('meta')) {
    redirect($return);
}

//Instantiate simplehtml_form
$mform = new metagroup_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    redirect($return);
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    
    // SETTING ENABLED.
    if (isset($fromform->enablemetagroup) && $fromform->enablemetagroup) {
        // Create parent group, if doesn't exist.
        // Use name from form (default set).
        $context = context_course::instance($course->id);
        require_capability('moodle/course:managegroups', $context);
        
        // This function uses default group name.
        // Check that something was entered?
        $groupname = $fromform->groupname;
        $metagroupid = $DB->get_record('metagroup', array('courseid' => $course->id), 'groupid');
        
        // No metagroup exists yet.
        if (!$metagroupid) {
            // Create and store group.
            $group = new stdClass();
            $group->courseid = $course->id;
            $group->name = $groupname;
            $groupid = groups_create_group($group);
            
            $metagroup = new stdClass();
            $metagroup->courseid = $course->id;
            $metagroup->groupid = $groupid;
            $DB->insert_record('metagroup', $metagroup);
        } else {
            // Metagroup exists, may need to change name.
            $groupid = $metagroupid->groupid;
            $group = groups_get_group($groupid);
            $group->name = $fromform->groupname;
            groups_update_group($group);
        }
        
        // Get enrollees of parent course.
        // Enroll them in group.
        $userids = array();
        $plugins = enrol_get_instances($courseid, true);
        foreach ($plugins as $plugin) {
            if ($plugin->enrol != 'meta') {
                $sql = get_enrolled_sql($context, '', 0, false, false, $plugin->id);
                $userrecs = $DB->get_records_sql($sql[0], $sql[1]);
                foreach ($userrecs as $userrec) {
                    array_push($userids, $userrec->id);
                }
            }
        }
        
        foreach ($userids as $userid) {
            groups_add_member($groupid, $userid);
        }
        
        // Listen for future enrolments.
        
        redirect($return, get_string('success', 'moodle'), null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        // SETTING DISABLED.
        // Delete parent group.
        // Stop listening for enrolments.
        // Delete row from metagroup DB.
        $metagroup = $DB->get_record('metagroup', array('courseid' => $course->id));
        if ($metagroup) {
            groups_delete_group($metagroup->groupid);
            $DB->delete_records('metagroup', array('id' => $metagroup->id));
        }
        redirect($return, get_string('success', 'moodle'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    
    //Set default data (if any)
    //$mform->set_data($toform);
    //displays the form
    $PAGE->set_heading($course->fullname);
    $PAGE->set_title(get_string('pluginname', 'local_metagroup'));
    
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_metagroup'));
    $mform->display();
    echo $OUTPUT->footer();
}