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

class layout_test_page implements renderable, templatable{
    public function __construct($sometext) {
        $this->sometext = $sometext;
    }

    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->sometext = $this->sometext;

        return $data;
    }
}