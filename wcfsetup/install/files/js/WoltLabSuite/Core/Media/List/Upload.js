/**
 * Uploads media files.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Media/List/Upload
 */
define(
	[
		'Core', 'Dom/Util', '../Upload'
	],
	function(
		Core, DomUtil, MediaUpload
	)
{
	"use strict";
	
	/**
	 * @constructor
	 */
	function MediaListUpload(buttonContainerId, targetId, options) {
		MediaUpload.call(this, buttonContainerId, targetId, options);
	}
	Core.inherit(MediaListUpload, MediaUpload, {
		/**
		 * Creates the upload button.
		 */
		_createButton: function() {
			MediaListUpload._super.prototype._createButton.call(this);
			
			var icon = elCreate('span');
			icon.classList = 'icon icon16 fa-upload';
			DomUtil.prepend(icon, elBySel('span', this._button));
		}
	});
	
	return MediaListUpload;
});
