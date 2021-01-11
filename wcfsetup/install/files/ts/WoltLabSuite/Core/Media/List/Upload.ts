/**
 * Uploads media files.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/List/Upload
 */

import MediaUpload from "../Upload";
import { MediaListUploadOptions } from "../Data";
import * as Core from "../../Core";

class MediaListUpload extends MediaUpload<MediaListUploadOptions> {
  protected _createButton(): void {
    super._createButton();

    const span = this._button.querySelector("span") as HTMLSpanElement;

    const space = document.createTextNode(" ");
    span.insertBefore(space, span.childNodes[0]);

    const icon = document.createElement("span");
    icon.className = "icon icon16 fa-upload";
    span.insertBefore(icon, span.childNodes[0]);
  }

  protected _getParameters(): ArbitraryObject {
    if (this._options.categoryId) {
      return Core.extend(
        super._getParameters() as object,
        {
          categoryID: this._options.categoryId,
        } as object,
      ) as ArbitraryObject;
    }

    return super._getParameters();
  }
}

Core.enableLegacyInheritance(MediaListUpload);

export = MediaListUpload;
