/**
 * Handles the change of the show order of elements.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "tslib", "../Api/GetObject", "../Api/PostObject", "../Helper/PromiseMutex", "../Language", "./Dialog", "sortablejs", "./Snackbar"], function (require, exports, tslib_1, GetObject_1, PostObject_1, PromiseMutex_1, Language_1, Dialog_1, sortablejs_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    sortablejs_1 = tslib_1.__importDefault(sortablejs_1);
    async function showDialog(endpoint) {
        const items = await getItems(endpoint);
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(getHtml(items)).asPrompt();
        dialog.show((0, Language_1.getPhrase)("wcf.global.changeShowOrder"));
        const sortable = new sortablejs_1.default(dialog.content.querySelector(".sortableList"), {
            direction: "vertical",
            animation: 150,
            fallbackOnBody: true,
            dataIdAttr: "data-object-id",
            draggable: "li",
            handle: ".sortableList__handle",
        });
        dialog.addEventListener("primary", () => {
            void saveItems(endpoint, sortable.toArray().map(Number)).then(() => {
                (0, Snackbar_1.showDefaultSuccessSnackbar)().addEventListener("snackbar:close", () => {
                    window.location.reload();
                });
            });
        });
    }
    async function getItems(endpoint) {
        return (await (0, GetObject_1.getObject)(`${window.WSC_RPC_API_URL}${endpoint}`)).unwrap();
    }
    async function saveItems(endpoint, values) {
        await (0, PostObject_1.postObject)(`${window.WSC_RPC_API_URL}${endpoint}`, { values });
    }
    function getHtml(items) {
        const list = document.createElement("ol");
        list.classList.add("sortableList");
        items.forEach((item) => {
            const listItem = document.createElement("li");
            listItem.dataset.objectId = item.id.toString();
            listItem.textContent = item.label;
            const icon = document.createElement("fa-icon");
            icon.setIcon("grip-vertical");
            const handle = document.createElement("span");
            handle.append(icon);
            handle.classList.add("sortableList__handle");
            listItem.prepend(handle);
            list.append(listItem);
        });
        return list.outerHTML;
    }
    function setup(button, endpoint) {
        button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => showDialog(endpoint)));
    }
});
