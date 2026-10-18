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

    const allowAll = document.querySelector<HTMLInputElement>(
      `input[name="${escapeAttributeSelector(this._fieldId)}[allowAll]"]:checked`,
    );

    return {
      [this._fieldId]: {
        allowAll: allowAll?.value ?? "1",
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
