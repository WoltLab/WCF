/**
 * Data handler for a form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Field
 * @since 5.2
 */

import * as Core from "../../../Core";
import { FormBuilderData } from "../Data";

class Field {
  protected _fieldId: string;
  protected _field: HTMLElement | null;

  constructor(fieldId: string) {
    this.init(fieldId);
  }

  /**
   * Initializes the field.
   */
  protected init(fieldId: string): void {
    this._fieldId = fieldId;

    this._readField();
  }

  /**
   * Returns the current data of the field or a promise returning the current data
   * of the field.
   *
   * @return	{Promise|data}
   */
  protected _getData(): FormBuilderData {
    throw new Error("Missing implementation of WoltLabSuite/Core/Form/Builder/Field/Field._getData!");
  }

  /**
   * Reads the field's HTML element.
   */
  protected _readField(): void {
    this._field = document.getElementById(this._fieldId);

    if (this._field === null) {
      throw new Error("Unknown field with id '" + this._fieldId + "'.");
    }
  }

  /**
   * Destroys the field.
   *
   * This function is useful for remove registered elements from other APIs like dialogs.
   */
  public destroy(): void {
    // does nothinbg
  }

  /**
   * Returns a promise providing the current data of the field.
   */
  public getData(): Promise<FormBuilderData> {
    return Promise.resolve(this._getData());
  }

  /**
   * Returns the id of the field.
   */
  public getId(): string {
    return this._fieldId;
  }
}

Core.enableLegacyInheritance(Field);

export = Field;
