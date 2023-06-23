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
 * Event observer.
 *
 * @package   local_metagroup
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/metagroup/locallib.php');

/**
 * Event observer.
 *
 * @package   local_metagroup
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_metagroup_observer {

    /**
     * Handlers for observed events.
     *
     * @param unknown $event
     */
    public static function manage_events($event) {
        global $DB;
        $data = $event->get_data();
        switch ($data['eventname']) {
            case '\\core\\event\\group_deleted':
                $DB->delete_records('metagroup', array('groupid' => $data['objectid']));
                break;
            case '\\core\\event\\user_enrolment_created':
                process_course_enrol($event);
                break;
        }
    }
}
