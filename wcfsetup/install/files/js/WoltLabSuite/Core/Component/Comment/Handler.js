define(["require", "exports", "tslib", "../../Ajax", "../../Dom/Util", "../../Helper/Selector", "../../Language", "./Add"], function (require, exports, tslib_1, Ajax_1, Util_1, Selector_1, Language_1, Add_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    class CommentHandler {
        #container;
        constructor(container) {
            this.#container = container;
            this.#initComments();
            this.#initLoadNextComments();
            this.#initCommentAdd();
        }
        #initCommentAdd() {
            if (this.#container.dataset.canAdd) {
                new Add_1.CommentAdd(this.#container.querySelector(".commentAdd"));
            }
        }
        #initComments() {
            (0, Selector_1.wheneverFirstSeen)("woltlab-core-comment", (element) => {
                element.addEventListener('reply', () => {
                    console.log('reply clicked');
                });
                element.addEventListener("delete", () => {
                    element.parentElement?.remove();
                });
            });
        }
        #initLoadNextComments() {
            if (this.#displayedComments < this.#totalComments) {
                if (!this.#container.querySelector(".commentLoadNext")) {
                    const li = document.createElement("li");
                    li.classList.add("commentLoadNext", "showMore");
                    this.#container.append(li);
                    const button = document.createElement("button");
                    button.type = "button";
                    button.classList.add("button", "small", "commentLoadNext__button");
                    button.textContent = (0, Language_1.getPhrase)("wcf.comment.more");
                    li.append(button);
                    button.addEventListener("click", () => {
                        void this.#loadNextComments();
                    });
                }
            }
        }
        async #loadNextComments() {
            const button = this.#container.querySelector(".commentLoadNext__button");
            button.disabled = true;
            const response = (await (0, Ajax_1.dboAction)("loadComments", "wcf\\data\\comment\\CommentAction")
                .payload({
                data: {
                    objectID: this.#container.dataset.objectId,
                    objectTypeID: this.#container.dataset.objectTypeId,
                    lastCommentTime: this.#container.dataset.lastCommentTime,
                },
            })
                .dispatch());
            const fragment = Util_1.default.createFragmentFromHtml(response.template);
            this.#container.insertBefore(fragment, this.#container.querySelector(".commentLoadNext"));
            this.#container.dataset.lastCommentTime = response.lastCommentTime.toString();
            if (this.#displayedComments < this.#totalComments) {
                button.disabled = false;
            }
            else {
                this.#container.querySelector(".commentLoadNext").hidden = true;
            }
        }
        get #displayedComments() {
            return this.#container.querySelectorAll(".comment").length;
        }
        get #totalComments() {
            return parseInt(this.#container.dataset.comments);
        }
    }
    function setup(elementId) {
        const element = document.getElementById(elementId);
        if (!element) {
            console.debug(`[Comment.Handler] Unable to find container identified by '${elementId}'`);
            return;
        }
        new CommentHandler(element);
    }
    exports.setup = setup;
});
