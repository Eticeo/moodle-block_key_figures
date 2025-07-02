/**
 * Function call in php
 *
 * @param instid int : id of block instance
 * @param waitModal bool : true if we need to wait modal to call js. False if not (ex: load param is tab)
 */
export const init = (instid, waitModal) => {
    // Lines to give window functions needed.
    window.block_key_figures_manage_lines = block_key_figures_manage_lines;
    window.block_key_figures_change_line = block_key_figures_change_line;

    if (waitModal) {
        // Call specific function.
        block_key_figures_waitmodal(instid);
    } else {
        // Go on.
        block_key_figures_on_open();
    }
};

/**
 * Waits for the modal to be opened and then runs the init function
 *
 * @param instid int : id of block instance
 */
function block_key_figures_waitmodal(instid) {
    // We need to wait specific element (which is in modal) to run init.
    block_key_figures_waitForElm("input[name='blockid'][value='" + instid + "']").then((elm) => {
        // Element is here, run init.
        block_key_figures_on_open();

        // And now wait modal closed to rerun the waitmodal.
        block_key_figures_waitdestroymodal(instid);
    });
}

/**
 * Needed in case we open, close then reopen modal
 *
 * @param instid int : id of block instance
 */
function block_key_figures_waitdestroymodal(instid) {
    block_key_figures_waitForNotElm("input[name='blockid'][value='" + instid + "']").then((elm) => {
        block_key_figures_on_open();
        block_key_figures_waitmodal(instid);
    });
}

/**
 * Function to run when the modal is opened
 */
function block_key_figures_on_open() {
    document.querySelectorAll('[id^="id_config_block_number"]').forEach(function(element) {
        block_key_figures_manage_lines(element, 'id_configheader');

        element.addEventListener('change', function() {
            block_key_figures_manage_lines(element, 'id_configheader');
        });
    });

    document.querySelectorAll('[id^="id_config_line_number_"]').forEach(function(element) {
        block_key_figures_change_line(element);

        element.addEventListener('change', function() {
            block_key_figures_change_line(element);
        });
    });
}

/**
 * Function wait specific element are NOT here to continue
 *
 * @param selector string: the specific element selector
 * @returns {Promise}
 */
function block_key_figures_waitForNotElm(selector) {
    return new Promise(resolve => {
        if (!document.querySelector(selector)) {
            return resolve(document.querySelector(selector));
        }

        const observer = new MutationObserver(mutations => {
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
}

/**
 * Function wait specific element are here to continue
 *
 * @param selector string: the specific element selector
 * @returns {Promise}
 */
function block_key_figures_waitForElm(selector) {
    return new Promise(resolve => {
        if (document.querySelector(selector)) {
            return resolve(document.querySelector(selector));
        }

        const observer = new MutationObserver(mutations => {
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
}

/**
 * Check the current number of block and hide the blocks settings that we don't need,
 * and show that we need
 *
 * @param selectSelector    | string or object, selector of the number of line to show
 * @param targetId          | string id of the lines to show/hide
 */
function block_key_figures_manage_lines(element, targetId) {
    var blockNumber = parseInt(element.value);

    document.querySelectorAll('[id^="' + targetId + '"]').forEach(function(element) {
        let match = element.id.match(new RegExp(targetId + "(\\d+)_"));
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
 * @param object   | object
 */
function block_key_figures_change_line(object) {
    var blockNum = object.id;
    blockNum = parseInt(blockNum.replace('id_config_line_number_', ''));

    block_key_figures_manage_lines(object, 'fitem_id_config_number_' + blockNum + '_');
    block_key_figures_manage_lines(object, 'fitem_id_config_number_caption_' + blockNum + '_');
}
