/**
 * Generic interface for drag and Drop file uploads.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/DragAndDrop
 */
define(['Core', 'EventHandler', 'WoltLabSuite/Core/Ui/Redactor/DragAndDrop'], function (Core, EventHandler, UiRedactorDragAndDrop) {
	/**
	 * @exports     WoltLabSuite/Core/Ui/DragAndDrop
	 */
	return {
		/**
		 * @param       {Object}        options
		 */
		register: function (options) {
			var uuid = Core.getUuid();
			options = Core.extend({
				element: '',
				elementId: '',
				onDrop: function(data) {
					/* data: { file: File } */
				},
				onGlobalDrop: function (data) {
					/* data: { cancelDrop: boolean, event: DragEvent } */
				}
			});
			
			EventHandler.add('com.woltlab.wcf.redactor2', 'dragAndDrop_' + options.elementId, options.onDrop);
			EventHandler.add('com.woltlab.wcf.redactor2', 'dragAndDrop_globalDrop_' + options.elementId, options.onGlobalDrop);
			
			UiRedactorDragAndDrop.init({
				uuid: uuid,
				$editor: [options.element],
				$element: [{id: options.elementId}]
			});
		}
	};
});
