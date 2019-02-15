/**
 * Provides helper functions for file handling.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/FileUtil
 */
define(['Dictionary', 'StringUtil'], function(Dictionary, StringUtil) {
	"use strict";
	
	var _fileExtensionIconMapping = Dictionary.fromObject({
		// archive
		zip: 'archive',
		rar: 'archive',
		tar: 'archive',
		gz: 'archive',
		
		// audio
		mp3: 'audio',
		ogg: 'audio',
		wav: 'audio',
		
		// code
		php: 'code',
		html: 'code',
		htm: 'code',
		tpl: 'code',
		js: 'code',
		
		// excel
		xls: 'excel',
		ods: 'excel',
		xlsx: 'excel',
		
		// image
		gif: 'image',
		jpg: 'image',
		jpeg: 'image',
		png: 'image',
		bmp: 'image',
		
		// video
		avi: 'video',
		wmv: 'video',
		mov: 'video',
		mp4: 'video',
		mpg: 'video',
		mpeg: 'video',
		flv: 'video',
		
		// pdf
		pdf: 'pdf',
		
		// powerpoint
		ppt: 'powerpoint',
		pptx: 'powerpoint',
		
		// text
		txt: 'text',
		
		// word
		doc: 'word',
		docx: 'word',
		odt: 'word'
	});
	
	return {
		/**
		 * Formats the given filesize.
		 * 
		 * @param	{integer}	byte		number of bytes
		 * @param	{integer}	precision	number of decimals
		 * @return	{string}	formatted filesize
		 */
		formatFilesize: function(byte, precision) {
			if (precision === undefined) {
				precision = 2;
			}
			
			var symbol = 'Byte';
			if (byte >= 1000) {
				byte /= 1000;
				symbol = 'kB';
			}
			if (byte >= 1000) {
				byte /= 1000;
				symbol = 'MB';
			}
			if (byte >= 1000) {
				byte /= 1000;
				symbol = 'GB';
			}
			if (byte >= 1000) {
				byte /= 1000;
				symbol = 'TB';
			}
			
			return StringUtil.formatNumeric(byte, -precision) + ' ' + symbol;
		},
		
		/**
		 * Returns the icon name for given filename.
		 * 
		 * Note: For any file icon name like `fa-file-word`, only `word`
		 * will be returned by this method.
		 *
		 * @parsm	{string}	filename	name of file for which icon name will be returned
		 * @return	{string}	FontAwesome icon name
		 */
		getIconNameByFilename: function(filename) {
			var lastDotPosition = filename.lastIndexOf('.');
			if (lastDotPosition !== false) {
				var extension = filename.substr(lastDotPosition + 1);
				
				if (_fileExtensionIconMapping.has(extension)) {
					return _fileExtensionIconMapping.get(extension);
				}
			}
			
			return '';
		}
	};
});
