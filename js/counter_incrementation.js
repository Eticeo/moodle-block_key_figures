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
 * Key Figures Block Counter Animation
 *
 * This file handles the counter animation for key figures in the block
 *
 * @package    block_key_figures
 * @copyright  2023 Jan Eticeo <contact@eticeo.fr>
 * @author     2023 Jan Guevara Gabrielle <gabrielle.guevara@eticeo.fr>
 * @author     2025 Feb Belgrand Laureen <laureen.belgrand@eticeo.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialize counter animations when DOM is loaded
 */
document.addEventListener("DOMContentLoaded", function() {
    document
        .querySelectorAll(".block_key_figures.block .col-number")
        .forEach(function(element) {
            const finalresult = element.innerHTML;
            let newresult = finalresult;

            // Extract and process numbers
            const allregexnums = {};
            const allregexsteps = {};
            const matchesnum = finalresult.match(/[0-9]+/g);
            const numberblockid = element.id;

            let index = null;
            if (matchesnum) {
                matchesnum.forEach(function(match, num) {
                    const regexnum = new RegExp(match, "g");
                    index = "##regex" + num + "##";
                    newresult = newresult.replace(regexnum, index);
                    allregexnums[index] = parseInt(match, 10);
                    allregexsteps[index] = blockKeyFiguresGetStep(
                        parseInt(match, 10)
                    );
                });
            }

            // Start animation if numbers were found
            if (index !== null) {
                blockKeyFiguresIncrement(
                    numberblockid,
                    0,
                    allregexnums,
                    allregexsteps,
                    finalresult,
                    newresult
                );
            }
        });
});

/**
 * Calculate the step size for counter animation
 *
 * @param {number} number The target number to reach
 * @returns {number} The step size for the animation
 */
function blockKeyFiguresGetStep(number) {
    const step = Math.floor(number / 100);
    return step > 0 ? step : 1;
}

/**
 * Animate the counter increment
 *
 * @param {string} numberblockid ID of the number block element
 * @param {number} num Current counter value
 * @param {Object} allregexnums Object mapping regex placeholders to their target values
 * @param {Object} allregexsteps Object mapping regex placeholders to their step values
 * @param {string} finalresult The final text to display
 * @param {string} newresult The intermediate text with placeholders
 */
function blockKeyFiguresIncrement(
    numberblockid,
    num,
    allregexnums,
    allregexsteps,
    finalresult,
    newresult
) {
    num += 1;
    let isfinalresult = true;
    let newresultnum = newresult;

    // Update each number in the text
    for (let regexnum in allregexnums) {
        const reg = new RegExp(regexnum, "g");
        let replacenum = num * allregexsteps[regexnum];

        if (replacenum <= allregexnums[regexnum]) {
            isfinalresult = false;
        } else {
            replacenum = allregexnums[regexnum];
        }
        newresultnum = newresultnum.replace(reg, replacenum);
    }

    // Update the element
    const element = document.getElementById(numberblockid);
    if (element) {
        element.innerHTML = newresultnum;
    }

    // Continue animation if not finished
    if (!isfinalresult) {
        setTimeout(function() {
            blockKeyFiguresIncrement(
                numberblockid,
                num,
                allregexnums,
                allregexsteps,
                finalresult,
                newresult
            );
        }, 10);
    }
}
