/**
 * Provides helper functions to work with files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/File/Util
 */
define([], function() {
	/**
	 * @exports	WoltLab/WCF/File/Util
	 */
	var FileUtil = {
		/**
		 * Returns the FontAwesome icon CSS class name for a mime type.
		 * 
		 * @param	{string}	mimeType	mime type of the relevant file
		 * @return	{string}	FontAwesome icon CSS class name for the mime type
		 */
		getIconClassByMimeType: function(mimeType) {
			if (mimeType.substr(0, 6) == 'image/') {
				return 'fa-file-image-o';
			}
			else if (mimeType.substr(0, 6) == 'video/') {
				return 'fa-file-video-o';
			}
			else if (mimeType.substr(0, 6) == 'audio/') {
				return 'fa-file-sound-o';
			}
			else if (mimeType.substr(0, 5) == 'text/') {
				return 'fa-file-text-o';
			}
			else {
				switch (mimeType) {
					case 'application/msword':
					case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
						return 'fa-file-word-o';
					break;
					
					case 'application/pdf':
						return 'fa-file-pdf-o';
					break;
					
					case 'application/vnd.ms-powerpoint':
					case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
						return 'fa-file-powerpoint-o';
					break;
					
					case 'application/vnd.ms-excel':
					case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
						return 'fa-file-excel-o';
					break;
					
					case 'application/zip':
					case 'application/x-tar':
					case 'application/x-gzip':
						return 'fa-file-archive-o';
					break;
					
					case 'application/xml':
						return 'fa-file-text-o';
					break;
				}
			}
			
			return 'fa-file-o';
		}
	};
	
	return FileUtil;
});
