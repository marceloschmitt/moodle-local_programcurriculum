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
 * Adiciona link "Currículo" automaticamente no primeiro tópico do curso
 * usando a capability do bloco programcurriculum
 */
function local_programcurriculum_inject_first_section($course, $context) {
    global $PAGE;

    // Verifica permissão do usuário no contexto do curso
    if (!has_capability('block/programcurriculum:viewownprogress', $context)) {
        return;
    }

    // Garantir que estamos na visualização do curso
    if (strpos($PAGE->pagetype, 'course-view') === false) {
        return;
    }

    // URL do bloco programcurriculum
    $url = new moodle_url('/blocks/programcurriculum/view.php', ['courseid' => $course->id]);

    // HTML do link/botão
    $linkhtml = html_writer::div(
        html_writer::link(
            $url,
            get_string('curriculumnav', 'local_programcurriculum'),
            ['class' => 'btn btn-primary']
        ),
        ['class' => 'local-curriculum-firstsection', 'style' => 'margin-bottom:10px;']
    );

    // Exibe o link no topo do primeiro tópico
    echo $linkhtml;
}

/**
 * Hook chamado no render do curso
 */
function local_programcurriculum_before_standard_top_of_page() {
    global $COURSE;

    $context = context_course::instance($COURSE->id);
    local_programcurriculum_inject_first_section($COURSE, $context);
}
