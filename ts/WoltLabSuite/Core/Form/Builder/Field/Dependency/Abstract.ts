/**
 * Abstract implementation of a form field dependency.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */

import { escapeAttributeSelector } from "WoltLabSuite/Core/Dom/Util";
import * as DependencyManager from "./Manager";

function isInput(node: Node): node is HTMLInputElement {
  return node.nodeName === "INPUT";
}

abstract class FormBuilderFormFieldDependency {
  protected _dependentElement: HTMLElement;
  protected _field: HTMLElement;
  protected _fields: HTMLElement[];
  protected _noField?: HTMLInputElement;

  constructor(dependentElementId: string, fieldId: string) {
    this.init(dependentElementId, fieldId);
  }

  /**
   * Returns `true` if the dependency is met.
   */
  public checkDependency(): boolean {
    throw new Error(
      "Missing implementation of WoltLabSuite/Core/Form/Builder/Field/Dependency/Abstract.checkDependency!",
    );
  }

  /**
   * Return the node whose availability depends on the value of a field.
   */
  public getDependentNode(): HTMLElement {
    return this._dependentElement;
  }

  /**
   * Returns the field the availability of the element dependents on.
   */
  public getField(): HTMLElement {
    return this._field;
  }

  /**
   * Returns all fields requiring event listeners for this dependency to be properly resolved.
   */
  public getFields(): HTMLElement[] {
    return this._fields;
  }

  /**
   * Initializes the new dependency object.
   */
  protected init(dependentElementId: string, fieldId: string): void {
    this._dependentElement = document.getElementById(dependentElementId)!;
    if (this._dependentElement === null) {
      throw new Error("Unknown dependent element with container id '" + dependentElementId + "Container'.");
    }

    this._field = document.getElementById(fieldId)!;
    if (this._field === null) {
      this._fields = [];
      document
        .querySelectorAll(`input[type=radio][name="${escapeAttributeSelector(fieldId)}"]`)
        .forEach((field: HTMLInputElement) => {
          this._fields.push(field);
        });

      if (!this._fields.length) {
        document
          .querySelectorAll(`input[type=checkbox][name="${escapeAttributeSelector(fieldId)}[]"]`)
          .forEach((field: HTMLInputElement) => {
            this._fields.push(field);
          });

        if (!this._fields.length) {
          throw new Error("Unknown field with id '" + fieldId + "'.");
        }
      }
    } else {
      this._fields = [this._field];

      // Handle special case of boolean form fields that have two form fields.
      if (isInput(this._field) && this._field.type === "radio" && this._field.dataset.noInputId !== "") {
        this._noField = document.getElementById(this._field.dataset.noInputId!)! as HTMLInputElement;
        if (this._noField === null) {
          throw new Error("Cannot find 'no' input field for input field '" + fieldId + "'");
        }

        this._fields.push(this._noField);
      }
    }

    DependencyManager.addDependency(this);
  }
}

export = FormBuilderFormFieldDependency;
