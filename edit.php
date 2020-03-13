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
 * Implementation of edit form.
 *
 * @package   local_metagroup
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/metagroup/classes/forms/edit_form.php');
require_once($CFG->dirroot . '/local/metagroup/locallib.php');
require_once($CFG->dirroot.'/group/lib.php');

$courseid   = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('moodle/course:managegroups', $context);

$PAGE->set_url($CFG->wwwroot . '/local/metagroup/edit.php', array('courseid' => $course->id));
$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/local/metagroup/edit.php', array('courseid' => $course->id));

if (!enrol_is_enabled('meta')) {
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)), get_string('meta_error', 'local_metagroup'), null, \core\output\notification::NOTIFY_ERROR);
}

$mform = new metagroup_form();

if ($mform->is_cancelled()) {
    redirect($return);
} else if ($fromform = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    if (isset($fromform->enablemetagroup) && $fromform->enablemetagroup) {
        // Setting is enabled.
        $groupname = $fromform->groupname;
        $metagroupid = $DB->get_record('metagroup', array('courseid' => $course->id), 'groupid');

        if (!$metagroupid) {
            // No metagroup exists yet. Create and store metagroup.
            create_metagroup($course->id, $groupname, $context);
        } else {
            // Metagroup exists, may need to change name.
            edit_metagroup($metagroupid->groupid, $groupname);
        }
    } else {
        // Setting is disabled.
        delete_metagroup($course->id);
    }
    redirect($return, get_string('success', 'moodle'), null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    $PAGE->set_heading($course->fullname);
    $PAGE->set_title(get_string('pluginname', 'local_metagroup'));

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_metagroup'));
    $mform->display();
    echo $OUTPUT->footer();
}