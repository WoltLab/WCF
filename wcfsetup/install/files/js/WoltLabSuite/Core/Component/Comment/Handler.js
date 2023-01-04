define(["require", "exports", "tslib", "../../Ajax", "../../Dom/Change/Listener", "../../Dom/Util", "../../Helper/Selector", "../../Language", "./Add", "./Response/Add", "../../Ui/Scroll"], function (require, exports, tslib_1, Ajax_1, Listener_1, Util_1, Selector_1, Language_1, Add_1, Add_2, UiScroll) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    UiScroll = tslib_1.__importStar(UiScroll);
    class CommentHandler {
        #container;
        #commentResponseAdd;
        constructor(container) {
            this.#container = container;
            this.#initComments();
            this.#initLoadNextComments();
            this.#initCommentAdd();
        }
        #initCommentAdd() {
            if (this.#container.dataset.canAdd) {
                new Add_1.CommentAdd(this.#container.querySelector(".commentAdd"), parseInt(this.#container.dataset.objectTypeId), parseInt(this.#container.dataset.objectId), (template) => {
                    this.#insertComment(template);
                });
                this.#commentResponseAdd = new Add_2.CommentResponseAdd(this.#container.querySelector(".commentResponseAdd"), (commentId, template) => {
                    this.#insertResponse(commentId, template);
                });
            }
        }
        #initComments() {
            (0, Selector_1.wheneverFirstSeen)("woltlab-core-comment", (element) => {
                element.addEventListener("reply", () => {
                    this.#showAddResponse(element.parentElement, element.commentId);
                });
                element.addEventListener("delete", () => {
                    element.parentElement?.remove();
                });
                this.#initLoadNextResponses(element.parentElement);
            });
        }
        #initLoadNextResponses(comment) {
            const displayedResponses = comment.querySelectorAll(".commentResponse").length;
            const responses = parseInt(comment.dataset.responses);
            if (displayedResponses < responses) {
                const phrase = (0, Language_1.getPhrase)("wcf.comment.response.more", { count: responses - displayedResponses });
                if (!comment.querySelector(".commentLoadNextResponses")) {
                    const li = document.createElement("li");
                    li.classList.add("commentLoadNextResponses");
                    comment.querySelector(".commentResponseList").append(li);
                    const button = document.createElement("button");
                    button.type = "button";
                    button.classList.add("button", "small", "commentLoadNextResponses__button");
                    button.textContent = phrase;
                    li.append(button);
                    button.addEventListener("click", () => {
                        void this.#loadNextResponses(comment);
                    });
                }
                else {
                    comment.querySelector(".commentLoadNextResponses__button").textContent = phrase;
                }
            }
            else {
                comment.querySelector(".commentLoadNextResponses")?.remove();
            }
        }
        async #loadNextResponses(comment, loadAllResponses = false) {
            const button = comment.querySelector(".commentLoadNextResponses__button");
            button.disabled = true;
            const response = (await (0, Ajax_1.dboAction)("loadResponses", "wcf\\data\\comment\\response\\CommentResponseAction")
                .payload({
                data: {
                    commentID: comment.dataset.commentId,
                    lastResponseTime: comment.dataset.lastResponseTime,
                    lastResponseID: comment.dataset.lastResponseId,
                    loadAllResponses: loadAllResponses ? 1 : 0,
                },
            })
                .dispatch());
            const fragment = Util_1.default.createFragmentFromHtml(response.template);
            fragment.querySelectorAll(".commentResponse").forEach((element) => {
                comment.querySelector(`.commentResponse[data-response-id="${element.dataset.responseId}"]`)?.remove();
            });
            comment
                .querySelector(".commentResponseList")
                .insertBefore(fragment, this.#container.querySelector(".commentLoadNextResponses"));
            comment.dataset.lastResponseTime = response.lastResponseTime.toString();
            comment.dataset.lastResponseId = response.lastResponseID.toString();
            this.#initLoadNextResponses(comment);
        }
        #initLoadNextComments() {
            if (this.#displayedComments < this.#totalComments) {
                if (!this.#container.querySelector(".commentLoadNext")) {
                    const li = document.createElement("li");
                    li.classList.add("commentLoadNext", "showMore");
                    this.#container.querySelector(".commentList").append(li);
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
            this.#container
                .querySelector(".commentList")
                .insertBefore(fragment, this.#container.querySelector(".commentLoadNext"));
            this.#container.dataset.lastCommentTime = response.lastCommentTime.toString();
            if (this.#displayedComments < this.#totalComments) {
                button.disabled = false;
            }
            else {
                this.#container.querySelector(".commentLoadNext").hidden = true;
            }
        }
        #showAddResponse(container, commentId) {
            container.append(this.#container.querySelector(".commentResponseAdd"));
            this.#commentResponseAdd.show(commentId);
        }
        #insertComment(template) {
            Util_1.default.insertHtml(template, this.#container.querySelector(".commentAdd"), "after");
            Listener_1.default.trigger();
            const scrollTarget = this.#container.querySelector(".commentAdd").nextElementSibling;
            window.setTimeout(() => {
                UiScroll.element(scrollTarget);
            }, 100);
        }
        #insertResponse(commentId, template) {
            const li = this.#container.querySelector(`.comment[data-comment-id="${commentId}"]`);
            let commentResponseList = li.querySelector(".commentResponseList");
            if (!commentResponseList) {
                const div = document.createElement("div");
                div.classList.add("comment__responses");
                li.append(div);
                commentResponseList = document.createElement("ul");
                commentResponseList.classList.add("containerList", "commentResponseList");
                commentResponseList.dataset.responses = "1";
                div.append(commentResponseList);
            }
            Util_1.default.insertHtml(template, commentResponseList, "append");
            Listener_1.default.trigger();
            const scrollTarget = commentResponseList.firstElementChild;
            window.setTimeout(() => {
                UiScroll.element(scrollTarget);
            }, 100);
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
