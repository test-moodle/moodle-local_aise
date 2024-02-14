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
 * @package    local_aise
 * @copyright  2023 Austrian Federal Ministry of Education
 * @author     GTN solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;

/**
 * Ensures accent insensitive search for PostgreSQL-databases.
 * @param array $params
 * @param string $col
 * @param string $value
 * @param bool $casesensitive
 * @param bool $accentsensitive
 * @param bool $notlike
 * @param string $escapechar
 * @return string
 */
function aise_like(string $fieldname, string $param, bool $casesensitive = false, bool $accentsensitive = false, bool $notlike = false, string $escapechar = "\\"): string {
    return \local_aise\locallib::sql_like($fieldname, $param, $casesensitive, $accentsensitive, $notlike, $escapechar);
}

/**
 * This function is defined as a dummy to ensure, that lib.php is loaded by Moodle.
 * DO NOT DELETE!!!
 * @return void
 */
function local_aise_after_config() {
}
