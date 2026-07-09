/**
 * @woltlabExcludeBundle all
 */

import { getPhrase } from "WoltLabSuite/Core/Language";
import UiUserProfileMenuItemAbstract from "./Abstract";
import FormBuilderDialog from "../../../../../Form/Builder/Dialog";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";

interface AjaxResponse {
  isIgnoredUser: 1 | 0;
}

/**
 * @deprecated 6.2 Use `WoltLabSuite/Core/Component/User/Ignore` instead.
 */
class UiUserProfileMenuItemIgnore extends UiUserProfileMenuItemAbstract {
  private readonly dialog: FormBuilderDialog;

  constructor(userId: number, isActive: boolean) {
    super(userId, isActive);

    this.dialog = new FormBuilderDialog("ignoreDialog", "wcf\\data\\user\\ignore\\UserIgnoreAction", "getDialog", {
      dialog: {
        title: getPhrase("wcf.user.button.ignore"),
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
    return getPhrase("wcf.user.button." + (this._isActive ? "un" : "") + "ignore");
  }

  _ajaxSuccess(data: AjaxResponse): void {
    this._isActive = !!data.isIgnoredUser;
    this._updateButton();

    showDefaultSuccessSnackbar();
  }

  protected _toggle(event: MouseEvent): void {
    event.preventDefault();

    this.dialog.open();
  }
}

export = UiUserProfileMenuItemIgnore;
