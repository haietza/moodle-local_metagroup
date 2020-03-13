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
 * Metagroup locallib tests.
 *
 * @package   local_metagroup
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/metagroup/locallib.php');

/**
 * Unit tests for {@link local_metagroup}.
 * @group local_metagroup
 *
 */
class local_metagroup_locallib_testcase extends advanced_testcase {
    /**
     * Test metagroup DB record added.
     */
    public function test_metagroup_create_metagroup_metagroup() {
        global $DB;
        $this->resetAfterTest();
        
        $course = $this->getDataGenerator()->create_course();
        $courseid = $course->id;
        $context = context_course::instance($course->id);
        $groupname = 'Metagroup name';
        
        create_metagroup($courseid, $groupname, $context);
        
        $metagroupexists = $DB->record_exists('metagroup', array('courseid' => $courseid));
        
        $this->assertTrue($metagroupexists);
    }
    
    /**
     * Test metagroup created.
     */
    public function test_metagroup_create_metagroup_group() {
        global $DB;
        $this->resetAfterTest();
        
        $course = $this->getDataGenerator()->create_course();
        $courseid = $course->id;
        $context = context_course::instance($course->id);
        $groupname = 'Metagroup name';
        
        create_metagroup($courseid, $groupname, $context);
        
        $groupexists = $DB->record_exists('groups', array('courseid' => $courseid, 'name' => $groupname));
        
        
        $this->assertTrue($groupexists);
    }
    
    /**
     * Test metagroup name changed.
     */
    public function test_metagroup_edit_metagroup() {
        global $DB;
        $this->resetAfterTest();
        
        $course = $this->getDataGenerator()->create_course();
        $courseid = $course->id;
        $context = context_course::instance($course->id);
        $groupname = 'Metagroup name';
        
        // Create group.
        $group = new stdClass();
        $group->courseid = $courseid;
        $group->name = $groupname;
        $groupid = groups_create_group($group);
        
        // Create metagroup.
        $metagroup = new stdClass();
        $metagroup->courseid = $courseid;
        $metagroup->groupid = $groupid;
        $DB->insert_record('metagroup', $metagroup);
        
        $newgroupname = 'New metagroup name';
        edit_metagroup($groupid, $newgroupname);
        
        $groupexists = $DB->record_exists('groups', array('courseid' => $courseid, 'name' => $newgroupname));
        
        $this->assertTrue($groupexists);
    }
}