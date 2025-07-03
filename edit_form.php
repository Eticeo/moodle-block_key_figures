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
require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Form for editing Key figures block
 *
 * @package    block_key_figures
 * @copyright  2023 Jan Eticeo <contact@eticeo.fr>
 * @author     2023 Jan Guevara Gabrielle <gabrielle.guevara@eticeo.fr>
 * @author     2025 Feb Belgrand Laureen <laureen.belgrand@eticeo.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_key_figures_edit_form extends block_edit_form {

    /**
     * Specific definition of the form.
     *
     * @param MoodleQuickForm $mform The form to be defined.
     * @return void
     */
    protected function specific_definition($mform): void {

        $this->page->requires->js_call_amd('block_key_figures/editform', 'init', [$this->block->instance->id, false]);

        /****************************
         *      GENERALS SETTINGS
         ****************************/
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Title of the block.
        $mform->addElement('text', 'config_title', get_string('config_title_desc', 'block_key_figures'));
        $mform->setDefault('config_title', get_string('config_title', 'block_key_figures'));
        $mform->setType('config_title', PARAM_TEXT);

        // Subtitle of the block.
        $mform->addElement('text', 'config_subtitle', get_string('config_subtitle_desc', 'block_key_figures'));
        $mform->setDefault('config_subtitle', get_string('config_subtitle', 'block_key_figures'));
        $mform->setType('config_sub_title', PARAM_TEXT);

        // Text to display under the block.
        $filepickeroptions = ['maxfiles' => EDITOR_UNLIMITED_FILES];
        $mform->addElement('editor', 'config_sub_text',
                get_string('config_sub_text', 'block_key_figures'), null, $filepickeroptions);
        $mform->setType('config_sub_text', PARAM_RAW);

        // Block width on the page (1 to 12).
        $options = range(1, 12);
        $options = array_combine($options, $options);
        $mform->addElement('select', 'config_space', get_string('configspace_desc', 'block_key_figures'), $options);
        $mform->setDefault('config_space', 12);

        // Title color
        $color = '';
        $mform->addElement('text', 'config_title_font_color', get_string('config_title_font_color_desc', 'block_key_figures'));
        $mform->setDefault('config_title_font_color', $color);
        $mform->setType('config_title_font_color', PARAM_TEXT);

        // Subtitle color.
        $color = '';
        $mform->addElement('text', 'config_subtitle_font_color',
                get_string('config_subtitle_font_color_desc', 'block_key_figures'));
        $mform->setDefault('config_subtitle_font_color', $color);
        $mform->setType('config_subtitle_font_color', PARAM_TEXT);

        // Subtext color.
        $color = '';
        $mform->addElement('text', 'config_sub_text_font_color',
                get_string('config_sub_text_font_color_desc', 'block_key_figures'));
        $mform->setDefault('config_sub_text_font_color', $color);
        $mform->setType('config_sub_text_font_color', PARAM_TEXT);

        // Background color.
        $color = '';
        $mform->addElement('text', 'config_background_color', get_string('config_background_color_desc', 'block_key_figures'));
        $mform->setDefault('config_background_color', $color);
        $mform->setType('config_background_color', PARAM_TEXT);

        // Tiles background color.
        $color = '';
        $mform->addElement('text', 'config_tile_background_color',
                get_string('config_tile_background_color_desc', 'block_key_figures'));
        $mform->setDefault('config_tile_background_color', $color);
        $mform->setType('config_tile_background_color', PARAM_TEXT);

        // Icons color.
        $color = '';
        $mform->addElement('text', 'config_icon_color', get_string('config_icon_color_desc', 'block_key_figures'));
        $mform->setDefault('config_icon_color', $color);
        $mform->setType('config_icon_color', PARAM_TEXT);

        // Number color.
        $color = '';
        $mform->addElement('text', 'config_number_color', get_string('config_number_color_desc', 'block_key_figures'));
        $mform->setDefault('config_number_color', $color);
        $mform->setType('config_number_color', PARAM_TEXT);

        // Caption color.
        $color = '';
        $mform->addElement('text', 'config_caption_color', get_string('config_caption_color_desc', 'block_key_figures'));
        $mform->setDefault('config_caption_color', $color);
        $mform->setType('config_caption_color', PARAM_TEXT);

        // Show border?
        $mform->addElement('selectyesno', 'config_tile_border', get_string('config_tile_border', 'block_key_figures'));
        $mform->setDefault('config_tile_border', 1);

        // We add a select for choose the number of blocks to display (max 12).
        $mform->addElement('select', 'config_block_number', get_string('config_block_number_desc', 'block_key_figures'),
                $options);
        $mform->setDefault('config_block_number', 2);
        $mform->setType('config_block_number', PARAM_INT);

        /****************************
         *       TILES PARAMS
         ****************************/

        $optionsflexdirection = ['row' => get_string('config_row', 'block_key_figures'),
                'column' => get_string('config_column', 'block_key_figures')];

        // For each block.
        foreach ($options as $blocknum) {
            $mform->addElement('header', 'configheader' . $blocknum,
                    get_string('config_block_settings', 'block_key_figures', $blocknum));

            // Icon of the block (html class).
            $mform->addElement('text', 'config_icon[' . $blocknum . ']', get_string('config_icon_desc', 'block_key_figures'));
            $mform->setType('config_icon[' . $blocknum . ']', PARAM_TEXT);

            // Block direction (row or column).
            $mform->addElement('select', 'config_flex_direction[' . $blocknum . ']',
                    get_string('config_flex_direction_desc', 'block_key_figures'), $optionsflexdirection);
            $mform->setType('config_flex_direction[' . $blocknum . ']', PARAM_TEXT);

            // Number of lines in the block.
            $mform->addElement('select', 'config_line_number[' . $blocknum . ']',
                    get_string('config_line_number_desc', 'block_key_figures'), $options);
            $mform->setType('config_line_number[' . $blocknum . ']', PARAM_INT);

            // For each line.
            foreach ($options as $linenum) {
                // Number to display.
                $mform->addElement('text', 'config_number[' . $blocknum . '][' . $linenum . ']',
                        get_string('config_number_desc', 'block_key_figures', $linenum));
                $mform->setType('config_number[' . $blocknum . '][' . $linenum . ']', PARAM_TEXT);

                // Caption to display.
                $mform->addElement('text', 'config_number_caption[' . $blocknum . '][' . $linenum . ']',
                        get_string('config_number_caption_desc', 'block_key_figures', $linenum));
                $mform->setType('config_number_caption[' . $blocknum . '][' . $linenum . ']', PARAM_TEXT);
            }
        }
    }
}
