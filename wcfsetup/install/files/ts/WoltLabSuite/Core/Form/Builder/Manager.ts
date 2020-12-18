/**
 * Manager for registered Ajax forms and its fields that can be used to retrieve the current data
 * of the registered forms.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Manager
 * @since 5.2
 */

import * as Core from "../../Core";
import * as EventHandler from "../../Event/Handler";
import Field from "./Field/Field";
import * as DependencyManager from "./Field/Dependency/Manager";
import { FormBuilderData } from "./Data";

type FormId = string;
type FieldId = string;

const _fields = new Map<FormId, Map<FieldId, Field>>();
const _forms = new Map<FormId, HTMLElement>();

/**
 * Returns a promise returning the data of the form with the given id.
 */
export function getData(formId: FieldId): Promise<FormBuilderData> {
  if (!hasForm(formId)) {
    throw new Error("Unknown form with id '" + formId + "'.");
  }

  const promises: Promise<FormBuilderData>[] = [];

  _fields.get(formId)!.forEach((field) => {
    const fieldData = field.getData();

    if (!(fieldData instanceof Promise)) {
      throw new TypeError("Data for field with id '" + field.getId() + "' is no promise.");
    }

    promises.push(fieldData);
  });

  return Promise.all(promises).then((promiseData: FormBuilderData[]) => {
    return promiseData.reduce((carry, current) => Core.extend(carry, current), {});
  });
}

/**
 * Returns the registered form field with given.
 *
 * @since 5.2.3
 */
export function getField(formId: FieldId, fieldId: FieldId): Field {
  if (!hasField(formId, fieldId)) {
    throw new Error("Unknown field with id '" + formId + "' for form with id '" + fieldId + "'.");
  }

  return _fields.get(formId)!.get(fieldId)!;
}

/**
 * Returns the registered form with given id.
 */
export function getForm(formId: FieldId): HTMLElement {
  if (!hasForm(formId)) {
    throw new Error("Unknown form with id '" + formId + "'.");
  }

  return _forms.get(formId)!;
}

/**
 * Returns `true` if a field with the given id has been registered for the form with the given id
 * and `false` otherwise.
 */
export function hasField(formId: FieldId, fieldId: FieldId): boolean {
  if (!hasForm(formId)) {
    throw new Error("Unknown form with id '" + formId + "'.");
  }

  return _fields.get(formId)!.has(fieldId);
}

/**
 * Returns `true` if a form with the given id has been registered and `false` otherwise.
 */
export function hasForm(formId: FieldId): boolean {
  return _forms.has(formId);
}

/**
 * Registers the given field for the form with the given id.
 */
export function registerField(formId: FieldId, field: Field): void {
  if (!hasForm(formId)) {
    throw new Error("Unknown form with id '" + formId + "'.");
  }

  if (!(field instanceof Field)) {
    throw new Error("Add field is no instance of 'WoltLabSuite/Core/Form/Builder/Field/Field'.");
  }

  const fieldId = field.getId();

  if (hasField(formId, fieldId)) {
    throw new Error(
      "Form field with id '" + fieldId + "' has already been registered for form with id '" + formId + "'.",
    );
  }

  _fields.get(formId)!.set(fieldId, field);

  EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "registerField", {
    field: field,
    formId: formId,
  });
}

/**
 * Registers the form with the given id.
 */
export function registerForm(formId: FieldId): void {
  if (hasForm(formId)) {
    throw new Error("Form with id '" + formId + "' has already been registered.");
  }

  const form = document.getElementById(formId);
  if (form === null) {
    throw new Error("Unknown form with id '" + formId + "'.");
  }

  _forms.set(formId, form);
  _fields.set(formId, new Map<FieldId, Field>());

  EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "registerForm", {
    formId: formId,
  });
}

/**
 * Unregisters the form with the given id.
 */
export function unregisterForm(formId: FieldId): void {
  if (!hasForm(formId)) {
    throw new Error("Unknown form with id '" + formId + "'.");
  }

  EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "beforeUnregisterForm", {
    formId: formId,
  });

  _forms.delete(formId);

  _fields.get(formId)!.forEach(function (field) {
    field.destroy();
  });

  _fields.delete(formId);

  DependencyManager.unregister(formId);

  EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "afterUnregisterForm", {
    formId: formId,
  });
}
