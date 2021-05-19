/**
 * @woltlabExcludeBundle tiny
 */

import * as Core from "../../../../../Core";
import * as Language from "../../../../../Language";
import * as UiNotification from "../../../../Notification";
import UiUserProfileMenuItemAbstract from "./Abstract";
import FormBuilderDialog from "../../../../../Form/Builder/Dialog";

interface AjaxResponse {
  isIgnoredUser: 1 | 0;
}

class UiUserProfileMenuItemIgnore extends UiUserProfileMenuItemAbstract {
  private readonly dialog: FormBuilderDialog;

  constructor(userId: number, isActive: boolean) {
    super(userId, isActive);

    this.dialog = new FormBuilderDialog("ignoreDialog", "wcf\\data\\user\\ignore\\UserIgnoreAction", "getDialog", {
      dialog: {
        title: Language.get("wcf.user.button.ignore"),
      },
      actionParameters: {
        userID: this._userId,
      },
      submitActionName: "submitDialog",
      successCallback: (r: AjaxResponse) => this._ajaxSuccess(r),
      destroyOnClose: true,
    });
  }

  _getLabel(): string {
    return Language.get("wcf.user.button." + (this._isActive ? "un" : "") + "ignore");
  }

  _ajaxSuccess(data: AjaxResponse): void {
    this._isActive = !!data.isIgnoredUser;
    this._updateButton();

    UiNotification.show();
  }

  protected _toggle(event: MouseEvent): void {
    event.preventDefault();

    this.dialog.open();
  }
}

Core.enableLegacyInheritance(UiUserProfileMenuItemIgnore);

export = UiUserProfileMenuItemIgnore;
