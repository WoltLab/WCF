/**
 * Handles the button to search for package updates.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Api/Packages/Updates/SearchForUpdates", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Language"], function (require, exports, SearchForUpdates_1, Dialog_1, PromiseMutex_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    let noResultsDialog = undefined;
    async function searchForUpdates() {
        if (noResultsDialog !== undefined) {
            noResultsDialog.show((0, Language_1.getPhrase)("wcf.acp.package.searchForUpdates"));
            return;
        }
        const result = await (0, SearchForUpdates_1.searchForUpdates)();
        if (!result.ok && result.error.param === "benchmark") {
            const message = document.createElement("p");
            message.textContent = result.error.code;
            (0, Dialog_1.dialogFactory)().fromElement(message).asAlert().show((0, Language_1.getPhrase)("wcf.global.error.title"));
            return;
        }
        const response = result.unwrap();
        if (typeof window._trackSearchForUpdates === "function") {
            window._trackSearchForUpdates({ returnValues: response });
            return;
        }
        if (response.url !== "") {
            window.location.href = response.url;
            return;
        }
        const content = document.createElement("p");
        content.textContent = (0, Language_1.getPhrase)("wcf.acp.package.searchForUpdates.noResults");
        noResultsDialog = (0, Dialog_1.dialogFactory)().fromElement(content).asAlert();
        noResultsDialog.show((0, Language_1.getPhrase)("wcf.acp.package.searchForUpdates"));
    }
    function setup(button) {
        const search = (0, PromiseMutex_1.promiseMutex)(() => {
            button.disabled = true;
            return searchForUpdates().finally(() => {
                button.disabled = false;
            });
        });
        button.addEventListener("click", () => {
            search();
        });
        if (new URL(window.location.href).searchParams.has("searchForUpdates")) {
            search();
        }
    }
});
