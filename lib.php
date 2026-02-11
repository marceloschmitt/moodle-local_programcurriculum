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
 * Adiciona "Currículo" ao menu do curso ao lado de Participants/Grades.
 *
 * @param navigation_node $coursenode The course navigation node.
 * @param stdClass $course The course object.
 * @param context_course $context The course context.
 */
function local_programcurriculum_extend_navigation_course(
    navigation_node $coursenode,
    stdClass $course,
    context_course $context
): void {
    // Verifica se o usuário tem permissão
    if (!has_capability('block/programcurriculum:viewownprogress', $context)) {
        return;
    }

    // URL do bloco/programa de currículo
    $url = new moodle_url('/blocks/programcurriculum/view.php', ['courseid' => $course->id]);

    // Cria o nó do menu
    $node = navigation_node::create(
        get_string('curriculumnav', 'local_programcurriculum'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'programcurriculum',
        new pix_icon('i/report', '')
    );

    // Define que o item apareça no menu principal
    $node->showinflatnavigation = true;

    // Define a ordem para aparecer antes do "More" (tipicamente < 50)
    $node->order = 20; // Ajuste se precisar

    // Procura o node "participants" ou "grades" para inserir após
    $insertafter = $coursenode->get('participants');
    if (!$insertafter) {
        $insertafter = $coursenode->get('grades');
    }

    if ($insertafter) {
        // Insere logo após Participants ou Grades
        $coursenode->insert_node($node, $insertafter->key);
    } else {
        // Se não encontrar, adiciona normalmente
        $coursenode->add_node($node);
    }
}
