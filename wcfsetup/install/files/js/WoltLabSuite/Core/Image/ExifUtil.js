/**
 * Provides helper functions for Exif metadata handling.
 *
 * @author	Maximilian Mader
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Image/ExifUtil
 */
define([], function() {
	"use strict";
	
	var _tagNames = {
		'SOI':   0xD8, // Start of image
		'APP0':  0xE0, // JFIF tag
		'APP1':  0xE1, // EXIF / XMP
		'APP2':  0xE2, // General purpose tag
		'APP3':  0xE3, // General purpose tag
		'APP4':  0xE4, // General purpose tag
		'APP5':  0xE5, // General purpose tag
		'APP6':  0xE6, // General purpose tag
		'APP7':  0xE7, // General purpose tag
		'APP8':  0xE8, // General purpose tag
		'APP9':  0xE9, // General purpose tag
		'APP10': 0xEA, // General purpose tag
		'APP11': 0xEB, // General purpose tag
		'APP12': 0xEC, // General purpose tag
		'APP13': 0xED, // General purpose tag
		'APP14': 0xEE, // Often used to store copyright information
		'COM':   0xFE, // Comments
	};
	
	// Known sequence signatures
	var _signatureEXIF = 'Exif';
	var _signatureXMP  = 'http://ns.adobe.com/xap/1.0/';
	
	return {
		/**
		 * Extracts the EXIF / XMP sections of a JPEG blob.
		 *
		 * @param       blob    {Blob}                                  JPEG blob
		 * @returns             {Promise<Uint8Array | TypeError>}       Promise resolving with the EXIF / XMP sections
		 */
		getExifBytesFromJpeg: function (blob) {
			return new Promise(function (resolve, reject) {
				if (!(blob instanceof Blob) && !(blob instanceof File)) {
					return reject(new TypeError('The argument must be a Blob or a File'));
				}
				
				var reader = new FileReader();
				
				reader.addEventListener('error', function () {
					reader.abort();
					reject(reader.error);
				});
				
				reader.addEventListener('load', function() {
					var buffer = reader.result;
					var bytes = new Uint8Array(buffer);
					var exif = new Uint8Array();
					
					if (bytes[0] !== 0xFF && bytes[1] !== _tagNames.SOI) {
						return reject(new Error('Not a JPEG'));
					}
					
					for (var i = 2; i < bytes.length;) {
						// each sequence starts with 0xFF
						if (bytes[i] !== 0xFF) break;
						
						var length = 2 + ((bytes[i + 2] << 8) | bytes[i + 3]);
						
						// Check if the next byte indicates an EXIF sequence
						if (bytes[i + 1] === _tagNames.APP1) {
							var signature = '';
							for (var j = i + 4; bytes[j] !== 0 && j < bytes.length; j++) {
								signature += String.fromCharCode(bytes[j]);
							}
							
							// Only copy Exif and XMP data
							if (signature === _signatureEXIF || signature === _signatureXMP) {
								// append the found EXIF sequence, usually only a single EXIF (APP1) sequence should be defined
								var sequence = Array.prototype.slice.call(bytes, i, length + i); // IE11 does not have slice in the Uint8Array prototype
								var concat = new Uint8Array(exif.length + sequence.length);
								concat.set(exif);
								concat.set(sequence, exif.length);
								exif = concat;
							}
						}
						
						i += length
					}
					
					// No EXIF data found
					resolve(exif);
				});
				
				reader.readAsArrayBuffer(blob);
			});
		},
		
		/**
		 * Removes all EXIF and XMP sections of a JPEG blob.
		 *
		 * @param       blob    {Blob}                          JPEG blob
		 * @returns             {Promise<Blob | TypeError>}     Promise resolving with the altered JPEG blob
		 */
		removeExifData: function (blob) {
			return new Promise(function (resolve, reject) {
				if (!(blob instanceof Blob) && !(blob instanceof File)) {
					return reject(new TypeError('The argument must be a Blob or a File'));
				}
				
				var reader = new FileReader();
				
				reader.addEventListener('error', function () {
					reader.abort();
					reject(reader.error);
				});
				
				reader.addEventListener('load', function () {
					var buffer = reader.result;
					var bytes = new Uint8Array(buffer);
					
					if (bytes[0] !== 0xFF && bytes[1] !== _tagNames.SOI) {
						return reject(new Error('Not a JPEG'));
					}
					
					for (var i = 2; i < bytes.length;) {
						// each sequence starts with 0xFF
						if (bytes[i] !== 0xFF) break;
						
						var length = 2 + ((bytes[i + 2] << 8) | bytes[i + 3]);
						
						// Check if the next byte indicates an EXIF sequence
						if (bytes[i + 1] === _tagNames.APP1) {
							var signature = '';
							for (var j = i + 4; bytes[j] !== 0 && j < bytes.length; j++) {
								signature += String.fromCharCode(bytes[j]);
							}
							
							// Only remove Exif and XMP data
							if (signature === _signatureEXIF || signature === _signatureXMP) {
								var start = Array.prototype.slice.call(bytes, 0, i);
								var end = Array.prototype.slice.call(bytes, i + length);
								bytes = new Uint8Array(start.length + end.length);
								bytes.set(start, 0);
								bytes.set(end, start.length);
							}
						}
						else {
							i += length;
						}
					}
					
					resolve(new Blob([bytes], {type: blob.type}));
				});
				
				reader.readAsArrayBuffer(blob);
			});
		},
		
		/**
		 * Overrides the APP1 (EXIF / XMP) sections of a JPEG blob with the given data.
		 *
		 * @param       blob    {Blob}                  JPEG blob
		 * @param       exif    {Uint8Array}            APP1 sections
		 * @returns             {Promise<Blob | never>} Promise resolving with the altered JPEG blob
		 */
		setExifData: function (blob, exif) {
			return this.removeExifData(blob).then(function (blob) {
				return new Promise(function (resolve) {
					var reader = new FileReader();
					
					reader.addEventListener('error', function () {
						reader.abort();
						reject(reader.error);
					});
					
					reader.addEventListener('load', function () {
						var buffer = reader.result;
						var bytes = new Uint8Array(buffer);
						var offset = 2;
						
						// check if the second tag is the JFIF tag
						if (bytes[2] === 0xFF && bytes[3] === _tagNames.APP0) {
							offset += 2 + ((bytes[4] << 8) | bytes[5]);
						}
						
						var start = Array.prototype.slice.call(bytes, 0, offset);
						var end = Array.prototype.slice.call(bytes, offset);
						
						bytes = new Uint8Array(start.length + exif.length + end.length);
						bytes.set(start);
						bytes.set(exif, offset);
						bytes.set(end, offset + exif.length);
						
						resolve(new Blob([bytes], {type: blob.type}));
					});
					
					reader.readAsArrayBuffer(blob);
				});
			});
		}
	};
});
