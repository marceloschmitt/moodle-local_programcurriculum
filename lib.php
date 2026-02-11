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
 * Extends the course settings navigation with a link to curriculum progress.
 *
 * @see https://moodledev.io/docs/5.1/apis/core/navigation
 *
 * @param navigation_node $parentnode The course navigation node.
 * @param stdClass $course The course object.
 * @param context_course $context The course context.
 */
function local_programcurriculum_extend_navigation_course(
    navigation_node $parentnode,
    stdClass $course,
    context_course $context
): void {
    if (!has_capability('block/programcurriculum:viewownprogress', $context)) {
        return;
    }

    $url = new moodle_url('/blocks/programcurriculum/view.php', ['courseid' => $course->id]);

    $parentnode->add(
        get_string('curriculumnav', 'local_programcurriculum'),
        $url
    );
}

/**
 * Injects a visible link at the top of the course page.
 *
 * Output callback: before_standard_top_of_body_html.
 * Moodle discovers it via get_plugins_with_function() - no callbacks.php needed.
 * IMPORTANT: Purge all caches after adding (Site admin → Development → Purge all caches).
 *
 * @see https://docs.moodle.org/dev/Output_callbacks
 * @return string HTML to inject at top of body, or empty string.
 */
function local_programcurriculum_before_standard_top_of_body_html(): string {
    global $PAGE;

    if (!$PAGE->course || $PAGE->course->id <= 0) {
        return '';
    }

    if ($PAGE->context->contextlevel != CONTEXT_COURSE && $PAGE->context->contextlevel != CONTEXT_MODULE) {
        return '';
    }

    $coursecontext = $PAGE->context->contextlevel == CONTEXT_COURSE
        ? $PAGE->context
        : $PAGE->context->get_course_context(false);

    if (!$coursecontext || !has_capability('block/programcurriculum:viewownprogress', $coursecontext)) {
        return '';
    }

    $url = new moodle_url('/blocks/programcurriculum/view.php', ['courseid' => $PAGE->course->id]);
    $text = get_string('curriculumnav', 'local_programcurriculum');

    $link = html_writer::link($url, $text, [
        'class' => 'btn btn-primary',
        'style' => 'display: inline-block; margin: 0.5rem 0;',
    ]);

    $html = html_writer::div(
        html_writer::div($link, 'container-fluid'),
        'local-programcurriculum-top-link',
        ['class' => 'mb-3 p-2', 'style' => 'background-color: var(--bs-gray-100, #f8f9fa);']
    );

    $script = '
    document.addEventListener("DOMContentLoaded", function() {
        var holder = document.getElementById("local-programcurriculum-top-link-movable");
        var target = document.getElementById("region-main") || document.querySelector("[role=\"main\"]");
        if (holder && target) {
            target.insertBefore(holder, target.firstChild);
        }
    });';

    $html = html_writer::div($html, '', ['id' => 'local-programcurriculum-top-link-movable']);
    $html .= html_writer::tag('script', $script, ['type' => 'text/javascript']);

    return $html;
}
