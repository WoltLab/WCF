/**
 * Data handler for a captcha form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Captcha
 * @since 5.2
 */

import Field from "./Field";
import ControllerCaptcha from "../../../Controller/Captcha";
import { FormBuilderData } from "../Data";
import * as Core from "../../../Core";

class Captcha extends Field {
  protected _getData(): FormBuilderData {
    if (ControllerCaptcha.has(this._fieldId)) {
      return ControllerCaptcha.getData(this._fieldId) as FormBuilderData;
    }

    return {};
  }

  protected _readField(): void {
    // does nothing
  }

  destroy(): void {
    if (ControllerCaptcha.has(this._fieldId)) {
      ControllerCaptcha.delete(this._fieldId);
    }
  }
}

Core.enableLegacyInheritance(Captcha);

export = Captcha;
