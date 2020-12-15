/**
 * Handles the trophy image upload.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Trophy/Upload
 */

import * as Core from "../../../Core";
import DomUtil from "../../../Dom/Util";
import * as Language from "../../../Language";
import * as UiNotification from "../../../Ui/Notification";
import Upload from "../../../Upload";
import { UploadOptions } from "../../../Upload/Data";

interface AjaxResponse {
  returnValues: {
    url: string;
  };
}

interface AjaxResponseError {
  returnValues: {
    errorType: string;
  };
}

class TrophyUpload extends Upload {
  private readonly trophyId: number;
  private readonly tmpHash: string;

  constructor(trophyId: number, tmpHash: string, options: Partial<UploadOptions>) {
    super(
      "uploadIconFileButton",
      "uploadIconFileContent",
      Core.extend(
        {
          className: "wcf\\data\\trophy\\TrophyAction",
        },
        options,
      ),
    );

    this.trophyId = ~~trophyId;
    this.tmpHash = tmpHash;
  }

  protected _getParameters(): ArbitraryObject {
    return {
      trophyID: this.trophyId,
      tmpHash: this.tmpHash,
    };
  }

  protected _success(uploadId: number, data: AjaxResponse): void {
    DomUtil.innerError(this._button, false);

    this._target.innerHTML = `<img src="${data.returnValues.url}?timestamp=${Date.now()}" alt="">`;

    UiNotification.show();
  }

  protected _failure(uploadId: number, data: AjaxResponseError): boolean {
    DomUtil.innerError(this._button, Language.get(`wcf.acp.trophy.imageUpload.error.${data.returnValues.errorType}`));

    // remove previous images
    this._target.innerHTML = "";

    return false;
  }
}

Core.enableLegacyInheritance(TrophyUpload);

export = TrophyUpload;
