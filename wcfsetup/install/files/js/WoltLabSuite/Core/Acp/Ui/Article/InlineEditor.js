/**
 * Handles article trash, restore and delete.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Article/InlineEditor
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Controller/Clipboard", "../../../Core", "../../../Dom/Util", "../../../Event/Handler", "../../../Language", "../../../Ui/Confirmation", "../../../Ui/Dialog", "../../../Ui/Notification"], function (require, exports, tslib_1, Ajax, ControllerClipboard, Core, Util_1, EventHandler, Language, UiConfirmation, Dialog_1, UiNotification) {
    "use strict";
    Ajax = (0, tslib_1.__importStar)(Ajax);
    ControllerClipboard = (0, tslib_1.__importStar)(ControllerClipboard);
    Core = (0, tslib_1.__importStar)(Core);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    EventHandler = (0, tslib_1.__importStar)(EventHandler);
    Language = (0, tslib_1.__importStar)(Language);
    UiConfirmation = (0, tslib_1.__importStar)(UiConfirmation);
    Dialog_1 = (0, tslib_1.__importDefault)(Dialog_1);
    UiNotification = (0, tslib_1.__importStar)(UiNotification);
    const articles = new Map();
    class AcpUiArticleInlineEditor {
        /**
         * Initializes the ACP inline editor for articles.
         */
        constructor(objectId, options) {
            this.options = Core.extend({
                i18n: {
                    defaultLanguageId: 0,
                    isI18n: false,
                    languages: {},
                },
                redirectUrl: "",
            }, options);
            if (objectId) {
                this.initArticle(undefined, ~~objectId);
            }
            else {
                document.querySelectorAll(".jsArticleRow").forEach((article) => this.initArticle(article, 0));
                EventHandler.add("com.woltlab.wcf.clipboard", "com.woltlab.wcf.article", (data) => this.clipboardAction(data));
            }
        }
        /**
         * Reacts to executed clipboard actions.
         */
        clipboardAction(actionData) {
            // only consider events if the action has been executed
            if (actionData.responseData !== null) {
                const callbackFunction = new Map([
                    ["com.woltlab.wcf.article.delete", (articleId) => this.triggerDelete(articleId)],
                    ["com.woltlab.wcf.article.publish", (articleId) => this.triggerPublish(articleId)],
                    ["com.woltlab.wcf.article.restore", (articleId) => this.triggerRestore(articleId)],
                    ["com.woltlab.wcf.article.trash", (articleId) => this.triggerTrash(articleId)],
                    ["com.woltlab.wcf.article.unpublish", (articleId) => this.triggerUnpublish(articleId)],
                ]);
                const triggerFunction = callbackFunction.get(actionData.data.actionName);
                if (triggerFunction) {
                    actionData.responseData.objectIDs.forEach((objectId) => triggerFunction(objectId));
                    UiNotification.show();
                }
            }
            else if (actionData.data.actionName === "com.woltlab.wcf.article.setCategory") {
                const dialog = Dialog_1.default.openStatic("articleCategoryDialog", actionData.data.internalData.template, {
                    title: Language.get("wcf.acp.article.setCategory"),
                });
                const submitButton = dialog.content.querySelector("[data-type=submit]");
                submitButton.addEventListener("click", (ev) => this.submitSetCategory(ev, dialog.content));
            }
        }
        /**
         * Is called, if the set category dialog form is submitted.
         */
        submitSetCategory(event, content) {
            event.preventDefault();
            const innerError = content.querySelector(".innerError");
            const select = content.querySelector("select[name=categoryID]");
            const categoryId = ~~select.value;
            if (categoryId) {
                Ajax.api(this, {
                    actionName: "setCategory",
                    parameters: {
                        categoryID: categoryId,
                        useMarkedArticles: true,
                    },
                });
                if (innerError) {
                    innerError.remove();
                }
                Dialog_1.default.close("articleCategoryDialog");
            }
            else if (!innerError) {
                Util_1.default.innerError(select, Language.get("wcf.global.form.error.empty"));
            }
        }
        /**
         * Initializes an article row element.
         */
        initArticle(article, objectId) {
            let isArticleEdit = false;
            if (!article && ~~objectId > 0) {
                isArticleEdit = true;
                article = undefined;
            }
            else {
                objectId = ~~article.dataset.objectId;
            }
            const scope = article || document;
            const buttonDelete = scope.querySelector(".jsButtonDelete");
            buttonDelete.addEventListener("click", (ev) => this.prompt(ev, objectId, "delete"));
            const buttonRestore = scope.querySelector(".jsButtonRestore");
            buttonRestore.addEventListener("click", (ev) => this.prompt(ev, objectId, "restore"));
            const buttonTrash = scope.querySelector(".jsButtonTrash");
            buttonTrash.addEventListener("click", (ev) => this.prompt(ev, objectId, "trash"));
            if (isArticleEdit) {
                const buttonToggleI18n = scope.querySelector(".jsButtonToggleI18n");
                if (buttonToggleI18n !== null) {
                    buttonToggleI18n.addEventListener("click", (ev) => this.toggleI18n(ev, objectId));
                }
            }
            articles.set(objectId, {
                buttons: {
                    delete: buttonDelete,
                    restore: buttonRestore,
                    trash: buttonTrash,
                },
                element: article,
                isArticleEdit: isArticleEdit,
            });
        }
        /**
         * Prompts a user to confirm the clicked action before executing it.
         */
        prompt(event, objectId, actionName) {
            event.preventDefault();
            const article = articles.get(objectId);
            UiConfirmation.show({
                confirm: () => {
                    this.invoke(objectId, actionName);
                },
                message: article.buttons[actionName].dataset.confirmMessageHtml,
                messageIsHtml: true,
            });
        }
        /**
         * Toggles an article between i18n and monolingual.
         */
        toggleI18n(event, objectId) {
            event.preventDefault();
            const phrase = Language.get("wcf.acp.article.i18n." + (this.options.i18n.isI18n ? "fromI18n" : "toI18n") + ".confirmMessage");
            let html = `<p>${phrase}</p>`;
            // build language selection
            if (this.options.i18n.isI18n) {
                html += `<dl><dt>${Language.get("wcf.acp.article.i18n.source")}</dt><dd>`;
                const defaultLanguageId = this.options.i18n.defaultLanguageId.toString();
                html += Object.entries(this.options.i18n.languages)
                    .map(([languageId, languageName]) => {
                    return `<label><input type="radio" name="i18nLanguage" value="${languageId}" ${defaultLanguageId === languageId ? "checked" : ""}> ${languageName}</label>`;
                })
                    .join("");
                html += "</dd></dl>";
            }
            UiConfirmation.show({
                confirm: (parameters, content) => {
                    let languageId = 0;
                    if (this.options.i18n.isI18n) {
                        const input = content.parentElement.querySelector("input[name='i18nLanguage']:checked");
                        languageId = ~~input.value;
                    }
                    Ajax.api(this, {
                        actionName: "toggleI18n",
                        objectIDs: [objectId],
                        parameters: {
                            languageID: languageId,
                        },
                    });
                },
                message: html,
                messageIsHtml: true,
            });
        }
        /**
         * Invokes the selected action.
         */
        invoke(objectId, actionName) {
            Ajax.api(this, {
                actionName: actionName,
                objectIDs: [objectId],
            });
        }
        /**
         * Handles an article being deleted.
         */
        triggerDelete(articleId) {
            const article = articles.get(articleId);
            if (!article) {
                // The affected article might be hidden by the filter settings.
                return;
            }
            if (article.isArticleEdit) {
                window.location.href = this.options.redirectUrl;
            }
            else {
                const tbody = article.element.parentElement;
                article.element.remove();
                if (tbody.querySelector("tr") === null) {
                    window.location.reload();
                }
            }
        }
        /**
         * Handles publishing an article via clipboard.
         */
        triggerPublish(articleId) {
            const article = articles.get(articleId);
            if (!article) {
                // The affected article might be hidden by the filter settings.
                return;
            }
            if (article.isArticleEdit) {
                // unsupported
            }
            else {
                const notice = article.element.querySelector(".jsUnpublishedArticle");
                notice.remove();
            }
        }
        /**
         * Handles an article being restored.
         */
        triggerRestore(articleId) {
            const article = articles.get(articleId);
            if (!article) {
                // The affected article might be hidden by the filter settings.
                return;
            }
            Util_1.default.hide(article.buttons.delete);
            Util_1.default.hide(article.buttons.restore);
            Util_1.default.show(article.buttons.trash);
            if (article.isArticleEdit) {
                const notice = document.querySelector(".jsArticleNoticeTrash");
                Util_1.default.hide(notice);
            }
            else {
                const icon = article.element.querySelector(".jsIconDeleted");
                icon.remove();
            }
        }
        /**
         * Handles an article being trashed.
         */
        triggerTrash(articleId) {
            const article = articles.get(articleId);
            if (!article) {
                // The affected article might be hidden by the filter settings.
                return;
            }
            Util_1.default.show(article.buttons.delete);
            Util_1.default.show(article.buttons.restore);
            Util_1.default.hide(article.buttons.trash);
            if (article.isArticleEdit) {
                const notice = document.querySelector(".jsArticleNoticeTrash");
                Util_1.default.show(notice);
            }
            else {
                const badge = document.createElement("span");
                badge.className = "badge label red jsIconDeleted";
                badge.textContent = Language.get("wcf.message.status.deleted");
                const h3 = article.element.querySelector(".containerHeadline > h3");
                h3.insertAdjacentElement("afterbegin", badge);
            }
        }
        /**
         * Handles unpublishing an article via clipboard.
         */
        triggerUnpublish(articleId) {
            const article = articles.get(articleId);
            if (!article) {
                // The affected article might be hidden by the filter settings.
                return;
            }
            if (article.isArticleEdit) {
                // unsupported
            }
            else {
                const badge = document.createElement("span");
                badge.className = "badge jsUnpublishedArticle";
                badge.textContent = Language.get("wcf.acp.article.publicationStatus.unpublished");
                const h3 = article.element.querySelector(".containerHeadline > h3");
                const a = h3.querySelector("a");
                h3.insertBefore(badge, a);
                h3.insertBefore(document.createTextNode(" "), a);
            }
        }
        _ajaxSuccess(data) {
            let notificationCallback;
            switch (data.actionName) {
                case "delete":
                    this.triggerDelete(data.objectIDs[0]);
                    break;
                case "restore":
                    this.triggerRestore(data.objectIDs[0]);
                    break;
                case "setCategory":
                case "toggleI18n":
                    notificationCallback = () => window.location.reload();
                    break;
                case "trash":
                    this.triggerTrash(data.objectIDs[0]);
                    break;
            }
            UiNotification.show(undefined, notificationCallback);
            ControllerClipboard.reload();
        }
        _ajaxSetup() {
            return {
                data: {
                    className: "wcf\\data\\article\\ArticleAction",
                },
            };
        }
    }
    Core.enableLegacyInheritance(AcpUiArticleInlineEditor);
    return AcpUiArticleInlineEditor;
});
