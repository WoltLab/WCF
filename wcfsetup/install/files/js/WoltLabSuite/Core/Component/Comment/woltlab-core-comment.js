define(["require", "exports", "tslib", "../../Ajax", "../../Ui/Dropdown/Simple", "../Confirmation"], function (require, exports, tslib_1, Ajax_1, Simple_1, Confirmation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreCommentElement = void 0;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    class WoltlabCoreCommentElement extends HTMLElement {
        connectedCallback() {
            if (this.menu) {
                const enableButton = this.menu.querySelector(".comment__option--enable");
                enableButton?.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#enable();
                });
                const deleteButton = this.menu.querySelector(".comment__option--delete");
                deleteButton?.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#delete();
                });
            }
            const replyButton = this.querySelector(".comment__button--reply");
            replyButton?.addEventListener("click", () => {
                this.dispatchEvent(new CustomEvent("reply"));
            });
        }
        async #enable() {
            await (0, Ajax_1.dboAction)("enable", "wcf\\data\\comment\\CommentAction").objectIds([this.commentId]).dispatch();
            this.querySelector(".comment__status--disabled").hidden = true;
            if (this.menu) {
                this.menu.querySelector(".comment__option--enable").hidden = true;
            }
        }
        async #delete() {
            const result = await (0, Confirmation_1.confirmationFactory)().delete("todo");
            if (result) {
                await (0, Ajax_1.dboAction)("delete", "wcf\\data\\comment\\CommentAction").objectIds([this.commentId]).dispatch();
                this.dispatchEvent(new CustomEvent("delete"));
            }
        }
        get commentId() {
            return parseInt(this.getAttribute("comment-id"));
        }
        get menu() {
            return Simple_1.default.getDropdownMenu(`commentOptions${this.commentId}`);
        }
    }
    exports.WoltlabCoreCommentElement = WoltlabCoreCommentElement;
    window.customElements.define("woltlab-core-comment", WoltlabCoreCommentElement);
    exports.default = WoltlabCoreCommentElement;
});
