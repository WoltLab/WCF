define(['WoltLabSuite/Core/Ui/Page/Search'], function (UiPageSearch) {
    "use strict";
    function AcpUiCodeMirrorPage(elementId) { this.init(elementId); }
    AcpUiCodeMirrorPage.prototype = {
        init: function (elementId) {
            this._element = elById(elementId);
            elById('codemirror-' + elementId + '-page').addEventListener('click', this._click.bind(this));
        },
        _click: function (event) {
            event.preventDefault();
            UiPageSearch.open(this._insert.bind(this));
        },
        _insert: function (pageID) {
            this._element.codemirror.replaceSelection('{{ page="' + pageID + '" }}');
        }
    };
    return AcpUiCodeMirrorPage;
});
