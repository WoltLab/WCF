define(['Language', 'Ui/Dialog'], function(Language, UiDialog) {
	return {
		init: function () {
			elBySelAll('.jsButtonCopyPage', undefined, (function(button) {
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
				id: 'acpPageCopyDialog',
				options: {
					title: Language.get('wcf.acp.page.copy')
				}
			};
		}
	};
});
