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
 * Core functions.
 *
 * @package   local_metagroup
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add link to course-user admin menu.
 *
 * @param unknown $settingsnav
 * @param unknown $context
 */
function local_metagroup_extend_settings_navigation($settingsnav, $context) {
    global $CFG, $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('moodle/course:managegroups', context_course::instance($PAGE->course->id))) {
        return;
    }

    $courseadminnode = $settingsnav->get('courseadmin');
    if ($useradminnode = $courseadminnode->get('users')) {
        $linkname = get_string('pluginname', 'local_metagroup');
        $url = new moodle_url('/local/metagroup/edit.php', array('courseid' => $PAGE->course->id));

        $useradminnode->add(
                $linkname,
                $url,
                navigation_node::TYPE_SETTING,
                'metagroup',
                'metagroup',
                new pix_icon('i/reload', $linkname));
    }
}