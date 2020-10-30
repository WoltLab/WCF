/**
 * Uploads the user cover photo via AJAX.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/CoverPhoto/Upload
 */

import DomUtil from "../../../Dom/Util";
import * as EventHandler from "../../../Event/Handler";
import { ResponseData } from "../../../Ajax/Data";
import * as UiDialog from "../../Dialog";
import * as UiNotification from "../../Notification";
import Upload from "../../../Upload";

interface AjaxResponse extends ResponseData {
  returnValues: {
    errorMessage?: string;
    url?: string;
  };
}

/**
 * @constructor
 */
class UiUserCoverPhotoUpload extends Upload {
  private readonly userId: number;

  constructor(userId: number) {
    super("coverPhotoUploadButtonContainer", "coverPhotoUploadPreview", {
      action: "uploadCoverPhoto",
      className: "wcf\\data\\user\\UserProfileAction",
    });

    this.userId = userId;
  }

  protected _getParameters(): object {
    return {
      userID: this.userId,
    };
  }

  protected _success(uploadId: number, data: AjaxResponse): void {
    // remove or display the error message
    DomUtil.innerError(this._button, data.returnValues.errorMessage);

    // remove the upload progress
    this._target.innerHTML = "";

    if (data.returnValues.url) {
      const photo = document.querySelector(".userProfileCoverPhoto") as HTMLElement;
      photo.style.setProperty("background-image", `url(${data.returnValues.url})`, "");

      UiDialog.close("userProfileCoverPhotoUpload");
      UiNotification.show();

      EventHandler.fire("com.woltlab.wcf.user", "coverPhoto", {
        url: data.returnValues.url,
      });
    }
  }
}

export = UiUserCoverPhotoUpload;
