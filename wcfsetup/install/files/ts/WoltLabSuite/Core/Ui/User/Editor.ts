/**
 * Simple notification overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Editor
 */

import * as Ajax from '../../Ajax';
import { AjaxCallbackObject } from '../../Ajax/Data';
import * as Core from '../../Core';
import { DialogCallbackObject, DialogSettings } from '../Dialog/Data';
import DomUtil from '../../Dom/Util';
import * as Language from '../../Language';
import * as StringUtil from '../../StringUtil';
import UiDialog from '../Dialog';
import * as UiNotification from '../Notification';

class UserEditor implements AjaxCallbackObject, DialogCallbackObject {
  private actionName = '';
  private readonly header: HTMLElement;

  constructor() {
    this.header = document.querySelector('.userProfileUser') as HTMLElement;

    ['ban', 'disableAvatar', 'disableCoverPhoto', 'disableSignature', 'enable'].forEach(action => {
      const button = document.querySelector('.userProfileButtonMenu .jsButtonUser' + StringUtil.ucfirst(action)) as HTMLElement;

      // The button is missing if the current user lacks the permission.
      if (button) {
        button.dataset.action = action;
        button.addEventListener('click', (ev) => this._click(ev));
      }
    });
  }

  /**
   * Handles clicks on action buttons.
   */
  _click(event: MouseEvent): void {
    event.preventDefault();

    const target = event.currentTarget as HTMLElement;
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

      UiDialog.open(this);
    } else {
      Ajax.api(this, {
        actionName: actionName,
      });
    }
  }

  /**
   * Handles form submit and input validation.
   */
  _submit(event: Event): void {
    event.preventDefault();

    const label = document.getElementById('wcfUiUserEditorExpiresLabel') as HTMLElement;

    let expires = '';
    let errorMessage = '';
    const neverExpires = document.getElementById('wcfUiUserEditorNeverExpires') as HTMLInputElement;
    if (!neverExpires.checked) {
      const expireValue = document.getElementById('wcfUiUserEditorExpiresDatePicker') as HTMLInputElement;
      expires = expireValue.value;
      if (expires === '') {
        errorMessage = Language.get('wcf.global.form.error.empty');
      }
    }

    DomUtil.innerError(label, errorMessage);

    const parameters = {};
    parameters[this.actionName + 'Expires'] = expires;
    const reason = document.getElementById('wcfUiUserEditorReason') as HTMLTextAreaElement;
    parameters[this.actionName + 'Reason'] = reason.value.trim();

    Ajax.api(this, {
      actionName: this.actionName,
      parameters: parameters,
    });
  }

  _ajaxSuccess(data) {
    let button: HTMLElement;
    switch (data.actionName) {
      case 'ban':
      case 'unban':
        this.header.dataset.banned = (data.actionName === 'ban') ? 'true' : 'false';
        button = document.querySelector('.userProfileButtonMenu .jsButtonUserBan') as HTMLElement;
        button.textContent = Language.get('wcf.user.' + (data.actionName === 'ban' ? 'unban' : 'ban'));

        const contentTitle = this.header.querySelector('.contentTitle') as HTMLElement;
        let banIcon = contentTitle.querySelector('.jsUserBanned') as HTMLElement;
        if (data.actionName === 'ban') {
          banIcon = document.createElement('span');
          banIcon.className = 'icon icon24 fa-lock jsUserBanned jsTooltip';
          banIcon.title = data.returnValues;
          contentTitle.appendChild(banIcon);
        } else if (banIcon) {
          banIcon.remove();
        }
        break;

      case 'disableAvatar':
      case 'enableAvatar':
        this.header.dataset.disableAvatar = (data.actionName === 'disableAvatar') ? 'true' : 'false';
        button = document.querySelector('.userProfileButtonMenu .jsButtonUserDisableAvatar') as HTMLElement;
        button.textContent = Language.get('wcf.user.' + (data.actionName === 'disableAvatar' ? 'enable' : 'disable') + 'Avatar');
        break;

      case 'disableCoverPhoto':
      case 'enableCoverPhoto':
        this.header.dataset.disableCoverPhoto = (data.actionName === 'disableCoverPhoto') ? 'true' : 'false';
        button = document.querySelector('.userProfileButtonMenu .jsButtonUserDisableCoverPhoto') as HTMLElement;
        button.textContent = Language.get('wcf.user.' + (data.actionName === 'disableCoverPhoto' ? 'enable' : 'disable') + 'CoverPhoto');
        break;

      case 'disableSignature':
      case 'enableSignature':
        this.header.dataset.disableSignature = (data.actionName === 'disableSignature') ? 'true' : 'false';
        button = document.querySelector('.userProfileButtonMenu .jsButtonUserDisableSignature') as HTMLElement;
        button.textContent = Language.get('wcf.user.' + (data.actionName === 'disableSignature' ? 'enable' : 'disable') + 'Signature');
        break;

      case 'enable':
      case 'disable':
        this.header.dataset.isDisabled = (data.actionName === 'disable') ? 'true' : 'false';
        button = document.querySelector('.userProfileButtonMenu .jsButtonUserEnable') as HTMLElement;
        button.textContent = Language.get('wcf.acp.user.' + (data.actionName === 'enable' ? 'disable' : 'enable'));
        break;
    }

    if (['ban', 'disableAvatar', 'disableCoverPhoto', 'disableSignature'].indexOf(data.actionName) !== -1) {
      UiDialog.close(this);
    }

    UiNotification.show();
  }

  _ajaxSetup() {
    return {
      data: {
        className: 'wcf\\data\\user\\UserAction',
        objectIDs: [+this.header.dataset.objectId!],
      },
    };
  }

  _dialogSetup(): DialogSettings {
    return {
      id: 'wcfUiUserEditor',
      options: {
        onSetup: content => {
          const checkbox = document.getElementById('wcfUiUserEditorNeverExpires') as HTMLInputElement;
          checkbox.addEventListener('change', () => {
            const settings = document.getElementById('wcfUiUserEditorExpiresSettings') as HTMLElement;
            DomUtil[checkbox.checked ? 'hide' : 'show'](settings);
          });

          const submitButton = content.querySelector('button.buttonPrimary') as HTMLButtonElement;
          submitButton.addEventListener('click', this._submit.bind(this));
        },
        onShow: content => {
          UiDialog.setTitle('wcfUiUserEditor', Language.get('wcf.user.' + this.actionName + '.confirmMessage'));

          const reason = document.getElementById('wcfUiUserEditorReason') as HTMLElement;
          let label = reason.nextElementSibling as HTMLElement;
          const phrase = 'wcf.user.' + this.actionName + '.reason.description';
          label.textContent = Language.get(phrase);
          window[(label.textContent === phrase) ? 'elHide' : 'elShow'](label);

          label = document.getElementById('wcfUiUserEditorNeverExpires')!.nextElementSibling as HTMLElement;
          label.textContent = Language.get('wcf.user.' + this.actionName + '.neverExpires');

          label = content.querySelector('label[for="wcfUiUserEditorExpires"]') as HTMLElement;
          label.textContent = Language.get('wcf.user.' + this.actionName + '.expires');

          label = document.getElementById('wcfUiUserEditorExpiresLabel') as HTMLElement;
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
export function init() {
  new UserEditor();
}
