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
 * Key Figures Block Counter Animation.
 *
 * This module handles the counter animation for key figures in the block.
 *
 * @module     block_key_figures/counter
 * @package    block_key_figures
 * @copyright  2026 Jan Eticeo <contact@eticeo.fr>
 * @author     2026 Feb Belgrand Laureen <laureen.belgrand@eticeo.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialize counter animations for all key figure blocks.
 */
export const init = () => {
    // Use a function to ensure DOM is ready.
    const initializeCounters = () => {
        const elements = document.querySelectorAll(".block_key_figures.block .col-number");
        
        elements.forEach((element) => {
            const finalresult = element.innerHTML;
            let newresult = finalresult;

            // Extract and process numbers.
            const allregexnums = {};
            const allregexsteps = {};
            const matchesnum = finalresult.match(/[0-9]+/g);
            const numberblockid = element.id;

            let index = null;
            if (matchesnum) {
                matchesnum.forEach((match, num) => {
                    const regexnum = new RegExp(match, "g");
                    index = "##regex" + num + "##";
                    newresult = newresult.replace(regexnum, index);
                    allregexnums[index] = parseInt(match, 10);
                    allregexsteps[index] = getStep(parseInt(match, 10));
                });
            }

            // Start animation if numbers were found.
            if (index !== null) {
                increment(
                    numberblockid,
                    0,
                    allregexnums,
                    allregexsteps,
                    finalresult,
                    newresult
                );
            }
        });
    };

    // Check if DOM is already loaded.
    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", initializeCounters);
    } else {
        // DOM is already loaded.
        initializeCounters();
    }
};

/**
 * Calculate the step size for counter animation.
 *
 * @param {number} number The target number to reach.
 * @returns {number} The step size for the animation.
 */
const getStep = (number) => {
    const step = Math.floor(number / 100);
    return step > 0 ? step : 1;
};

/**
 * Animate the counter increment.
 *
 * @param {string} numberblockid ID of the number block element.
 * @param {number} num Current counter value.
 * @param {Object} allregexnums Object mapping regex placeholders to their target values.
 * @param {Object} allregexsteps Object mapping regex placeholders to their step values.
 * @param {string} finalresult The final text to display.
 * @param {string} newresult The intermediate text with placeholders.
 */
const increment = (
    numberblockid,
    num,
    allregexnums,
    allregexsteps,
    finalresult,
    newresult
) => {
    num += 1;
    let isfinalresult = true;
    let newresultnum = newresult;

    // Update each number in the text.
    for (const regexnum in allregexnums) {
        if (Object.prototype.hasOwnProperty.call(allregexnums, regexnum)) {
            const reg = new RegExp(regexnum, "g");
            let replacenum = num * allregexsteps[regexnum];

            if (replacenum <= allregexnums[regexnum]) {
                isfinalresult = false;
            } else {
                replacenum = allregexnums[regexnum];
            }
            newresultnum = newresultnum.replace(reg, replacenum);
        }
    }

    // Update the element.
    const element = document.getElementById(numberblockid);
    if (element) {
        element.innerHTML = newresultnum;
    }

    // Continue animation if not finished.
    if (!isfinalresult) {
        setTimeout(() => {
            increment(
                numberblockid,
                num,
                allregexnums,
                allregexsteps,
                finalresult,
                newresult
            );
        }, 10);
    }
};

