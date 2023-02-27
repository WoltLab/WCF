import Field from "./Field";
import { FormBuilderData } from "../Data";
import { escapeAttributeSelector } from "WoltLabSuite/Core/Dom/Util";

class SimpleAcl extends Field {
  protected _getData(): FormBuilderData {
    const groupIds = Array.from(
      document.querySelectorAll(`input[name="${escapeAttributeSelector(this._fieldId)}[group][]"]`),
    ).map((input: HTMLInputElement) => input.value);

    const usersIds = Array.from(
      document.querySelectorAll(`input[name="${escapeAttributeSelector(this._fieldId)}[user][]"]`),
    ).map((input: HTMLInputElement) => input.value);

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

export = SimpleAcl;
