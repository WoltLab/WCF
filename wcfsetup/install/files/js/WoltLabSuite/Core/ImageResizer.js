/**
 * This module allows resizing and conversion of HTMLImageElements to Blob and File objects
 *
 * @author	Maximilian Mader
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/ImageResizer
 */
define([
	'WoltLabSuite/Core/FileUtil',
	'WoltLabSuite/Core/ExifUtil',
	'Pica'
], function(FileUtil, ExifUtil, Pica) {
	"use strict";
	
	var pica = new Pica({features: ['js', 'wasm', 'ww']});
	
	/**
	 * @constructor
	 */
	function ImageResizer() { }
	ImageResizer.prototype = {
		maxWidth: 800,
		maxHeight: 600,
		quality: 0.8,
		fileType: 'image/jpeg',
		
		/**
		 * Sets the default maximum width for this instance
		 *
		 * @param       {Number}        value   the new default maximum width
		 * @returns     {ImageResizer}          this ImageResizer instance
		 */
		setMaxWidth: function (value) {
			if (value == null) value = ImageResizer.prototype.maxWidth;
			
			this.maxWidth = value;
			return this;
		},
		
		/**
		 * Sets the default maximum height for this instance
		 *
		 * @param       {Number}        value   the new default maximum height
		 * @returns     {ImageResizer}          this ImageResizer instance
		 */
		setMaxHeight: function (value) {
			if (value == null) value = ImageResizer.prototype.maxHeight;
			
			this.maxHeight = value;
			return this;
		},
		
		/**
		 * Sets the default quality for this instance
		 *
		 * @param       {Number}        value   the new default quality
		 * @returns     {ImageResizer}          this ImageResizer instance
		 */
		setQuality: function (value) {
			if (value == null) value = ImageResizer.prototype.quality;
			
			this.quality = value;
			return this;
		},
		
		/**
		 * Sets the default file type for this instance
		 *
		 * @param       {Number}        value   the new default file type
		 * @returns     {ImageResizer}          this ImageResizer instance
		 */
		setFileType: function (value) {
			if (value == null) value = ImageResizer.prototype.fileType;
			
			this.fileType = value;
			return this;
		},
		
		/**
		 * Converts the result of ImageResizer.resize() into a File
		 *
		 * @param       {Object{exif: Uint8Array|undefined, image: Canvas|File} result  the result of ImageResizer.resize()
		 * @param       {String}        fileName        the name of the returned file
		 * @param       {String}        [fileType]      the type of the returned image
		 * @param       {Number}        [quality]       quality setting, currently only effective for "image/jpeg"
		 * @returns     {Promise<File>} the File object
		 */
		getFile: function (result, fileName, fileType, quality) {
			fileType = fileType || this.fileType;
			quality = quality || this.quality;
			
			var basename = fileName.match(/(.+)(\..+?)$/);
			
			return pica.toBlob(result.image, fileType, quality)
				.then(function (blob) {
					if (fileType === 'image/jpeg' && typeof result.exif !== 'undefined') {
						return ExifUtil.setExifData(blob, result.exif);
					}
					
					return blob;
				})
				.then(function (blob) {
					return FileUtil.blobToFile(blob, basename[1] + '_autoscaled');
				});
		},
		
		/**
		 * Downscales an image given as File object.
		 *
		 * @param       {File}        file              the image to resize
		 * @param       {Number}      [maxWidth]        maximum width
		 * @param       {Number}      [maxHeight]       maximum height
		 * @param       {Number}      [quality]         quality in percent
		 * @param       {boolean}     [force]           whether to force scaling even if unneeded
		 * @param       {Promise}     cancelPromise     a Promise used to cancel pica's operation
		 * @returns     {Promise<{exif: any, image: any} | never>} a Promise resolving with the resized image as Canvas and optional EXIF data
		 */
		resize: function (file, maxWidth, maxHeight, quality, force, cancelPromise) {
			maxWidth = maxWidth || this.maxWidth;
			maxHeight = maxHeight || this.maxHeight;
			quality = quality || this.quality;
			force = force || false;

			var exif;
			if (file.type === 'image/jpeg') {
				// Extract EXIF data
				exif = ExifUtil.getExifBytesFromJpeg(file);
			}
			
			var resizer = new Promise(function (resolve, reject) {
				var reader = new FileReader();
				var image = new Image();
				
				reader.addEventListener('load', function () {
					image.src = reader.result;
				});
				
				reader.addEventListener('error', function () {
					reader.abort();
					reject(reader.error);
				});
				
				image.addEventListener('error', reject);
				
				image.addEventListener('load', function () {
					var canvas = document.createElement('canvas');
					
					// Prevent upscaling
					var newWidth = Math.min(maxWidth, image.width);
					var newHeight = Math.min(maxHeight, image.height);
					
					if (image.width <= newWidth && image.height <= newHeight && !force) {
						return resolve(file);
					}
					
					// Keep image ratio
					if (newWidth >= newHeight) {
						canvas.width = newWidth;
						canvas.height = newWidth * (image.height / image.width);
					}
					else {
						canvas.width = newHeight * (image.width / image.height);
						canvas.height = newHeight;
					}
					
					// Map to Pica's quality
					var resizeQuality = 1;
					if (quality >= 0.8) {
						resizeQuality = 3;
					}
					else if (quality >= 0.4) {
						resizeQuality = 2;
					}
					
					var options = {
						quality: resizeQuality,
						cancelToken: cancelPromise
					};
					
					pica.resize(image, canvas, options).then(function (result) {
						resolve(result);
					}, reject);
				});
				
				reader.readAsDataURL(file);
			});
			
			return Promise.all([ exif, resizer ])
				.then(function (result) {
					return { exif: result[0], image: result[1] };
				});
		}
	};
	
	return ImageResizer;
});
