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
import DependencyManager from "./Field/Dependency/Manager";
import { FormBuilderData } from "./Data";

const _fields = new Map<string, Map<string, Field>>();
const _forms = new Map<string, HTMLElement>();

const FormBuilderManager = {
  /**
   * Returns a promise returning the data of the form with the given id.
   */
  getData(formId: string): Promise<FormBuilderData> {
    if (!this.hasForm(formId)) {
      throw new Error("Unknown form with id '" + formId + "'.");
    }

    const promises: Promise<FormBuilderData>[] = [];

    _fields.get(formId)!.forEach(function (field) {
      const fieldData = field.getData();

      if (!(fieldData instanceof Promise)) {
        throw new TypeError("Data for field with id '" + field.getId() + "' is no promise.");
      }

      promises.push(fieldData);
    });

    return Promise.all(promises).then(function (promiseData: FormBuilderData[]) {
      let data = {};

      promiseData.forEach((_data) => {
        data = Core.extend(data, _data);
      });

      return data;
    });
  },

  /**
   * Returns the registered form field with given.
   *
   * @since 5.2.3
   */
  getField(formId: string, fieldId: string): Field {
    if (!this.hasField(formId, fieldId)) {
      throw new Error("Unknown field with id '" + formId + "' for form with id '" + fieldId + "'.");
    }

    return _fields.get(formId)!.get(fieldId)!;
  },

  /**
   * Returns the registered form with given id.
   */
  getForm(formId: string): HTMLElement {
    if (!this.hasForm(formId)) {
      throw new Error("Unknown form with id '" + formId + "'.");
    }

    return _forms.get(formId)!;
  },

  /**
   * Returns `true` if a field with the given id has been registered for the form with the given id
   * and `false` otherwise.
   */
  hasField(formId: string, fieldId: string): boolean {
    if (!this.hasForm(formId)) {
      throw new Error("Unknown form with id '" + formId + "'.");
    }

    return _fields.get(formId)!.has(fieldId);
  },

  /**
   * Returns `true` if a form with the given id has been registered and `false` otherwise.
   */
  hasForm(formId): boolean {
    return _forms.has(formId);
  },

  /**
   * Registers the given field for the form with the given id.
   */
  registerField(formId: string, field: Field): void {
    if (!this.hasForm(formId)) {
      throw new Error("Unknown form with id '" + formId + "'.");
    }

    if (!(field instanceof Field)) {
      throw new Error("Add field is no instance of 'WoltLabSuite/Core/Form/Builder/Field/Field'.");
    }

    const fieldId = field.getId();

    if (this.hasField(formId, fieldId)) {
      throw new Error(
        "Form field with id '" + fieldId + "' has already been registered for form with id '" + formId + "'.",
      );
    }

    _fields.get(formId)!.set(fieldId, field);

    EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "registerField", {
      field: field,
      formId: formId,
    });
  },

  /**
   * Registers the form with the given id.
   */
  registerForm(formId: string): void {
    if (this.hasForm(formId)) {
      throw new Error("Form with id '" + formId + "' has already been registered.");
    }

    const form = document.getElementById(formId);
    if (form === null) {
      throw new Error("Unknown form with id '" + formId + "'.");
    }

    _forms.set(formId, form);
    _fields.set(formId, new Map<string, Field>());

    EventHandler.fire("WoltLabSuite/Core/Form/Builder/Manager", "registerForm", {
      formId: formId,
    });
  },

  /**
   * Unregisters the form with the given id.
   */
  unregisterForm: function (formId: string): void {
    if (!this.hasForm(formId)) {
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
  },
};

Core.enableLegacyInheritance(FormBuilderManager);

export = FormBuilderManager;
