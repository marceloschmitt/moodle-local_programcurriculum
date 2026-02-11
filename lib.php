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


defined('MOODLE_INTERNAL') || die();

/**
 * Add item to course secondary navigation (horizontal menu).
 *
 * Moodle 5.1 compatible.
 *
 * @param navigation_node $navigation
 * @param stdClass $course
 * @param context_course $context
 */
function local_programcurriculum_extend_navigation_course_secondary(
    navigation_node $navigation,
    stdClass $course,
    context_course $context
): void {

    if (!has_capability('block/programcurriculum:viewownprogress', $context)) {
        return;
    }

    $url = new moodle_url(
        '/blocks/programcurriculum/view.php',
        ['courseid' => $course->id]
    );

    $node = navigation_node::create(
        get_string('curriculumnav', 'local_programcurriculum'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'programcurriculum'
    );

    // ESSENCIAL para aparecer no menu horizontal principal
    $node->showinflatnavigation = true;

    // Adiciona direto na navegação secundária
    $navigation->add_node($node);
}
