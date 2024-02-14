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

namespace local_aise;

defined('MOODLE_INTERNAL') || die;

class locallib {
    /**
     * Ensures accent insensitive search for PostgreSQL-databases.
     * @param string $fieldname Usually the name of the table column.
     * @param string $param Usually the bound query parameter (?, :named).
     * @param bool $casesensitive Use case sensitive search when set to true (default).
     * @param bool $accentsensitive Use accent sensitive search when set to true (default). (not all databases support accent insensitive)
     * @param bool $notlike True means "NOT LIKE".
     * @param string $escapechar The escape char for '%' and '_'.
     * @return string The SQL code fragment.
     */
    public static function sql_like(string $fieldname, string $param, bool $casesensitive = false, bool $accentsensitive = false, bool $notlike = false, string $escapechar = "\\"): string {
        global $DB;

        if (!static::is_pgsql() || $accentsensitive) {
            return $DB->sql_like($fieldname, $param, $casesensitive, $accentsensitive, $notlike, $escapechar);
        }

        // Ensure extension is installed.
        static::check_extension();

        $not = $notlike ? 'NOT ' : '';
        $like = $casesensitive ? 'LIKE' : 'ILIKE';
        return "unaccent({$fieldname}) {$not}{$like} unaccent({$param}) ESCAPE '{$escapechar}'";
    }

    /**
     * Check if pgsql unaccent-extension is enabled. If not, try to enable it.
     */
    public static function check_extension(): void {
        global $DB;

        if (!self::is_pgsql()) {
            // nothing todo
            return;
        }

        $cache = \cache::make('local_aise', 'application');
        if (!$cache->get('unaccent_exists')) {
            $sql = 'SELECT oid as id, extname FROM pg_extension WHERE extname = ?';
            $params = ['unaccent'];
            $extension = $DB->get_record_sql($sql, $params);
            if (!$extension) {
                $createsql = 'CREATE EXTENSION IF NOT EXISTS unaccent';
                $DB->execute($createsql);
                // Try again
                $extension = $DB->get_record_sql('SELECT oid as id, extname FROM pg_extension WHERE extname = ?', $params);
                if (!$extension) {
                    throw new \moodle_exception("Extension unaccent must be created by a database-administrator. Please execute the statement <strong>$createsql</strong>");
                }
            }
            $cache->set('unaccent_exists', 1);
        }
    }

    public static function is_pgsql(): bool {
        global $CFG;
        return $CFG->dbtype == 'pgsql';
    }
}
