define(['Language', 'Ui/Dialog'], function (Language, UiDialog) {
    return {
        init: function () {
            elBySelAll('.jsButtonCopyBox', undefined, (function (button) {
                button.addEventListener('click', this._click.bind(this));
            }).bind(this));
        },
        /**
         * @param {Event} event
         * @protected
         */
        _click: function (event) {
            event.preventDefault();
            UiDialog.open(this);
        },
        _dialogSetup: function () {
            return {
                id: 'acpBoxCopyDialog',
                options: {
                    title: Language.get('wcf.acp.box.copy')
                }
            };
        }
    };
});
