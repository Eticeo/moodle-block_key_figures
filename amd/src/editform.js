/**
 * Function call in php
 *
 * @param {number} instid - id of block instance
 * @param {boolean} waitModal - true if we need to wait modal to call js. False if not (ex: load param is tab)
 */
export const init = (instid, waitModal) => {
    // Lines to give window functions needed.
    window.blockKeyFiguresManageLines = blockKeyFiguresManageLines;
    window.blockKeyFiguresChangeLine = blockKeyFiguresChangeLine;

    if (waitModal) {
        // Call specific function.
        blockKeyFiguresWaitModal(instid);
    } else {
        // Go on.
        blockKeyFiguresOnOpen();
    }
};

/**
 * Waits for the modal to be opened and then runs the init function
 *
 * @param {number} instid - id of block instance
 * @returns {Promise<boolean>} - true if the modal is opened, false if not
 */
function blockKeyFiguresWaitModal(instid) {
    // We need to wait specific element (which is in modal) to run init.
    blockKeyFiguresWaitForElm("input[name='blockid'][value='" + instid + "']").then(() => {
        // Element is here, run init.
        blockKeyFiguresOnOpen();
        // And now wait modal closed to rerun the waitmodal.
        blockKeyFiguresWaitDestroyModal(instid);

        return true;

    }).catch(error => {
        return false;
    });
}

/**
 * Needed in case we open, close then reopen modal
 *
 * @param {number} instid - id of block instance
 * @returns {Promise<boolean>} - true if the modal is destroyed, false if not
 */
function blockKeyFiguresWaitDestroyModal(instid) {
    blockKeyFiguresWaitForNotElm("input[name='blockid'][value='" + instid + "']").then(() => {
        blockKeyFiguresOnOpen();
        blockKeyFiguresWaitModal(instid);

        return true;

    }).catch(error => {
        return false;
    });
}

/**
 * Function to run when the modal is opened
 */
function blockKeyFiguresOnOpen() {
    document.querySelectorAll('[id^="id_config_block_number"]').forEach(function(element) {
        blockKeyFiguresManageLines(element, 'id_configheader');

        element.addEventListener('change', function() {
            blockKeyFiguresManageLines(element, 'id_configheader');
        });
    });

    document.querySelectorAll('[id^="id_config_line_number_"]').forEach(function(element) {
        blockKeyFiguresChangeLine(element);

        element.addEventListener('change', function() {
            blockKeyFiguresChangeLine(element);
        });
    });
}

/**
 * Function wait specific element are NOT here to continue
 *
 * @param {string} selector - the specific element selector
 * @returns {Promise}
 */
function blockKeyFiguresWaitForNotElm(selector) {
    return new Promise(resolve => {
        if (!document.querySelector(selector)) {
            return resolve(document.querySelector(selector));
        }

        const observer = new MutationObserver(() => {
            if (!document.querySelector(selector)) {
                resolve(document.querySelector(selector));
                observer.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    return false;
}

/**
 * Function wait specific element are here to continue
 *
 * @param {string} selector - the specific element selector
 * @returns {Promise}
 */
function blockKeyFiguresWaitForElm(selector) {
    return new Promise(resolve => {
        if (document.querySelector(selector)) {
            return resolve(document.querySelector(selector));
        }

        const observer = new MutationObserver(() => {
            if (document.querySelector(selector)) {
                resolve(document.querySelector(selector));
                observer.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    return false;
}

/**
 * Check the current number of block and hide the blocks settings that we don't need,
 * and show that we need
 *
 * @param {string} element - element to manage
 * @param {string} targetid - id of the lines to show/hide
 */
function blockKeyFiguresManageLines(element, targetid) {
    let blockNumber = parseInt(element.value);

    document.querySelectorAll('[id^="' + targetid + '"]').forEach(function(element) {
        let match = element.id.match(new RegExp(targetid + "(\\d+)_"));
        if (match) {
            let num = parseInt(match[1]);
            if (num > blockNumber) {
                element.style.display = 'none';
            } else {
                element.style.display = '';
            }
        }
    });
}

/**
 * Function to manage the lines of the block key figures
 *
 * @param {object} object - object
 */
function blockKeyFiguresChangeLine(object) {
    let blocknum = object.id;
    blocknum = parseInt(blocknum.replace('id_config_line_number_', ''));

    blockKeyFiguresManageLines(object, 'fitem_id_config_number_' + blocknum + '_');
    blockKeyFiguresManageLines(object, 'fitem_id_config_number_caption_' + blocknum + '_');
}
