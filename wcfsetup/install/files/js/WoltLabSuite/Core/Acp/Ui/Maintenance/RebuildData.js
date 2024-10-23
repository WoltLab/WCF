/**
 * Handles worker execution for the RebuildDataPage.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../Worker", "../../../Language"], function (require, exports, tslib_1, Worker_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.register = register;
    exports.runAllWorkers = runAllWorkers;
    Worker_1 = tslib_1.__importDefault(Worker_1);
    Language = tslib_1.__importStar(Language);
    const workers = new Map();
    function register(button) {
        if (!button.dataset.className) {
            throw new Error(`Missing 'data-class-name' attribute.`);
        }
        workers.set(button, parseInt(button.dataset.nicevalue, 10));
        button.addEventListener("click", function (event) {
            event.preventDefault();
            void runWorker(button);
        });
    }
    async function runAllWorkers() {
        const sorted = Array.from(workers)
            .sort(([, a], [, b]) => {
            return a - b;
        })
            .map(([el]) => el);
        let i = 1;
        for (const worker of sorted) {
            await runWorker(worker, `${worker.textContent} (${i++} / ${sorted.length})`, true);
        }
    }
    async function runWorker(button, dialogTitle = button.textContent, implicitContinue = false) {
        return new Promise((resolve, reject) => {
            new Worker_1.default({
                dialogId: "cache",
                dialogTitle,
                className: button.dataset.className,
                implicitContinue,
                callbackAbort() {
                    reject();
                },
                callbackSuccess() {
                    let span = button.nextElementSibling;
                    if (span && span.nodeName === "SPAN") {
                        span.remove();
                    }
                    span = document.createElement("span");
                    span.innerHTML = `<fa-icon name="check" solid></fa-icon> ${Language.get("wcf.acp.worker.success")}`;
                    button.parentNode.insertBefore(span, button.nextElementSibling);
                    resolve();
                },
            });
        });
    }
});
