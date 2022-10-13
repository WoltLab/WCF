/**
 * Data handler for a content language form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Language/ContentLanguage
 * @since 5.2
 */

import Value from "../Value";
import * as LanguageChooser from "../../../../Language/Chooser";

class ContentLanguage extends Value {
  public destroy(): void {
    LanguageChooser.removeChooser(this._fieldId);
  }
}

export = ContentLanguage;
