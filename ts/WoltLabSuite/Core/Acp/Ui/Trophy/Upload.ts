/**
 * Handles the trophy image upload.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import * as Core from "../../../Core";
import DomUtil from "../../../Dom/Util";
import { getPhrase } from "WoltLabSuite/Core/Language";
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

    showDefaultSuccessSnackbar();
  }

  protected _failure(uploadId: number, data: AjaxResponseError): boolean {
    DomUtil.innerError(this._button, getPhrase(`wcf.acp.trophy.imageUpload.error.${data.returnValues.errorType}`));

    // remove previous images
    this._target.innerHTML = "";

    return false;
  }
}

export = TrophyUpload;
