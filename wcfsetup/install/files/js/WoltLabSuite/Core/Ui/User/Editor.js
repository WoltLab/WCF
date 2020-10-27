/**
 * Simple notification overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Editor
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
define(["require", "exports", "../../Ajax", "../../Core", "../../Dom/Util", "../../Language", "../../StringUtil", "../Dialog", "../Notification"], function (require, exports, Ajax, Core, Util_1, Language, StringUtil, Dialog_1, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    Ajax = __importStar(Ajax);
    Core = __importStar(Core);
    Util_1 = __importDefault(Util_1);
    Language = __importStar(Language);
    StringUtil = __importStar(StringUtil);
    Dialog_1 = __importDefault(Dialog_1);
    UiNotification = __importStar(UiNotification);
    class UserEditor {
        constructor() {
            this.actionName = '';
            this.header = document.querySelector('.userProfileUser');
            ['ban', 'disableAvatar', 'disableCoverPhoto', 'disableSignature', 'enable'].forEach(action => {
                const button = document.querySelector('.userProfileButtonMenu .jsButtonUser' + StringUtil.ucfirst(action));
                // The button is missing if the current user lacks the permission.
                if (button) {
                    button.dataset.action = action;
                    button.addEventListener('click', this._click.bind(this));
                }
            });
        }
        /**
         * Handles clicks on action buttons.
         */
        _click(event) {
            event.preventDefault();
            const target = event.currentTarget;
            const action = target.dataset.action || '';
            let actionName = '';
            switch (action) {
                case 'ban':
                    if (Core.stringToBool(this.header.dataset.banned || '')) {
                        actionName = 'unban';
                    }
                    break;
                case 'disableAvatar':
                    if (Core.stringToBool(this.header.dataset.disableAvatar || '')) {
                        actionName = 'enableAvatar';
                    }
                    break;
                case 'disableCoverPhoto':
                    if (Core.stringToBool(this.header.dataset.disableCoverPhoto || '')) {
                        actionName = 'enableCoverPhoto';
                    }
                    break;
                case 'disableSignature':
                    if (Core.stringToBool(this.header.dataset.disableSignature || '')) {
                        actionName = 'enableSignature';
                    }
                    break;
                case 'enable':
                    actionName = (Core.stringToBool(this.header.dataset.isDisabled || '')) ? 'enable' : 'disable';
                    break;
            }
            if (actionName === '') {
                this.actionName = action;
                Dialog_1.default.open(this);
            }
            else {
                Ajax.api(this, {
                    actionName: actionName,
                });
            }
        }
        /**
         * Handles form submit and input validation.
         */
        _submit(event) {
            event.preventDefault();
            const label = document.getElementById('wcfUiUserEditorExpiresLabel');
            let expires = '';
            let errorMessage = '';
            const neverExpires = document.getElementById('wcfUiUserEditorNeverExpires');
            if (!neverExpires.checked) {
                const expireValue = document.getElementById('wcfUiUserEditorExpiresDatePicker');
                expires = expireValue.value;
                if (expires === '') {
                    errorMessage = Language.get('wcf.global.form.error.empty');
                }
            }
            Util_1.default.innerError(label, errorMessage);
            const parameters = {};
            parameters[this.actionName + 'Expires'] = expires;
            const reason = document.getElementById('wcfUiUserEditorReason');
            parameters[this.actionName + 'Reason'] = reason.value.trim();
            Ajax.api(this, {
                actionName: this.actionName,
                parameters: parameters,
            });
        }
        _ajaxSuccess(data) {
            let button;
            switch (data.actionName) {
                case 'ban':
                case 'unban':
                    this.header.dataset.banned = (data.actionName === 'ban') ? 'true' : 'false';
                    button = document.querySelector('.userProfileButtonMenu .jsButtonUserBan');
                    button.textContent = Language.get('wcf.user.' + (data.actionName === 'ban' ? 'unban' : 'ban'));
                    const contentTitle = this.header.querySelector('.contentTitle');
                    let banIcon = contentTitle.querySelector('.jsUserBanned');
                    if (data.actionName === 'ban') {
                        banIcon = document.createElement('span');
                        banIcon.className = 'icon icon24 fa-lock jsUserBanned jsTooltip';
                        banIcon.title = data.returnValues;
                        contentTitle.appendChild(banIcon);
                    }
                    else if (banIcon) {
                        banIcon.remove();
                    }
                    break;
                case 'disableAvatar':
                case 'enableAvatar':
                    this.header.dataset.disableAvatar = (data.actionName === 'disableAvatar') ? 'true' : 'false';
                    button = document.querySelector('.userProfileButtonMenu .jsButtonUserDisableAvatar');
                    button.textContent = Language.get('wcf.user.' + (data.actionName === 'disableAvatar' ? 'enable' : 'disable') + 'Avatar');
                    break;
                case 'disableCoverPhoto':
                case 'enableCoverPhoto':
                    this.header.dataset.disableCoverPhoto = (data.actionName === 'disableCoverPhoto') ? 'true' : 'false';
                    button = document.querySelector('.userProfileButtonMenu .jsButtonUserDisableCoverPhoto');
                    button.textContent = Language.get('wcf.user.' + (data.actionName === 'disableCoverPhoto' ? 'enable' : 'disable') + 'CoverPhoto');
                    break;
                case 'disableSignature':
                case 'enableSignature':
                    this.header.dataset.disableSignature = (data.actionName === 'disableSignature') ? 'true' : 'false';
                    button = document.querySelector('.userProfileButtonMenu .jsButtonUserDisableSignature');
                    button.textContent = Language.get('wcf.user.' + (data.actionName === 'disableSignature' ? 'enable' : 'disable') + 'Signature');
                    break;
                case 'enable':
                case 'disable':
                    this.header.dataset.isDisabled = (data.actionName === 'disable') ? 'true' : 'false';
                    button = document.querySelector('.userProfileButtonMenu .jsButtonUserEnable');
                    button.textContent = Language.get('wcf.acp.user.' + (data.actionName === 'enable' ? 'disable' : 'enable'));
                    break;
            }
            if (['ban', 'disableAvatar', 'disableCoverPhoto', 'disableSignature'].indexOf(data.actionName) !== -1) {
                Dialog_1.default.close(this);
            }
            UiNotification.show();
        }
        _ajaxSetup() {
            return {
                data: {
                    className: 'wcf\\data\\user\\UserAction',
                    objectIDs: [+this.header.dataset.objectId],
                },
            };
        }
        _dialogSetup() {
            return {
                id: 'wcfUiUserEditor',
                options: {
                    onSetup: content => {
                        const checkbox = document.getElementById('wcfUiUserEditorNeverExpires');
                        checkbox.addEventListener('change', () => {
                            const settings = document.getElementById('wcfUiUserEditorExpiresSettings');
                            Util_1.default[checkbox.checked ? 'hide' : 'show'](settings);
                        });
                        const submitButton = content.querySelector('button.buttonPrimary');
                        submitButton.addEventListener('click', this._submit.bind(this));
                    },
                    onShow: content => {
                        Dialog_1.default.setTitle('wcfUiUserEditor', Language.get('wcf.user.' + this.actionName + '.confirmMessage'));
                        const reason = document.getElementById('wcfUiUserEditorReason');
                        let label = reason.nextElementSibling;
                        const phrase = 'wcf.user.' + this.actionName + '.reason.description';
                        label.textContent = Language.get(phrase);
                        window[(label.textContent === phrase) ? 'elHide' : 'elShow'](label);
                        label = document.getElementById('wcfUiUserEditorNeverExpires').nextElementSibling;
                        label.textContent = Language.get('wcf.user.' + this.actionName + '.neverExpires');
                        label = content.querySelector('label[for="wcfUiUserEditorExpires"]');
                        label.textContent = Language.get('wcf.user.' + this.actionName + '.expires');
                        label = document.getElementById('wcfUiUserEditorExpiresLabel');
                        label.textContent = Language.get('wcf.user.' + this.actionName + '.expires.description');
                    },
                },
                source: `<div class="section">
        <dl>
          <dt><label for="wcfUiUserEditorReason">${Language.get('wcf.global.reason')}</label></dt>
          <dd><textarea id="wcfUiUserEditorReason" cols="40" rows="3"></textarea><small></small></dd>
        </dl>
        <dl>
          <dt></dt>
          <dd><label><input type="checkbox" id="wcfUiUserEditorNeverExpires" checked> <span></span></label></dd>
        </dl>
        <dl id="wcfUiUserEditorExpiresSettings" style="display: none">
          <dt><label for="wcfUiUserEditorExpires"></label></dt>
          <dd>
            <input type="date" name="wcfUiUserEditorExpires" id="wcfUiUserEditorExpires" class="medium" min="${new Date(window.TIME_NOW * 1000).toISOString()}" data-ignore-timezone="true">
            <small id="wcfUiUserEditorExpiresLabel"></small>
          </dd>
        </dl>
      </div>
      <div class="formSubmit">
        <button class="buttonPrimary">${Language.get('wcf.global.button.submit')}</button>
      </div>`,
            };
        }
    }
    /**
     * Initializes the user editor.
     */
    function init() {
        new UserEditor();
    }
    exports.init = init;
});
