/**
 * Provides the trophy icon designer.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/User/Content/Remove/Handler
 * @since       5.2
 */
define(['Ajax', 'Language', 'Ui/Dialog', 'WoltLabSuite/Core/Acp/Ui/Worker'], function (Ajax, Language, UiDialog, Worker) {
    "use strict";
    /**
     * Creates a new worker instance.
     * @constructor
     */
    function Handler(element, userId) { this.init(element, userId); }
    Handler.prototype = {
        /**
         * Initializes the content remove handler.
         */
        init: function (element, userId) {
            element.addEventListener('click', this._click.bind(this));
            this._userId = userId;
        },
        /**
         * Click on the remove content button.
         *
         * @param	{object}	event		click event
         */
        _click: function (event) {
            event.preventDefault();
            Ajax.api(this);
        },
        /**
         * Executes the remove content worker.
         *
         * @param       {Array}         objectTypes
         */
        _executeWorker: function (objectTypes) {
            new Worker({
                // dialog
                dialogId: 'removeContentWorker',
                dialogTitle: Language.get('wcf.acp.content.removeContent'),
                // ajax
                className: '\\wcf\\system\\worker\\UserContentRemoveWorker',
                parameters: {
                    userID: this._userId,
                    contentProvider: objectTypes
                }
            });
        },
        /**
         * Handles a click on the submit button in the overlay.
         */
        _submit: function () {
            var objectTypeInputs = elBySelAll('input.contentProviderObjectType', UiDialog.getDialog('userRemoveContentHandler-' + this._userId).content);
            var objectTypes = [];
            for (var i = 0, length = objectTypeInputs.length; i < length; i++) {
                if (objectTypeInputs[i].checked) {
                    objectTypes.push(objectTypeInputs[i].name);
                }
            }
            UiDialog.close('userRemoveContentHandler-' + this._userId);
            window.setTimeout(function () {
                if (objectTypes.length > 0) {
                    this._executeWorker(objectTypes);
                }
            }.bind(this), 200);
        },
        _ajaxSuccess: function (data) {
            UiDialog.open(this, data.returnValues.template);
            elBySel('input[type="submit"]', UiDialog.getDialog('userRemoveContentHandler-' + this._userId).content).addEventListener('click', this._submit.bind(this));
        },
        _ajaxSetup: function () {
            return {
                data: {
                    actionName: 'prepareRemoveContent',
                    className: 'wcf\\data\\user\\UserAction',
                    parameters: {
                        userID: this._userId
                    }
                }
            };
        },
        _dialogSetup: function () {
            return {
                id: 'userRemoveContentHandler-' + this._userId,
                options: {
                    title: Language.get('wcf.acp.content.removeContent')
                },
                source: null
            };
        }
    };
    return Handler;
});
