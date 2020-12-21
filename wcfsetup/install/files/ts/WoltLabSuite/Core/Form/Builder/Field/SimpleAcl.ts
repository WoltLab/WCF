import Field from "./Field";
import { FormBuilderData } from "../Data";
import * as Core from "../../../Core";

class SimpleAcl extends Field {
  protected _getData(): FormBuilderData {
    const groupIds = Array.from(document.querySelectorAll('input[name="' + this._fieldId + '[group][]"]')).map(
      (input: HTMLInputElement) => input.value,
    );

    const usersIds = Array.from(document.querySelectorAll('input[name="' + this._fieldId + '[user][]"]')).map(
      (input: HTMLInputElement) => input.value,
    );

    return {
      [this._fieldId]: {
        group: groupIds,
        user: usersIds,
      },
    };
  }

  protected _readField(): void {
    // does nothing
  }
}

Core.enableLegacyInheritance(SimpleAcl);

export = SimpleAcl;
