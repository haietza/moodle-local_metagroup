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
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class local_metagroup_locallib_testcase extends advanced_testcase {
    /**
     * Test metagroup DB record added.
     */
    public function test_metagroup_create_metagroup() {
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
    public function test_metagroup_create_group() {
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
    public function test_metagroup_edit() {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $courseid = $course->id;
        $context = context_course::instance($course->id);
        $groupname = 'Metagroup name';

        $group = new stdClass();
        $group->courseid = $courseid;
        $group->name = $groupname;
        $groupid = groups_create_group($group);

        $metagroup = new stdClass();
        $metagroup->courseid = $courseid;
        $metagroup->groupid = $groupid;
        $DB->insert_record('metagroup', $metagroup);

        $newgroupname = 'New metagroup name';
        edit_metagroup($groupid, $newgroupname);

        $groupexists = $DB->record_exists('groups', array('courseid' => $courseid, 'name' => $newgroupname));

        $this->assertTrue($groupexists);
    }

    /**
     * Test metagroup created, meta enrolments not added.
     */
    public function test_metagroup_create_meta_enrolments() {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $courseid = $course->id;
        $context = context_course::instance($course->id);

        $user1 = $this->getDataGenerator()->create_user();
        $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user1->id, $courseid, $studentroleid, 'meta');

        $user2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user2->id, $courseid, $studentroleid, 'manual');

        $groupname = 'Metagroup name';

        create_metagroup($courseid, $groupname, $context);
        $groupid = $DB->get_field('metagroup', 'groupid', array('courseid' => $courseid));

        $members = groups_get_members($groupid);
        if ($members) {
            $this->assertFalse(in_array($user1, $members));
        }
    }

    /**
     * Test metagroup created, non-meta enrolments not added.
     */
    public function test_metagroup_create_non_meta_enrolments() {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $courseid = $course->id;
        $context = context_course::instance($course->id);

        $user = $this->getDataGenerator()->create_user();
        $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $courseid, $studentroleid, 'manual');

        $groupname = 'Metagroup name';

        create_metagroup($courseid, $groupname, $context);
        $groupid = $DB->get_field('metagroup', 'groupid', array('courseid' => $courseid));

        $members = groups_get_members($groupid);
        if ($members) {
            $this->assertTrue(in_array($user, $members));
        }
    }

    /**
     * Test metagroup deleted.
     */
    public function test_metagroup_delete() {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $courseid = $course->id;
        $context = context_course::instance($course->id);

        $groupname = 'Metagroup name';
        $group = new stdClass();
        $group->courseid = $courseid;
        $group->name = $groupname;
        $groupid = groups_create_group($group);

        $metagroup = new stdClass();
        $metagroup->courseid = $courseid;
        $metagroup->groupid = $groupid;
        $DB->insert_record('metagroup', $metagroup);

        delete_metagroup($courseid);

        $metagroup = $DB->get_field('metagroup', 'id', array('courseid' => $courseid));
        $group = $DB->get_field('groups', 'id', array('courseid' => $courseid, 'name' => $groupname));

        $this->assertFalse($metagroup);
        $this->assertFalse($group);
    }

    /**
     * Test group deleted, metagroup disabled.
     */
    public function test_metagroup_group_deleted() {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $courseid = $course->id;
        $context = context_course::instance($course->id);

        $groupname = 'Metagroup name';
        $group = new stdClass();
        $group->courseid = $courseid;
        $group->name = $groupname;
        $groupid = groups_create_group($group);

        $metagroup = new stdClass();
        $metagroup->courseid = $courseid;
        $metagroup->groupid = $groupid;
        $DB->insert_record('metagroup', $metagroup);

        groups_delete_group($groupid);

        $metagroup = $DB->get_field('metagroup', 'id', array('courseid' => $courseid));

        $this->assertFalse($metagroup);
    }
}