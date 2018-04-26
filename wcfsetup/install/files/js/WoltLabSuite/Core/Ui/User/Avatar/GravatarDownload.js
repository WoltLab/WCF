/**
 * Simple OptIn before user's gravatar get's downloaded for DSGVO-reasons
 *
 * @author	Florian Gail
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/Avatar/GravatarDownload
 */
define(['Ui/Confirmation'], function (UiConfirmation) {
	"use strict";

	return {
		init: function () {
			var trigger = elBySel('.gravatarPreviewDownload');
			trigger.addEventListener(WCF_CLICK_EVENT, this._optIn.bind(this, trigger));
		},

		_optIn: function (element, event) {
			var self = this;

			UiConfirmation.show({
				legacyCallback: function (action) {
					if (action === 'cancel') return;
					self._download();
				},
				message: elBySel('.gravatarPreviewDownload').dataset.confirmMessageHtml,
				parameters: undefined,
				template: '',
				messageIsHtml: true
			});
		},

		_download: function (element, event) {
			elBySel('.gravatarPreview').src = elBySel('.gravatarPreviewDownload').dataset.src;
		}
	};
});
