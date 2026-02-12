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

        // Use renderer to generate content.
        $renderable = new \block_key_figures\output\block_content($this->config, $this->instance->id);
        $renderer = $this->page->get_renderer('block_key_figures');
        $this->content->text = $renderer->render_block_content($renderable);

        // Load required JavaScript modules.
        $this->page->requires->js_call_amd('block_key_figures/counter', 'init');
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

        $content = new stdClass();
        $content->title = null;
        $content->content = '';
        $content->contentformat = FORMAT_MOODLE;
        $content->footer = '';
        $content->files = [];

        if (!$this->hide_header()) {
            $content->title = $this->title;
        }

        if (isset($this->config->sub_text)) {
            $filteropt = new stdClass();
            if ($this->content_is_trusted()) {
                // Fancy html allowed only on course, category and system blocks.
                $filteropt->noclean = true;
            }

            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config.
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            [$content->content, $content->contentformat] =
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
     * Check if the content is trusted
     *
     * @return bool
     */
    public function content_is_trusted(): bool {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        // Find out if this block is on the profile page.
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // This is exception - page is completely private, nobody else may see content there.
                // That is why we allow JS here.
                return true;
            } else {
                // No JS on public personal pages, it would be a big security issue.
                return false;
            }
        }

        return true;
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
