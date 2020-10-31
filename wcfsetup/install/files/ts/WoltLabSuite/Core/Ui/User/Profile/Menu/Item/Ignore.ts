import * as Language from "../../../../../Language";
import { AjaxCallbackSetup, ResponseData } from "../../../../../Ajax/Data";
import * as UiNotification from "../../../../Notification";
import UiUserProfileMenuItemAbstract from "./Abstract";

interface AjaxResponse extends ResponseData {
  returnValues: {
    isIgnoredUser: 1 | 0;
  };
}

class UiUserProfileMenuItemIgnore extends UiUserProfileMenuItemAbstract {
  constructor(userId: number, isActive: boolean) {
    super(userId, isActive);
  }

  _getLabel(): string {
    return Language.get("wcf.user.button." + (this._isActive ? "un" : "") + "ignore");
  }

  _getAjaxActionName(): string {
    return this._isActive ? "unignore" : "ignore";
  }

  _ajaxSuccess(data: AjaxResponse): void {
    this._isActive = !!data.returnValues.isIgnoredUser;
    this._updateButton();

    UiNotification.show();
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        className: "wcf\\data\\user\\ignore\\UserIgnoreAction",
      },
    };
  }
}

export = UiUserProfileMenuItemIgnore;
