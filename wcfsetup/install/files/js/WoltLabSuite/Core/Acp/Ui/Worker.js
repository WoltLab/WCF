/**
 * Worker manager with support for custom callbacks and loop counts.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Acp/Ui/Worker
 */
define(['Ajax', 'Core', 'Language', 'Ui/Dialog'], function (Ajax, Core, Language, UiDialog) {
    "use strict";
    /**
     * Creates a new worker instance.
     *
     * @param       {Object}        options         configuration options
     * @constructor
     */
    function AcpUiWorker(options) { this.init(options); }
    AcpUiWorker.prototype = {
        /**
         * Creates a new worker instance.
         *
         * @param       {Object}        options         configuration options
         */
        init: function (options) {
            this._aborted = false;
            this._options = Core.extend({
                // dialog
                dialogId: '',
                dialogTitle: '',
                // ajax
                className: '',
                loopCount: -1,
                parameters: {},
                // callbacks
                callbackAbort: null,
                callbackFailure: null,
                callbackSuccess: null
            }, options);
            this._options.dialogId += 'Worker';
            // update title
            if (UiDialog.getDialog(this._options.dialogId) !== undefined) {
                UiDialog.setTitle(this._options.dialogId, this._options.dialogTitle);
            }
            this._request = Ajax.api(this);
        },
        _ajaxSuccess: function (data) {
            if (this._aborted)
                return;
            if (typeof data.template === 'string') {
                UiDialog.open(this, data.template);
            }
            var content = UiDialog.getDialog(this).content;
            // update progress
            var progress = elBySel('progress', content);
            progress.value = data.progress;
            progress.nextElementSibling.textContent = data.progress + '%';
            // worker is still busy
            if (data.progress < 100) {
                Ajax.api(this, {
                    loopCount: data.loopCount,
                    parameters: data.parameters
                });
            }
            else {
                var spinner = elBySel('.fa-spinner', content);
                spinner.classList.remove('fa-spinner');
                spinner.classList.add('fa-check');
                spinner.classList.add('green');
                var formSubmit = elCreate('div');
                formSubmit.className = 'formSubmit';
                formSubmit.innerHTML = '<button class="buttonPrimary">' + Language.get('wcf.global.button.next') + '</button>';
                content.appendChild(formSubmit);
                UiDialog.rebuild(this);
                var button = formSubmit.children[0];
                button.addEventListener(WCF_CLICK_EVENT, (function (event) {
                    event.preventDefault();
                    if (typeof this._options.callbackSuccess === 'function') {
                        this._options.callbackSuccess(data);
                        UiDialog.close(this);
                    }
                    else {
                        window.location = data.proceedURL;
                    }
                }).bind(this));
                button.focus();
            }
        },
        _ajaxFailure: function () {
            var dialog = UiDialog.getDialog(this);
            if (dialog !== undefined) {
                var spinner = elBySel('.fa-spinner', dialog.content);
                spinner.classList.remove('fa-spinner');
                spinner.classList.add('fa-times');
                spinner.classList.add('red');
            }
        },
        _ajaxSetup: function () {
            return {
                data: {
                    className: this._options.className,
                    loopCount: this._options.loopCount,
                    parameters: this._options.parameters
                },
                silent: true,
                url: 'index.php?worker-proxy/&t=' + SECURITY_TOKEN
            };
        },
        _dialogSetup: function () {
            return {
                id: this._options.dialogId,
                onClose: (function () {
                    this._aborted = true;
                    this._request.abortPrevious();
                    if (typeof this._options.callbackAbort === 'function') {
                        this._options.callbackAbort();
                    }
                    else {
                        window.location.reload();
                    }
                }).bind(this),
                options: {
                    backdropCloseOnClick: false,
                    title: this._options.dialogTitle
                },
                source: null
            };
        }
    };
    return AcpUiWorker;
});
