define(["require", "exports", "../../Ajax", "./Add"], function (require, exports, Ajax_1, Add_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    class CommentHandler {
        #container;
        constructor(container) {
            this.#container = container;
            this.#initComments();
            this.#initCommentAdd();
        }
        #initCommentAdd() {
            if (this.#container.dataset.canAdd) {
                new Add_1.CommentAdd(this.#container.querySelector(".commentAdd"));
            }
        }
        #initComments() { }
        #initComment(commentId, comment) {
            /*if (this._container.data('canAdd')) {
                    this._initAddResponse(commentID, comment);
                }*/
            const enableButton = comment.querySelector(".jsCommentEnableButton");
            if (enableButton) {
                enableButton.addEventListener("click", (event) => {
                    event.preventDefault();
                    void this.#enableComment(comment);
                });
            }
            const deleteButton = comment.querySelector(".jsCommentDeleteButton");
            if (deleteButton) {
                deleteButton.addEventListener("click", (event) => {
                    event.preventDefault();
                });
            }
            const replyButton = comment.querySelector('.jsCommentReplyButton');
            if (replyButton) {
                replyButton.addEventListener("click", (event) => {
                    //this._showAddResponse();
                });
            }
        }
        async #enableComment(comment) {
            await (0, Ajax_1.dboAction)("enable", "wcf\\data\\comment\\CommentAction")
                .objectIds([parseInt(comment.dataset.objectId)])
                .dispatch();
            comment.dataset.isDisabled = "";
            comment.querySelector(".jsIconDisabled").hidden = true;
            comment.querySelector(".jsCommentEnableButton").hidden = true;
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
