define(["require", "exports", "tslib", "../../../Ajax", "../../../Ui/Dropdown/Simple", "../../Confirmation"], function (require, exports, tslib_1, Ajax_1, Simple_1, Confirmation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreCommentResponseElement = void 0;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class WoltlabCoreCommentResponseElement extends HTMLElement {
        connectedCallback() {
            if (this.menu) {
                const enableButton = this.menu.querySelector(".commentResponse__option--enable");
                enableButton?.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#enable();
                });
                const deleteButton = this.menu.querySelector(".commentResponse__option--delete");
                deleteButton?.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#delete();
                });
            }
        }
        async #enable() {
            await (0, Ajax_1.dboAction)("enable", "wcf\\data\\comment\\response\\CommentResponseAction")
                .objectIds([this.responseId])
                .dispatch();
            this.querySelector(".commentResponse__status--disabled").hidden = true;
            if (this.menu) {
                this.menu.querySelector(".commentResponse__option--enable").hidden = true;
            }
        }
        async #delete() {
            const result = await (0, Confirmation_1.confirmationFactory)().delete("todo");
            if (result) {
                await (0, Ajax_1.dboAction)("delete", "wcf\\data\\comment\\response\\CommentResponseAction")
                    .objectIds([this.responseId])
                    .dispatch();
                this.dispatchEvent(new CustomEvent("delete"));
            }
        }
        get responseId() {
            return parseInt(this.getAttribute("response-id"));
        }
        get menu() {
            return Simple_1.default.getDropdownMenu(`commentResponseOptions${this.responseId}`);
        }
    }
    exports.WoltlabCoreCommentResponseElement = WoltlabCoreCommentResponseElement;
    window.customElements.define("woltlab-core-comment-response", WoltlabCoreCommentResponseElement);
    exports.default = WoltlabCoreCommentResponseElement;
});
