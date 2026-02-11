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
 * Extends the global navigation with a link to curriculum progress.
 *
 * Adds "Currículo" to the main course node (same level as Participants, Grades)
 * when the user has block/programcurriculum:viewownprogress.
 * Requires block_programcurriculum to be installed.
 *
 * @param global_navigation $nav The global navigation object.
 */
function local_programcurriculum_extend_navigation(global_navigation $nav): void {
    global $PAGE;

    if (!$PAGE->course || $PAGE->course->id <= 0) {
        return;
    }

    if ($PAGE->context->contextlevel != CONTEXT_COURSE && $PAGE->context->contextlevel != CONTEXT_MODULE) {
        return;
    }

    $coursecontext = $PAGE->context->contextlevel == CONTEXT_COURSE
        ? $PAGE->context
        : $PAGE->context->get_course_context(false);

    if (!$coursecontext || !has_capability('block/programcurriculum:viewownprogress', $coursecontext)) {
        return;
    }

    $courseid = (int) $PAGE->course->id;
    $coursenode = $nav->find($courseid, navigation_node::TYPE_COURSE);

    if (!$coursenode) {
        return;
    }

    $url = new moodle_url('/blocks/programcurriculum/view.php', ['courseid' => $courseid]);

    $coursenode->add(
        get_string('curriculumnav', 'local_programcurriculum'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'programcurriculum',
        new pix_icon('i/report', '')
    );
}
