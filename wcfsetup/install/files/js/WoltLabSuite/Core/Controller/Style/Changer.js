/**
 * Dialog based style changer.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "../../Language", "WoltLabSuite/Core/Api/Styles/ChangeStyle", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Api/Styles/GetStyleChooser"], function (require, exports, Language_1, ChangeStyle_1, Dialog_1, PromiseMutex_1, GetStyleChooser_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    exports.showDialog = showDialog;
    class ControllerStyleChanger {
        /**
         * Adds the style changer to the bottom navigation.
         */
        constructor() {
            document.querySelectorAll(".jsButtonStyleChanger").forEach((link) => {
                link.addEventListener("click", (0, PromiseMutex_1.promiseMutex)((ev) => this.showDialog(ev)));
            });
        }
        /**
         * Loads and displays the style change dialog.
         */
        async showDialog(event) {
            event.preventDefault();
            const response = await (0, GetStyleChooser_1.getStyleChooser)();
            if (!response.ok) {
                throw new Error("Failed to load style chooser.");
            }
            const dialog = (0, Dialog_1.dialogFactory)().fromHtml(response.value).withoutControls();
            dialog.content.querySelectorAll(".styleList > li").forEach((style) => {
                style.classList.add("pointer");
                style.addEventListener("click", (ev) => this.#click(ev));
            });
            dialog.show((0, Language_1.getPhrase)("wcf.style.changeStyle"));
        }
        /**
         * Changes the style and reloads current page.
         */
        async #click(event) {
            event.preventDefault();
            const listElement = event.currentTarget;
            const styleId = parseInt(listElement.dataset.styleId, 10);
            const response = await (0, ChangeStyle_1.changeStyle)(styleId);
            if (!response.ok) {
                throw new Error("Failed to change style.");
            }
            window.location.reload();
        }
    }
    let controllerStyleChanger;
    /**
     * Adds the style changer to the bottom navigation.
     */
    function setup() {
        if (!controllerStyleChanger) {
            controllerStyleChanger = new ControllerStyleChanger();
        }
    }
    /**
     * Loads and displays the style change dialog.
     */
    function showDialog(event) {
        void controllerStyleChanger.showDialog(event);
    }
});
