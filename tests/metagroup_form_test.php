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
 * Metagroup edit form tests.
 *
 * @package   local_metagroup
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/metagroup/classes/forms/edit_form.php');

/**
 * Unit tests for {@link local_metagroup}.
 * @group local_metagroup
 *
 */
class local_metagroup_form_testcase extends advanced_testcase {
    /**
     * Test metagroup form group name exists.
     */
    public function test_metagroup_form_group_name_exists() {
        global $DB;
        $this->resetAfterTest();
        
        $course = $this->getDataGenerator()->create_course();
        $courseid = $course->id;
        $groupname = 'Metagroup name';
        $this->getDataGenerator()->create_group(array('courseid' => $courseid, 'name' => $groupname));

        $submitteddata = array(
                'enablemetagroup' => 1,
                'groupname' => $groupname
        );
        metagroup_form::mock_submit($submitteddata);
        
        $form = new metagroup_form();
        $toform = new stdClass();
        $toform->courseid = $courseid;
        $form->set_data($toform);
        $fromform = $form->get_data();

        $this->assertNull($fromform);
    }
}