/**
 * Handles article trash, restore and delete.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Article/InlineEditor
 */
define(['Ajax', 'Core', 'Dictionary', 'Dom/Util', 'EventHandler', 'Language', 'Ui/Confirmation', 'Ui/Dialog', 'Ui/Notification', 'WoltLabSuite/Core/Controller/Clipboard'], function (Ajax, Core, Dictionary, DomUtil, EventHandler, Language, UiConfirmation, UiDialog, UiNotification, ControllerClipboard) {
    "use strict";
    var _articles = new Dictionary();
    /**
     * @constructor
     */
    function AcpUiArticleInlineEditor(objectId, options) { this.init(objectId, options); }
    AcpUiArticleInlineEditor.prototype = {
        /**
         * Initializes the ACP inline editor for articles.
         *
         * @param       {int}           objectId        article id, equals 0 on the article list, but is non-zero when editing a single article
         * @param       {Object}        options         list of configuration options
         */
        init: function (objectId, options) {
            this._options = Core.extend({
                i18n: {
                    defaultLanguageId: 0,
                    isI18n: false,
                    languages: {},
                },
                redirectUrl: ''
            }, options);
            if (objectId) {
                this._initArticle(null, objectId);
            }
            else {
                elBySelAll('.jsArticleRow', undefined, this._initArticle.bind(this));
                EventHandler.add('com.woltlab.wcf.clipboard', 'com.woltlab.wcf.article', this._clipboardAction.bind(this));
            }
        },
        /**
         * Reacts to executed clipboard actions.
         *
         * @param	{object<string, *>}	actionData	data of the executed clipboard action
         */
        _clipboardAction: function (actionData) {
            // only consider events if the action has been executed
            if (actionData.responseData !== null) {
                var triggerFunction;
                switch (actionData.data.actionName) {
                    case 'com.woltlab.wcf.article.delete':
                        triggerFunction = this._triggerDelete;
                        break;
                    case 'com.woltlab.wcf.article.publish':
                        triggerFunction = this._triggerPublish;
                        break;
                    case 'com.woltlab.wcf.article.restore':
                        triggerFunction = this._triggerRestore;
                        break;
                    case 'com.woltlab.wcf.article.trash':
                        triggerFunction = this._triggerTrash;
                        break;
                    case 'com.woltlab.wcf.article.unpublish':
                        triggerFunction = this._triggerUnpublish;
                        break;
                }
                if (triggerFunction) {
                    for (var i = 0, length = actionData.responseData.objectIDs.length; i < length; i++) {
                        triggerFunction(actionData.responseData.objectIDs[i]);
                    }
                    UiNotification.show();
                }
            }
            else if (actionData.data.actionName === 'com.woltlab.wcf.article.setCategory') {
                try {
                    UiDialog.getDialog('articleCategoryDialog');
                    UiDialog.openStatic('articleCategoryDialog');
                }
                catch (e) {
                    UiDialog.openStatic('articleCategoryDialog', actionData.data.internalData.template, {
                        title: Language.get('wcf.acp.article.setCategory')
                    });
                    elBySel('[data-type=submit]', UiDialog.getDialog('articleCategoryDialog').content).addEventListener('click', this._submitSetCategory.bind(this));
                }
            }
        },
        /**
         * Is called, if the set category dialog form is submitted.
         *
         * @param	{Event}		event		form submit button click event
         */
        _submitSetCategory: function (event) {
            var dialog = UiDialog.getDialog('articleCategoryDialog').content;
            var innerErrors = elByClass('innerError', dialog);
            var select = elBySel('select[name=categoryID]', dialog);
            var categoryId = ~~elBySel('select[name=categoryID]', event.currentTarget.parentNode.parentNode).value;
            if (categoryId) {
                Ajax.api(this, {
                    actionName: 'setCategory',
                    parameters: {
                        categoryID: categoryId,
                        useMarkedArticles: true
                    }
                });
                if (innerErrors.length === 1) {
                    elRemove(innerErrors.item(0));
                }
                UiDialog.close('articleCategoryDialog');
            }
            else if (innerErrors.length === 0) {
                elInnerError(select, Language.get('wcf.global.form.error.empty'));
            }
        },
        /**
         * Initializes an article row element.
         *
         * @param       {Element}       article         article row element
         * @param       {int}           objectId        optional article id
         * @protected
         */
        _initArticle: function (article, objectId) {
            var isArticleEdit = false;
            if (!article && ~~objectId > 0) {
                isArticleEdit = true;
                article = undefined;
            }
            else {
                objectId = ~~elData(article, 'object-id');
            }
            var buttonDelete = elBySel('.jsButtonDelete', article);
            buttonDelete.addEventListener(WCF_CLICK_EVENT, this._prompt.bind(this, objectId, 'delete'));
            var buttonRestore = elBySel('.jsButtonRestore', article);
            buttonRestore.addEventListener(WCF_CLICK_EVENT, this._prompt.bind(this, objectId, 'restore'));
            var buttonTrash = elBySel('.jsButtonTrash', article);
            buttonTrash.addEventListener(WCF_CLICK_EVENT, this._prompt.bind(this, objectId, 'trash'));
            if (isArticleEdit) {
                var buttonToggleI18n = elBySel('.jsButtonToggleI18n', article);
                if (buttonToggleI18n !== null)
                    buttonToggleI18n.addEventListener(WCF_CLICK_EVENT, this._toggleI18n.bind(this, objectId));
            }
            _articles.set(objectId, {
                buttons: {
                    delete: buttonDelete,
                    restore: buttonRestore,
                    trash: buttonTrash
                },
                element: article,
                isArticleEdit: isArticleEdit
            });
        },
        /**
         * Prompts a user to confirm the clicked action before executing it.
         *
         * @param       {int}           objectId        article id
         * @param       {string}        actionName      action name
         * @param       {Event}         event           event object
         * @protected
         */
        _prompt: function (objectId, actionName, event) {
            event.preventDefault();
            var article = _articles.get(objectId);
            UiConfirmation.show({
                confirm: (function () { this._invoke(objectId, actionName); }).bind(this),
                message: elData(article.buttons[actionName], 'confirm-message-html'),
                messageIsHtml: true
            });
        },
        /**
         * Toggles an article between i18n and monolingual.
         *
         * @param       {int}           objectId        article id
         * @param       {Event}         event           event object
         * @protected
         */
        _toggleI18n: function (objectId, event) {
            event.preventDefault();
            var html = '<p>' + Language.get('wcf.acp.article.i18n.' + (this._options.i18n.isI18n ? 'fromI18n' : 'toI18n') + '.confirmMessage') + '</p>';
            // build language selection
            if (this._options.i18n.isI18n) {
                html += '<dl><dt>' + Language.get('wcf.acp.article.i18n.source') + '</dt><dd>';
                for (var languageId in this._options.i18n.languages) {
                    if (this._options.i18n.languages.hasOwnProperty(languageId)) {
                        html += '<label><input type="radio" name="i18nLanguage" value="' + languageId + '"' + (~~this._options.i18n.defaultLanguageId === ~~languageId ? ' checked' : '') + '> ' + this._options.i18n.languages[languageId] + '</label>';
                    }
                }
                html += '</dd></dl>';
            }
            UiConfirmation.show({
                confirm: (function (parameters, content) {
                    var languageId = 0;
                    if (this._options.i18n.isI18n) {
                        languageId = elBySel("input[name='i18nLanguage']:checked", content.parentNode).value;
                    }
                    Ajax.api(this, {
                        actionName: 'toggleI18n',
                        objectIDs: [objectId],
                        parameters: {
                            languageID: languageId
                        }
                    });
                }).bind(this),
                message: html,
                messageIsHtml: true
            });
        },
        /**
         * Invokes the selected action.
         *
         * @param       {int}           objectId        article id
         * @param       {string}        actionName      action name
         * @protected
         */
        _invoke: function (objectId, actionName) {
            Ajax.api(this, {
                actionName: actionName,
                objectIDs: [objectId]
            });
        },
        /**
         * Handles an article being deleted.
         *
         * @param	{int}		articleId	id of the deleted article
         */
        _triggerDelete: function (articleId) {
            var article = _articles.get(articleId);
            if (article.isArticleEdit) {
                window.location = this._options.redirectUrl;
            }
            else {
                var tbody = article.element.parentNode;
                elRemove(article.element);
                if (elBySel('tr', tbody) === null) {
                    window.location.reload();
                }
            }
        },
        /**
         * Handles publishing an article via clipboard.
         *
         * @param	{int}		articleId	id of the published article
         */
        _triggerPublish: function (articleId) {
            var article = _articles.get(articleId);
            if (article.isArticleEdit) {
                // unsupported
            }
            else {
                elRemove(elBySel('.jsUnpublishedArticle', article.element));
            }
        },
        /**
         * Handles an article being restored.
         *
         * @param	{int}		articleId	id of the deleted article
         */
        _triggerRestore: function (articleId) {
            var article = _articles.get(articleId);
            elHide(article.buttons.delete);
            elHide(article.buttons.restore);
            elShow(article.buttons.trash);
            if (article.isArticleEdit) {
                elHide(elBySel('.jsArticleNoticeTrash'));
            }
            else {
                elRemove(elBySel('.jsIconDeleted', article.element));
            }
        },
        /**
         * Handles an article being trashed.
         *
         * @param	{int}		articleId	id of the deleted article
         */
        _triggerTrash: function (articleId) {
            var article = _articles.get(articleId);
            elShow(article.buttons.delete);
            elShow(article.buttons.restore);
            elHide(article.buttons.trash);
            if (article.isArticleEdit) {
                elShow(elBySel('.jsArticleNoticeTrash'));
            }
            else {
                var badge = elCreate('span');
                badge.className = 'badge label red jsIconDeleted';
                badge.textContent = Language.get('wcf.message.status.deleted');
                var h3 = elBySel('.containerHeadline > h3', article.element);
                h3.insertBefore(badge, h3.firstChild);
            }
        },
        /**
         * Handles unpublishing an article via clipboard.
         *
         * @param	{int}		articleId	id of the unpublished article
         */
        _triggerUnpublish: function (articleId) {
            var article = _articles.get(articleId);
            if (article.isArticleEdit) {
                // unsupported
            }
            else {
                var badge = elCreate('span');
                badge.className = 'badge jsUnpublishedArticle';
                badge.textContent = Language.get('wcf.acp.article.publicationStatus.unpublished');
                var h3 = elBySel('.containerHeadline > h3', article.element);
                var a = elBySel('a', h3);
                h3.insertBefore(badge, a);
                h3.insertBefore(document.createTextNode(" "), a);
            }
        },
        _ajaxSuccess: function (data) {
            var notificationCallback;
            switch (data.actionName) {
                case 'delete':
                    this._triggerDelete(data.objectIDs[0]);
                    break;
                case 'restore':
                    this._triggerRestore(data.objectIDs[0]);
                    break;
                case 'setCategory':
                    notificationCallback = window.location.reload.bind(window.location);
                    break;
                case 'toggleI18n':
                    UiNotification.show(undefined, function () { window.location.reload(); });
                    break;
                case 'trash':
                    this._triggerTrash(data.objectIDs[0]);
                    break;
            }
            UiNotification.show(undefined, notificationCallback);
            ControllerClipboard.reload();
        },
        _ajaxSetup: function () {
            return {
                data: {
                    className: 'wcf\\data\\article\\ArticleAction'
                }
            };
        }
    };
    return AcpUiArticleInlineEditor;
});
