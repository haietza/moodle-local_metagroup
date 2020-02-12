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

defined('MOODLE_INTERNAL') || die();

function process_course_enrol($event) {
    global $DB;

    $metagroup = $DB->get_record('metagroup', array('courseid' => $event->courseid));
    if (!$metagroup) {
        // Setting is disabled.
        return;
    }

    $enrol = $event->other['enrol'];
    if ($enrol == 'meta') {
        // Meta enrolment, group membership already handled.
        return;
    }

    $course = $DB->get_record('course', array('id' => $event->courseid), '*', MUST_EXIST);
    $group = groups_get_group($metagroup->groupid);

    if (groups_is_member($metagroup->groupid, $event->relateduserid)) {
        // User is alreaady a member of the metagroup.
        return;
    }

    groups_add_member($metagroup->groupid, $event->relateduserid);
}