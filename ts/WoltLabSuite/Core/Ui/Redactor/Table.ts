import * as Language from "../../Language";
import UiDialog from "../Dialog";
import { DialogCallbackObject, DialogCallbackSetup } from "../Dialog/Data";

type CallbackSubmit = () => void;

interface TableOptions {
  submitCallback: CallbackSubmit;
}

class UiRedactorTable implements DialogCallbackObject {
  protected callbackSubmit: CallbackSubmit;

  open(options: TableOptions): void {
    UiDialog.open(this);

    this.callbackSubmit = options.submitCallback;
  }

  _dialogSubmit(): void {
    // check if rows and cols are within the boundaries
    let isValid = true;
    ["rows", "cols"].forEach((type) => {
      const input = document.getElementById("redactor-table-" + type) as HTMLInputElement;
      if (+input.value < 1 || +input.value > 100) {
        isValid = false;
      }
    });

    if (!isValid) {
      return;
    }

    this.callbackSubmit();

    UiDialog.close(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "redactorDialogTable",
      options: {
        onShow: () => {
          const rows = document.getElementById("redactor-table-rows") as HTMLInputElement;
          rows.value = "2";

          const cols = document.getElementById("redactor-table-cols") as HTMLInputElement;
          cols.value = "3";
        },

        title: Language.get("wcf.editor.table.insertTable"),
      },
      source: `<dl>
          <dt>
            <label for="redactor-table-rows">${Language.get("wcf.editor.table.rows")}</label>
          </dt>
          <dd>
            <input type="number" id="redactor-table-rows" class="small" min="1" max="100" value="2" data-dialog-submit-on-enter="true">
          </dd>
        </dl>
        <dl>
          <dt>
            <label for="redactor-table-cols">${Language.get("wcf.editor.table.cols")}</label>
          </dt>
          <dd>
            <input type="number" id="redactor-table-cols" class="small" min="1" max="100" value="3" data-dialog-submit-on-enter="true">
          </dd>
        </dl>
        <div class="formSubmit">
          <button id="redactor-modal-button-action" class="buttonPrimary" data-type="submit">${Language.get(
            "wcf.global.button.insert",
          )}</button>
        </div>`,
    };
  }
}

let uiRedactorTable: UiRedactorTable;

export function showDialog(options: TableOptions): void {
  if (!uiRedactorTable) {
    uiRedactorTable = new UiRedactorTable();
  }

  uiRedactorTable.open(options);
}
