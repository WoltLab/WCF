/**
 * I18n interface for wysiwyg input fields.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Language/Text
 */

import { I18nValues, InputOrTextarea, Languages } from "./Input";
import * as LanguageInput from "./Input";

/**
 * Refreshes the editor content on language switch.
 */
function callbackSelect(element: InputOrTextarea): void {
  if (window.jQuery !== undefined) {
    window.jQuery(element).redactor("code.set", element.value);
  }
}

/**
 * Refreshes the input element value on submit.
 */
function callbackSubmit(element: InputOrTextarea): void {
  if (window.jQuery !== undefined) {
    element.value = window.jQuery(element).redactor("code.get") as string;
  }
}

/**
 * Initializes an WYSIWYG input field.
 */
export function init(
  elementId: string,
  values: I18nValues,
  availableLanguages: Languages,
  forceSelection: boolean,
): void {
  const element = document.getElementById(elementId);
  if (!element || element.nodeName !== "TEXTAREA" || !element.classList.contains("wysiwygTextarea")) {
    throw new Error(`Expected <textarea class="wysiwygTextarea" /> for id '${elementId}'.`);
  }

  LanguageInput.init(elementId, values, availableLanguages, forceSelection);

  LanguageInput.registerCallback(elementId, "select", callbackSelect);
  LanguageInput.registerCallback(elementId, "submit", callbackSubmit);
}
