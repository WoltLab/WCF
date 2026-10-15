/**
 * Data handler for a trophy badge form builder field in an Ajax form.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 */
import Field from "./Field";
import { FormBuilderData } from "../Data";

class TrophyBadge extends Field {
  protected _getData(): FormBuilderData {
    return {
      [this._fieldId]: {
        iconName: this.#getValue("iconName"),
        iconColor: this.#getValue("iconColor"),
        badgeColor: this.#getValue("badgeColor"),
        borderColor: this.#getValue("borderColor"),
      },
    };
  }

  protected _readField(): void {}

  #getValue(name: string): string {
    const input = document.getElementById(`${this._fieldId}_${name}`);
    if (!(input instanceof HTMLInputElement)) {
      throw new Error(`Unknown input field '${name}' of the field '${this._fieldId}'.`);
    }

    return input.value;
  }
}

export = TrophyBadge;
