define(["require", "exports", "tslib", "../../../Ui/Page/Search", "../../../Component/CodeMirror"], function (require, exports, tslib_1, UiPageSearch, CodeMirror_1) {
    "use strict";
    UiPageSearch = tslib_1.__importStar(UiPageSearch);
    class AcpUiCodeMirrorPage {
        element;
        constructor(elementId) {
            this.element = document.getElementById(elementId);
            const insertButton = document.getElementById(`codemirror-${elementId}-page`);
            insertButton.addEventListener("click", () => this._click());
        }
        _click() {
            UiPageSearch.open((pageID) => this._insert(pageID));
        }
        _insert(pageID) {
            (0, CodeMirror_1.insertText)(this.element, `{{ page="${pageID}" }}`);
        }
    }
    return AcpUiCodeMirrorPage;
});
