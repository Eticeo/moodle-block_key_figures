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
 * Renderer for Key figures block
 *
 * @package    block_key_figures
 * @copyright  2023 Jan Eticeo <contact@eticeo.fr>
 * @author     2023 Jan Guevara Gabrielle <gabrielle.guevara@eticeo.fr>
 * @author     2025 Feb Belgrand Laureen <laureen.belgrand@eticeo.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('BLOCK_KEY_FIGURES', 'block_key_figures');

/**
 * Key figures block class
 *
 * This block displays key figures with customizable styling and layout options
 *
 * @package    block_key_figures
 * @copyright  2023 Jan Eticeo <contact@eticeo.fr>
 * @author     2023 Jan Guevara Gabrielle <gabrielle.guevara@eticeo.fr>
 * @author     2025 Feb Belgrand Laureen <laureen.belgrand@eticeo.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_key_figures extends block_base {

    /**
     * Initialize the block
     *
     * @return void
     */
    public function init(): void {
        $this->title = get_string('pluginname', BLOCK_KEY_FIGURES);
    }

    /**
     * Define where the block can be used
     *
     * @return array
     */
    public function applicable_formats(): array {
        return ['all' => true];
    }

    /**
     * Hide the block header
     *
     * @return bool
     */
    public function hide_header(): bool {
        return true;
    }

    /**
     * Allow multiple instances of the block
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return true;
    }

    /**
     * Enable block configuration
     *
     * @return bool
     */
    public function has_config(): bool {
        return true;
    }

    /**
     * Get HTML attributes for the block
     *
     * @return array
     */
    public function html_attributes(): array {
        $attributes = [
            'id' => 'inst' . $this->instance->id,
            'class' => 'block_' . $this->name() . ' block ' . $this->bootstrap_size(),
            'role' => $this->get_aria_role(),
        ];

        if ($this->hide_header()) {
            $attributes['class'] .= ' no-header';
        }

        if ($this->instance_can_be_docked() && get_user_preferences('docked_block_instance_' . $this->instance->id, 0)) {
            $attributes['class'] .= ' dock_on_load';
        }

        return $attributes;
    }

    /**
     * Get block content
     *
     * @return stdClass|null
     */
    public function get_content(): stdClass|null {
        if ($this->content !== null) {
            return $this->content;
        }

        // Set block title.
        $this->title = !empty($this->config->title) ? $this->config->title : get_string('config_title', BLOCK_KEY_FIGURES);

        // Initialize content.
        $this->content = new stdClass();
        $this->content->text = $this->get_blocks_content();

        // Load required JavaScript
        $this->page->requires->js('/blocks/key_figures/js/counter_incrementation.js', true);
        $this->page->requires->js_call_amd('block_key_figures/editform', 'init', [$this->instance->id, true]);

        return $this->content;
    }

    /**
     * Get Bootstrap column size class
     *
     * @return string
     */
    public function bootstrap_size(): string {
        $space = !empty($this->config->space) ? $this->config->space : 12;
        return "col-{$space} col-md-{$space} col-sm-12";
    }

    /**
     * Generate block content HTML
     *
     * @return string
     */
    public function get_blocks_content(): string {
        // Initialize content with styles.
        $content = $this->generate_styles();

        // Add border class if enabled.
        $tilesborder = !empty($this->config->tile_border) ? 'border_display' : '';

        // Generate title section.
        $content .= $this->generate_title_section();

        // Generate blocks container.
        $content .= '<div class="block_key_figures_container ' . $tilesborder . '">';
        $content .= $this->generate_blocks();
        $content .= '</div>';

        return $content;
    }

    /**
     * Generate CSS styles for the block
     *
     * @return string
     */
    private function generate_styles(): string {
        $instanceid = '#inst' . $this->instance->id;
        $styles = '';

        $colors = [
            'title_font_color' => [".block_key_figures_title h2", "color"],
            'subtitle_font_color' => [".block_key_figures_title >*:not(h2):not(.sub_text)", "color"],
            'sub_text_font_color' => [".block_key_figures_title .sub_text p", "color"],
            'background_color' => ["", "background-color"],
            'tile_background_color' => [".sub_block_key_figures", "background-color"],
            'icon_color' => [".sub_block_key_figures .block_title", "color"],
            'number_color' => [".sub_block_key_figures .row-numbers .col-number", "color"],
            'caption_color' => [".sub_block_key_figures .row-numbers .col-number-caption", "color"],
        ];

        foreach ($colors as $configkey => [$selector, $property]) {
            if (!empty($this->config->$configkey)) {
                if ($selector) {
                    $styles .= "$instanceid $selector { $property: {$this->config->$configkey} !important; }";
                } else {
                    $styles .= "$instanceid, $instanceid .content { $property: {$this->config->$configkey} !important; }";
                }
            }
        }

        return $styles ? "<style>$styles</style>" : '';
    }

    /**
     * Generate title section HTML
     *
     * @return string
     */
    private function generate_title_section(): string {
        $content = '<div class="block_key_figures_title">
                    <h2>' . $this->config->title . '</h2>
                    <span>' . $this->config->subtitle . '</span>';

        if (!empty($this->config->sub_text['text'])) {
            $content .= '<div class="sub_text">' . $this->config->sub_text['text'] . '</div>';
        }

        $content .= '</div>';
        return $content;
    }

    /**
     * Generate blocks HTML
     *
     * @return string
     */
    private function generate_blocks(): string {
        $content = '';
        $blocksnumber = !empty($this->config->block_number) ? (int) $this->config->block_number : 0;
        $col = $this->get_column_class($blocksnumber);

        for ($blocknum = 1; $blocknum <= $blocksnumber; $blocknum++) {
            $flexdirection = $this->config->flex_direction[$blocknum] ?? 'row';
            $content .= $this->generate_single_block($blocknum, $col, $flexdirection);
        }

        return $content;
    }

    /**
     * Get Bootstrap column class based on number of blocks
     *
     * @param int $blocksnumber Number of blocks
     * @return string
     */
    private function get_column_class(int $blocksnumber): string {
        $columnmap = [
            1 => "col-12",
            2 => "col-6",
            3 => "col-4",
            4 => "col-3",
            5 => "col-4",
            6 => "col-4",
            7 => "col-3",
            8 => "col-3",
            9 => "col-4",
            10 => "col-3",
            11 => "col-3",
            12 => "col-3",
        ];

        return $columnmap[$blocksnumber] ?? "col-12";
    }

    /**
     * Generate single block HTML
     *
     * @param int $blocknum Block number
     * @param string $col Column class
     * @param string $flexdirection Flex direction
     * @return string
     */
    private function generate_single_block(int $blocknum, string $col, string $flexdirection): string {
        $content = '<div class="sub_block_key_figures ' . $col . '">';

        // Add icon if exists.
        if (!empty($this->config->icon[$blocknum])) {
            $content .= '<div class="block_title"><i class="' . $this->config->icon[$blocknum] . '"></i></div>';
        }

        // Generate numbers section.
        $content .= $this->generate_numbers_section($blocknum, $flexdirection);

        $content .= '</div>';
        return $content;
    }

    /**
     * Generate numbers section HTML
     *
     * @param int $blocknum Block number
     * @param string $flexdirection Flex direction
     * @return string
     */
    private function generate_numbers_section(int $blocknum, string $flexdirection): string {
        $content = $flexdirection == "column" ? '<div class="row">' : '<table>';
        $linesnumber = $this->config->line_number[$blocknum] ?? 0;

        for ($linenum = 1; $linenum <= $linesnumber; $linenum++) {
            $number = $this->config->number[$blocknum][$linenum] ?? '';
            $numbercaption = $this->config->number_caption[$blocknum][$linenum] ?? '';
            $idcolnumber = 'number_' . $this->instance->id . '_' . $blocknum . '_' . $linenum;

            $content .= $this->generate_number_row($number, $numbercaption, $idcolnumber, $flexdirection);
        }

        $content .= $flexdirection == "column" ? '</div>' : '</table>';
        return $content;
    }

    /**
     * Generate number row HTML
     *
     * @param string $number Number value
     * @param string $numbercaption Number caption
     * @param string $idcolnumber Column ID
     * @param string $flexdirection Flex direction
     * @return string
     */
    private function generate_number_row(string $number, string $numbercaption, string $idcolnumber, string $flexdirection): string {
        if ($flexdirection == "column") {
            return '<div class="col row-numbers">
                        <div class="col-number" id="' . $idcolnumber . '">' . $number . '</div>
                        <div class="col-number-caption">' . $numbercaption . '</div>
                    </div>';
        }

        return '<tr class="row-numbers">
                    <td class="col-number" id="' . $idcolnumber . '">' . $number . '</td>
                    <td class="col-number-caption">' . $numbercaption . '</td>
                </tr>';
    }

    /****************************************************
     *         Fonctions to work the atto images
     ****************************************************/

    /**
     * Return the content of the block for external functions.
     *
     * @param renderer_base $output the output format
     * @return stdClass
     */
    public function get_content_for_external($output): stdClass {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');

        $content = new stdClass;
        $content->title = null;
        $content->content = '';
        $content->contentformat = FORMAT_MOODLE;
        $content->footer = '';
        $content->files = [];

        if (!$this->hide_header()) {
            $content->title = $this->title;
        }

        if (isset($this->config->sub_text)) {
            $filteropt = new stdClass;
            if ($this->content_is_trusted()) {
                // Fancy html allowed only on course, category and system blocks.
                $filteropt->noclean = true;
            }

            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config.
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            list($content->content, $content->contentformat) =
                external_format_text(
                    $this->config->sub_text,
                    $format,
                    $this->context,
                    'block_key_figures',
                    'content',
                    null,
                    $filteropt
                );
            $content->files = external_util::get_area_files($this->context->id, 'block_key_figures', 'content', false, false);
        }

        return $content;
    }

    /**
     * Serialize and store config data.
     *
     * @param stdClass $data the data to store
     * @param boolean $nolongerused not used
     * @return void
     */
    public function instance_config_save($data, $nolongerused = false): void {
        $config = clone ($data);

        if (isset($config->sub_text) && isset($config->sub_text['text']) && $config->sub_text['text'] != '') {
            $blocktext = $data->sub_text;

            // Inspired by the function file_get_unused_draft_itemid.
            $fs = get_file_storage();
            $itemid = rand(1, 999999999);
            while ($fs->get_area_files($this->context->id, 'block_key_figures', 'content', $itemid)) {
                $itemid = rand(1, 999999999);
            }
            $file = file_save_draft_area_files(
                $blocktext['itemid'],
                $this->context->id,
                'block_key_figures',
                'content',
                $itemid,
                ['subdirs' => true],
                $blocktext['text']
            );

            $file = file_rewrite_pluginfile_urls(
                $file,
                'pluginfile.php',
                $this->context->id,
                'block_key_figures',
                'content',
                $itemid
            );

            // Move embedded files into a proper filearea and adjust HTML links to match.
            $config->sub_text['text'] = $file;
        }

        parent::instance_config_save($config, $nolongerused);
    }

    /**
     * Delete any block-specific data when deleting a block instance.
     *
     * @return bool
     */
    public function instance_delete(): bool {
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_key_figures');
        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     *
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    public function instance_copy($fromid): bool {
        $fromcontext = context_block::instance($fromid);
        $fs = get_file_storage();

        // This extra check if file area is empty adds one query if it is not empty but saves several if it is.
        if (!$fs->is_area_empty($fromcontext->id, 'block_key_figures', 'content', 0, false)) {
            $draftitemid = 0;
            file_prepare_draft_area($draftitemid, $fromcontext->id, 'block_key_figures', 'content', 0, ['subdirs' => true]);
            file_save_draft_area_files(
                $draftitemid,
                $this->context->id,
                'block_key_figures',
                'content',
                0,
                ['subdirs' => true]
            );
        }
        return true;
    }

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     */
    public function get_config_for_external(): object {
        global $CFG;

        // Return all settings for all users since it is safe (no private keys, etc..).
        $instanceconfigs = !empty($this->config) ? $this->config : new stdClass();
        $pluginconfigs = (object) ['allowcssclasses' => $CFG->block_html_allowcssclasses];

        return (object) [
            'instance' => $instanceconfigs,
            'plugin' => $pluginconfigs,
        ];
    }
}
