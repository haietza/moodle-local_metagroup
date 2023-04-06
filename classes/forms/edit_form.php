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
 * Edit settings form.
 *
 * @package   local_metagroup
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/formslib.php");

/**
 * Edit settings form.
 *
 * @package   local_metagroup
 * @copyright 2020, Michelle Melton <meltonml@appstate.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metagroup_form extends moodleform {

    /**
     * Define update settings form.
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    public function definition() {
        global $CFG, $DB, $COURSE;

        $mform = $this->_form;
        $metagroupid = $DB->get_field('metagroup', 'groupid', array('courseid' => $COURSE->id));

        $mform->addElement('checkbox', 'enablemetagroup', get_string('enable', 'local_metagroup'));
        if ($metagroupid) {
            $mform->setDefault('enablemetagroup', 1);
        }
        $mform->addHelpButton('enablemetagroup', 'enable', 'local_metagroup');

        $mform->addElement('text', 'groupname', get_string('groupname', 'local_metagroup'));
        $mform->setType('groupname', PARAM_TEXT);
        $mform->addRule('groupname', get_string('required'), 'required');
        if ($metagroupid) {
            $groupname = $DB->get_field('groups', 'name', array('id' => $metagroupid));
        } else {
            $groupname = $COURSE->fullname . ' course';
        }
        $mform->setDefault('groupname', $groupname);
        $mform->addHelpButton('groupname', 'groupname', 'local_metagroup');

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $COURSE->id);

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * Validate update settings form submissions.
     * {@inheritDoc}
     * @see moodleform::validation()
     *
     * @param array $data Array of ("fieldname"=>value) of submitted data.
     * @param array $files Array of uploaded files "element_name"=>tmp_file_path.
     * @return array Array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = array();

        if (isset($data['enablemetagroup']) && $data['enablemetagroup']) {
            $groups = groups_get_all_groups($data['courseid']);
            foreach ($groups as $group) {
                if ($group->name == $data['groupname']) {
                    $errors['groupname'] = get_string('groupnameexists', 'group', $data['groupname']);
                    break;
                }
            }
        }
        return $errors;
    }
}