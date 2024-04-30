<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Renderable for the layout-test page
 *
 * @package     local_greetings
 * @copyright   2024 Ashish Pondit <ashish.pondit@dsinnovators.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_greetings\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

require_once('../../config.php');
require_once($CFG->dirroot. '/local/greetings/lib.php');

class index_page implements renderable, templatable{
    public function __construct(\moodle_page $page, $user) {
        $this->page = $page;
        $this->user = $user;
    }

    public function export_for_template(renderer_base $output): array {
        $templatedata = [];
        $context = $this->page->context;
        $user = $this->user;
        global $DB;
        global $PAGE;

        $allowpost = has_capability('local/greetings:postmessages', $context);
        $deleteanypost = has_capability('local/greetings:deleteanymessages', $context);
        $deleteownpost = has_capability('local/greetings:deleteownmessages', $context);

        $action = optional_param('action', '', PARAM_TEXT);
        if ($action == 'del') {
            require_sesskey();
            $id = required_param('id', PARAM_TEXT);

            if ($deleteanypost || $deleteownpost) {
                $params = ['id' => $id];

                if (!$deleteanypost) {
                    $params += ['userid' => $user->id];
                }

                $DB->delete_records('local_greetings_messages', $params);
                redirect($PAGE->url);
            }
        }

        $messageform = new \local_greetings\form\message_form();

        if ($data = $messageform->get_data()) {
            require_capability('local/greetings:postmessages', $context);
            $message = required_param('message', PARAM_TEXT);

            if (!empty($message)) {
                $record = new stdClass();
                $record->message = $message;
                $record->timecreated = time();
                $record->userid = $user->id;

                $DB->insert_record('local_greetings_messages', $record);

                redirect($PAGE->url);
            }
        }

        if (isloggedin()) {
            $usergreeting = local_greetings_get_greeting($user);
        } else {
            $usergreeting = get_string('greetinguser', 'local_greetings');
        }

        if ($allowpost) {
            $messageformcontent = $messageform->display();
        } else {
            $messageformcontent = null;
        }

        $userfields = \core_user\fields::for_name()->with_identity($context);
        $userfieldssql = $userfields->get_sql('u');

        $sql = "SELECT m.id, m.message, m.timecreated, m.userid
        {$userfieldssql->selects}
                FROM {local_greetings_messages} m
            LEFT JOIN {user} u ON u.id = m.userid
                ORDER BY timecreated DESC";

        $allowview = has_capability('local/greetings:viewmessages', $context);


        if ($allowview) {
            $messages = $DB->get_records_sql($sql);

            $cardbackgroundcolor = get_config('local_greetings', 'messagecardbgcolor');
        }

        $templatedata = [
            'greetingmessage'=> $usergreeting,
            'messageform'=> $messageformcontent,
            'cardbackgroundcolor'=> $cardbackgroundcolor,
            'messageposts'=> array_values($messages),
            'sessionkey'=> sesskey(),
            'baseurl'=>$PAGE->url
        ];
        return $templatedata;
    }
}