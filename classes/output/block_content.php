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
 * Block content renderable class
 *
 * @package    block_key_figures
 * @copyright  2023 Jan Eticeo <contact@eticeo.fr>
 * @author     2023 Jan Guevara Gabrielle <gabrielle.guevara@eticeo.fr>
 * @author     2025 Feb Belgrand Laureen <laureen.belgrand@eticeo.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_key_figures\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Block content renderable class
 *
 * @package    block_key_figures
 * @copyright  2026 Jan Eticeo <contact@eticeo.fr>
 * @author     2026 Feb Belgrand Laureen <laureen.belgrand@eticeo.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_content implements renderable, templatable {

    /** @var object Block configuration */
    protected $config;

    /** @var int Block instance ID */
    protected $instanceid;

    /**
     * Constructor.
     *
     * @param object $config Block configuration.
     * @param int $instanceid Block instance ID.
     */
    public function __construct($config, $instanceid) {
        $this->config = $config;
        $this->instanceid = $instanceid;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;

        $styles = $this->get_styles();
        $data = [
            'instanceid' => $this->instanceid,
            'hasstyles' => !empty($styles),
            'styles' => $styles,
            'title' => $this->get_title_data(),
            'blocks' => $this->get_blocks_data(),
            'containerclasses' => $this->get_container_classes(),
        ];

        return $data;
    }

    /**
     * Get CSS styles for the block.
     *
     * @return array
     */
    protected function get_styles(): array {
        $instanceid = '#inst' . $this->instanceid;
        $styles = [];

        $colormap = [
            'title_font_color' => [".block_key_figures_title h2", "color"],
            'subtitle_font_color' => [".block_key_figures_title >*:not(h2):not(.sub_text)", "color"],
            'sub_text_font_color' => [".block_key_figures_title .sub_text p", "color"],
            'background_color' => ["", "background-color"],
            'tile_background_color' => [".sub_block_key_figures", "background-color"],
            'icon_color' => [".sub_block_key_figures .block_title", "color"],
            'number_color' => [".sub_block_key_figures .row-numbers .col-number", "color"],
            'caption_color' => [".sub_block_key_figures .row-numbers .col-number-caption", "color"],
        ];

        foreach ($colormap as $configkey => [$selector, $property]) {
            if (!empty($this->config->$configkey)) {
                if ($selector) {
                    $styles[] = [
                        'selector' => $instanceid . ' ' . $selector,
                        'property' => $property,
                        'value' => $this->config->$configkey,
                    ];
                } else {
                    $styles[] = [
                        'selector' => $instanceid . ', ' . $instanceid . ' .content',
                        'property' => $property,
                        'value' => $this->config->$configkey,
                    ];
                }
            }
        }

        return $styles;
    }

    /**
     * Get title section data.
     *
     * @return array
     */
    protected function get_title_data(): array {
        $title = !empty($this->config->title) ? $this->config->title : '';
        $subtitle = !empty($this->config->subtitle) ? $this->config->subtitle : '';
        $subtext = !empty($this->config->sub_text['text']) ? $this->config->sub_text['text'] : '';

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'hassubtext' => !empty($subtext),
            'subtext' => $subtext,
        ];
    }

    /**
     * Get container CSS classes.
     *
     * @return string
     */
    protected function get_container_classes(): string {
        $classes = 'block_key_figures_container';
        if (!empty($this->config->tile_border)) {
            $classes .= ' border_display';
        }
        return $classes;
    }

    /**
     * Get blocks data.
     *
     * @return array
     */
    protected function get_blocks_data(): array {
        $blocks = [];
        $blocksnumber = !empty($this->config->block_number) ? (int) $this->config->block_number : 0;
        $col = $this->get_column_class($blocksnumber);

        for ($blocknum = 1; $blocknum <= $blocksnumber; $blocknum++) {
            $flexdirection = $this->config->flex_direction[$blocknum] ?? 'row';
            $blocks[] = $this->get_single_block_data($blocknum, $col, $flexdirection);
        }

        return $blocks;
    }

    /**
     * Get single block data.
     *
     * @param int $blocknum Block number.
     * @param string $col Column class.
     * @param string $flexdirection Flex direction.
     * @return array
     */
    protected function get_single_block_data(int $blocknum, string $col, string $flexdirection): array {
        $blockdata = [
            'blocknum' => $blocknum,
            'colclass' => $col,
            'flexdirection' => $flexdirection,
            'hasicon' => !empty($this->config->icon[$blocknum]),
            'icon' => $this->config->icon[$blocknum] ?? '',
            'numbers' => $this->get_numbers_data($blocknum, $flexdirection),
            'iscolumn' => $flexdirection === 'column',
        ];

        return $blockdata;
    }

    /**
     * Get numbers data for a block.
     *
     * @param int $blocknum Block number.
     * @param string $flexdirection Flex direction.
     * @return array
     */
    protected function get_numbers_data(int $blocknum, string $flexdirection): array {
        $numbers = [];
        $linesnumber = $this->config->line_number[$blocknum] ?? 0;

        for ($linenum = 1; $linenum <= $linesnumber; $linenum++) {
            $number = $this->config->number[$blocknum][$linenum] ?? '';
            $numbercaption = $this->config->number_caption[$blocknum][$linenum] ?? '';
            $idcolnumber = 'number_' . $this->instanceid . '_' . $blocknum . '_' . $linenum;

            $numbers[] = [
                'id' => $idcolnumber,
                'number' => $number,
                'caption' => $numbercaption,
            ];
        }

        return $numbers;
    }

    /**
     * Get Bootstrap column class based on number of blocks.
     *
     * @param int $blocksnumber Number of blocks.
     * @return string
     */
    protected function get_column_class(int $blocksnumber): string {
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
}

