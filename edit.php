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

$courseid   = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);
//require_capability();

$PAGE->set_url($CFG->wwwroot . '/local/metagroup/edit.php', array('courseid' => $course->id));
$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/course/admin.php', array('id' => $course->id));
//$PAGE->navbar->add($loginsite);
//$PAGE->set_title($site->fullname);
//$PAGE->set_heading($site->fullname);

//Instantiate simplehtml_form
$mform = new metagroup_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    redirect($return);
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    
    // SETTING ENABLED.
    // Create parent group, if doesn't exist.
    // Add current parent course enrolments to group.
    // Listen for future enrolments.
    
    // SETTING DISABLED.
    // Delete parent group.
    // Stop listening for enrolments.
    
    redirect($return);
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