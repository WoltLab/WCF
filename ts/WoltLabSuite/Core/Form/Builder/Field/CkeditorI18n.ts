/**
 * Data handler for CKEditor with l10n support.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import ValueI18n from "./ValueI18n";
import { FormBuilderData } from "../Data";
import { dispatchToCkeditor } from "WoltLabSuite/Core/Component/Ckeditor/Event";
import { getCkeditorById } from "WoltLabSuite/Core/Component/Ckeditor";

export class CkeditorI18n extends ValueI18n {
  protected _getData(): FormBuilderData {
    // CKEditor does not permanently mirror the contents to the <textarea>.
    (this._field as HTMLTextAreaElement).value = getCkeditorById(this._fieldId)!.getHtml();

    return super._getData();
  }

  destroy(): void {
    super.destroy();

    dispatchToCkeditor(this._field!).destroy();
  }
}

export default CkeditorI18n;
