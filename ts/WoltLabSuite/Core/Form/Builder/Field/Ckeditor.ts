/**
 * Data handler for CKEditor.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";
import { dispatchToCkeditor } from "WoltLabSuite/Core/Component/Ckeditor/Event";
import { getCkeditorById } from "WoltLabSuite/Core/Component/Ckeditor";

export class Ckeditor extends Field {
  protected _getData(): FormBuilderData {
    const ckeditor = getCkeditorById(this._fieldId)!;

    return {
      [this._fieldId]: ckeditor.getHtml(),
    };
  }

  destroy(): void {
    dispatchToCkeditor(this._field!).destroy();
  }
}

export default Ckeditor;
