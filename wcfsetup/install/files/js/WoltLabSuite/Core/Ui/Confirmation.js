/**
 * Provides the confirmation dialog overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/Confirmation (alias)
 * @module  WoltLabSuite/Core/Ui/Confirmation
 */
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
define(["require", "exports", "../Core", "../Language", "./Dialog"], function (require, exports, Core, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getContentElement = exports.show = void 0;
    Core = __importStar(Core);
    Language = __importStar(Language);
    Dialog_1 = __importDefault(Dialog_1);
    class UiConfirmation {
        constructor() {
            this._active = false;
            this.dialog = document.createElement('div');
            this.dialog.id = 'wcfSystemConfirmation';
            this.dialog.classList.add('systemConfirmation');
            this.text = document.createElement('p');
            this.dialog.appendChild(this.text);
            this._content = document.createElement('div');
            this._content.id = 'wcfSystemConfirmationContent';
            this.dialog.appendChild(this._content);
            const formSubmit = document.createElement('div');
            formSubmit.classList.add('formSubmit');
            this.dialog.appendChild(formSubmit);
            this.confirmButton = document.createElement('button');
            this.confirmButton.classList.add('buttonPrimary');
            this.confirmButton.textContent = Language.get('wcf.global.confirmation.confirm');
            this.confirmButton.addEventListener('click', this._confirm.bind(this));
            formSubmit.appendChild(this.confirmButton);
            const cancelButton = document.createElement('button');
            cancelButton.textContent = Language.get('wcf.global.confirmation.cancel');
            cancelButton.addEventListener('click', () => {
                Dialog_1.default.close(this);
            });
            formSubmit.appendChild(cancelButton);
            document.body.appendChild(this.dialog);
        }
        open(options) {
            this.parameters = options.parameters || {};
            this._content.innerHTML = (typeof options.template === 'string') ? options.template.trim() : '';
            this.text[options.messageIsHtml ? 'innerHtml' : 'textContent'] = options.message;
            if (typeof options.legacyCallback === 'function') {
                this.callbackCancel = parameters => {
                    options.legacyCallback('cancel', parameters, this.content);
                };
                this.callbackConfirm = parameters => {
                    options.legacyCallback('confirm', parameters, this.content);
                };
            }
            else {
                if (typeof options.cancel !== 'function') {
                    options.cancel = () => {
                    };
                }
                this.callbackCancel = options.cancel;
                this.callbackConfirm = options.confirm;
            }
            this._active = true;
            Dialog_1.default.open(this);
        }
        get active() {
            return this._active;
        }
        get content() {
            return this._content;
        }
        /**
         * Invoked if the user confirms the dialog.
         */
        _confirm() {
            this.callbackConfirm(this.parameters, this.content);
            this._active = false;
            Dialog_1.default.close('wcfSystemConfirmation');
        }
        /**
         * Invoked on dialog close or if user cancels the dialog.
         */
        _onClose() {
            if (this.active) {
                this.confirmButton.blur();
                this._active = false;
                this.callbackCancel(this.parameters);
            }
        }
        /**
         * Sets the focus on the confirm button on dialog open for proper keyboard support.
         */
        _onShow() {
            this.confirmButton.blur();
            this.confirmButton.focus();
        }
        _dialogSetup() {
            return {
                id: 'wcfSystemConfirmation',
                options: {
                    onClose: this._onClose.bind(this),
                    onShow: this._onShow.bind(this),
                    title: Language.get('wcf.global.confirmation.title'),
                },
            };
        }
    }
    let confirmation;
    function getConfirmation() {
        if (!confirmation) {
            confirmation = new UiConfirmation();
        }
        return confirmation;
    }
    /**
     * Shows the confirmation dialog.
     */
    function show(options) {
        if (getConfirmation().active) {
            return;
        }
        options = Core.extend({
            cancel: null,
            confirm: null,
            legacyCallback: null,
            message: '',
            messageIsHtml: false,
            parameters: {},
            template: '',
        }, options);
        options.message = (typeof options.message === 'string') ? options.message.trim() : '';
        if (!options.message) {
            throw new Error("Expected a non-empty string for option 'message'.");
        }
        if (typeof options.confirm !== 'function' && typeof options.legacyCallback !== 'function') {
            throw new TypeError("Expected a valid callback for option 'confirm'.");
        }
        getConfirmation().open(options);
    }
    exports.show = show;
    /**
     * Returns content container element.
     */
    function getContentElement() {
        return getConfirmation().content;
    }
    exports.getContentElement = getContentElement;
});
