/**
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Language", "../Dialog"], function (require, exports, tslib_1, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.showDialog = void 0;
    Language = (0, tslib_1.__importStar)(Language);
    Dialog_1 = (0, tslib_1.__importDefault)(Dialog_1);
    class UiRedactorTable {
        open(options) {
            Dialog_1.default.open(this);
            this.callbackSubmit = options.submitCallback;
        }
        _dialogSubmit() {
            // check if rows and cols are within the boundaries
            let isValid = true;
            ["rows", "cols"].forEach((type) => {
                const input = document.getElementById("redactor-table-" + type);
                if (+input.value < 1 || +input.value > 100) {
                    isValid = false;
                }
            });
            if (!isValid) {
                return;
            }
            this.callbackSubmit();
            Dialog_1.default.close(this);
        }
        _dialogSetup() {
            return {
                id: "redactorDialogTable",
                options: {
                    onShow: () => {
                        const rows = document.getElementById("redactor-table-rows");
                        rows.value = "2";
                        const cols = document.getElementById("redactor-table-cols");
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
          <button id="redactor-modal-button-action" class="buttonPrimary" data-type="submit">${Language.get("wcf.global.button.insert")}</button>
        </div>`,
            };
        }
    }
    let uiRedactorTable;
    function showDialog(options) {
        if (!uiRedactorTable) {
            uiRedactorTable = new UiRedactorTable();
        }
        uiRedactorTable.open(options);
    }
    exports.showDialog = showDialog;
});
