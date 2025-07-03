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
 * Library for key_figures block
 *
 * @package    block_key_figures
 * @copyright  2023 Jan Eticeo <contact@eticeo.fr>
 * @author     2023 Jan Guevara Gabrielle <gabrielle.guevara@eticeo.fr>
 * @author     2025 Feb Belgrand Laureen <laureen.belgrand@eticeo.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handles file serving for the key_figures block
 *
 * This function manages file access permissions and serves files stored in the block
 *
 * @param mixed $course Course object
 * @param mixed $birecordorcm Block instance record or course module
 * @param mixed $context Context object
 * @param mixed $filearea File area name
 * @param mixed $args Additional arguments
 * @param mixed $forcedownload Whether to force download
 * @param array $options Additional options
 * @return void
 */
function block_key_figures_pluginfile(mixed $course, mixed $birecordorcm, mixed $context, mixed $filearea, mixed $args,
        mixed $forcedownload, array $options = []): void {
    global $DB, $CFG;

    // Extract item ID from arguments.
    $itemid = array_shift($args);

    // Verify context level.
    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    // Check course access permissions.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        // Check category visibility permissions.
        $parentcontext = $context->get_parent_context();
        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            $category = $DB->get_record('course_categories', ['id' => $parentcontext->instanceid], '*', MUST_EXIST);
            if (!$category->visible) {
                require_capability('moodle/category:viewhiddencategories', $parentcontext);
            }
        }
    }

    // Verify file area.
    if ($filearea !== 'content') {
        send_file_not_found();
    }

    // Get file storage instance.
    $fs = get_file_storage();

    // Prepare file path.
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    // Get and verify file.
    $file = $fs->get_file($context->id, 'block_key_figures', 'content', $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    // Set force download based on parent context.
    if ($parentcontext = context::instance_by_id($birecordorcm->parentcontextid, IGNORE_MISSING)) {
        $forcedownload = ($parentcontext->contextlevel == CONTEXT_USER) ? true : $forcedownload;
    } else {
        $forcedownload = true;
    }

    // Serve the file.
    \core\session\manager::write_close();
    send_stored_file($file, 60 * 60, 0, $forcedownload, $options);
}
