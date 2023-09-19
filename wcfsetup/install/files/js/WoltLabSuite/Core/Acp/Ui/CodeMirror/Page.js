define(["require", "exports", "tslib", "../../../Ui/Page/Search"], function (require, exports, tslib_1, UiPageSearch) {
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
            this.element.codemirror.replaceSelection(`{{ page="${pageID}" }}`);
        }
    }
    return AcpUiCodeMirrorPage;
});
