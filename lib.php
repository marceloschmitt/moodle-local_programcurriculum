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
 * Injects the Currículo link into the first section of the course.
 *
 * Uses before_standard_top_of_body_html (the real Moodle callback).
 * JavaScript moves the link into the first course section (#section-0).
 * IMPORTANT: Purge all caches after changes.
 *
 * @see https://docs.moodle.org/dev/Output_callbacks
 * @return string HTML to inject, or empty string.
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

    if (strpos($PAGE->pagetype, 'course-view') === false) {
        return '';
    }

    global $USER;

    $courseid = (int) $PAGE->course->id;
    $url = new moodle_url('/blocks/programcurriculum/view.php', ['courseid' => $courseid]);
    $text = get_string('curriculumnav', 'local_programcurriculum');

    $link = html_writer::link($url, $text, [
        'class' => 'btn btn-primary',
        'style' => 'display: inline-block; margin: 0.5rem 0;',
    ]);

    $wheelshtml = '';
    $canviewall = has_capability('block/programcurriculum:viewallprogress', $coursecontext);
    if (!$canviewall) {
        $mappingrepo = new \block_programcurriculum\mapping_repository();
        $coursemappings = $mappingrepo->get_by_moodle_course($courseid);
        $firstmapping = !empty($coursemappings) ? reset($coursemappings) : null;
        $curriculumid = $firstmapping ? (int) $firstmapping->curriculumid : 0;
        if ($curriculumid > 0) {
            $PAGE->requires->css('/blocks/programcurriculum/styles.css');
            $calculator = new \block_programcurriculum\progress_calculator();
            $progress = $calculator->calculate_for_user((int) $USER->id, $curriculumid);
            $enrollmentpercent = $progress['total'] > 0
                ? (int) round(($progress['enrolled'] / $progress['total']) * 100) : 0;
            $percent = $progress['percent'];
            $total = $progress['total'];
            $completed = $progress['completed'];
            $enrolleddisciplines = $progress['enrolled'];
            $wheelshtml = '<div class="programcurriculum-block-wheels programcurriculum-progress-wheels d-flex flex-wrap gap-2 mb-0">';
            $wheelshtml .= '<div class="programcurriculum-progress-wheel" role="progressbar" aria-valuenow="' . (int) $percent . '" aria-valuemin="0" aria-valuemax="100" title="' . s(get_string('progressbycompletion', 'block_programcurriculum') . ': ' . $percent . '% (' . $completed . '/' . $total . ')') . '">';
            $wheelshtml .= '<div class="programcurriculum-progress-wheel-circle" style="--p: ' . (int) $percent . ';"><span class="programcurriculum-progress-wheel-value">' . (int) $percent . '%</span></div>';
            $wheelshtml .= '<div class="programcurriculum-progress-wheel-label small fw-bold mt-1">' . s(get_string('progressbycompletion_header', 'block_programcurriculum')) . '</div>';
            $wheelshtml .= '<div class="programcurriculum-progress-wheel-detail small text-muted">' . (int) $completed . '/' . (int) $total . '</div></div>';
            $wheelshtml .= '<div class="programcurriculum-progress-wheel programcurriculum-progress-wheel--enrollment" role="progressbar" aria-valuenow="' . (int) $enrollmentpercent . '" aria-valuemin="0" aria-valuemax="100" title="' . s(get_string('progressbyenrollment', 'block_programcurriculum') . ': ' . $enrollmentpercent . '% (' . $enrolleddisciplines . '/' . $total . ')') . '">';
            $wheelshtml .= '<div class="programcurriculum-progress-wheel-circle" style="--p: ' . (int) $enrollmentpercent . ';"><span class="programcurriculum-progress-wheel-value">' . (int) $enrollmentpercent . '%</span></div>';
            $wheelshtml .= '<div class="programcurriculum-progress-wheel-label small fw-bold mt-1">' . s(get_string('progressbyenrollment_header', 'block_programcurriculum')) . '</div>';
            $wheelshtml .= '<div class="programcurriculum-progress-wheel-detail small text-muted">' . (int) $enrolleddisciplines . '/' . (int) $total . '</div></div>';
            $wheelshtml .= '</div>';
        }
    }

    $inner = $wheelshtml . html_writer::div($link, 'ms-3');
    $html = html_writer::div(
        html_writer::div($inner, 'container-fluid d-flex flex-wrap align-items-center gap-2'),
        'local-programcurriculum-top-link',
        ['class' => 'mb-3 p-2', 'style' => 'background-color: var(--bs-gray-100, #f8f9fa);']
    );

    // Move to first section: #section-0 (format_topics) or first .section
    $script = '
    document.addEventListener("DOMContentLoaded", function() {
        var holder = document.getElementById("local-programcurriculum-top-link-movable");
        var target = document.getElementById("section-0") ||
            document.querySelector(".course-content .section") ||
            document.querySelector(".sections .section") ||
            document.getElementById("region-main");
        if (holder && target) {
            var content = target.querySelector(".section_content, .content, .summary") || target;
            content.insertBefore(holder, content.firstChild);
        }
    });';

    $html = html_writer::div($html, '', ['id' => 'local-programcurriculum-top-link-movable']);
    $html .= html_writer::tag('script', $script, ['type' => 'text/javascript']);

    return $html;
}
