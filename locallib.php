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
 * Local functions for plugin.
 *
 * @package   local_metagroup
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/group/lib.php');

/**
 * Handler for course enrol event.
 *
 * @param unknown $event
 */
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

function create_metagroup($courseid, $groupname, $context) {
    global $DB;
    
    $group = new stdClass();
    $group->courseid = $courseid;
    $group->name = $groupname;
    $groupid = groups_create_group($group);
    
    $metagroup = new stdClass();
    $metagroup->courseid = $courseid;
    $metagroup->groupid = $groupid;
    $DB->insert_record('metagroup', $metagroup);
    
    // Get enrollees of metacourse. Enroll them in metagroup.
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
    
    return;
}

function edit_metagroup($groupid, $groupname) {
    $group = groups_get_group($groupid);
    if ($group->name != $groupname) {
        $group->name = $groupname;
        groups_update_group($group);
    }
    return;
}

function delete_metagroup($courseid) {
    global $DB;
    
    $metagroup = $DB->get_record('metagroup', array('courseid' => $courseid));
    if ($metagroup) {
        groups_delete_group($metagroup->groupid);
        $DB->delete_records('metagroup', array('id' => $metagroup->id));
    }
    return;
}