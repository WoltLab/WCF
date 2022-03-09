define(["require", "exports", "tslib", "../../../Core", "../../../Ui/Page/Search"], function (require, exports, tslib_1, Core, UiPageSearch) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    UiPageSearch = tslib_1.__importStar(UiPageSearch);
    class AcpUiCodeMirrorPage {
        constructor(elementId) {
            this.element = document.getElementById(elementId);
            const insertButton = document.getElementById(`codemirror-${elementId}-page`);
            insertButton.addEventListener("click", (ev) => this._click(ev));
        }
        _click(event) {
            event.preventDefault();
            UiPageSearch.open((pageID) => this._insert(pageID));
        }
        _insert(pageID) {
            this.element.codemirror.replaceSelection(`{{ page="${pageID}" }}`);
        }
    }
    Core.enableLegacyInheritance(AcpUiCodeMirrorPage);
    return AcpUiCodeMirrorPage;
});
