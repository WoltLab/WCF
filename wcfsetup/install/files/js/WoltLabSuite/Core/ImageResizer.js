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
	var pica = Pica({ features: [ 'js', 'wasm', 'ww' ] });
	
	/**
	 * @constructor
	 */
	function ImageResizer() { }
	
	ImageResizer.prototype = {
		maxWidth: 800,
		maxHeight: 600,
		quality: 0.8,
		fileType: 'image/jpeg',
		
		setMaxWidth: function (value) {
			if (value == null) value = ImageResizer.prototype.maxWidth;
			
			this.maxWidth = value;
			return this;
		},
		
		setMaxHeight: function (value) {
			if (value == null) value = ImageResizer.prototype.maxHeight;
			
			this.maxHeight = value;
			return this;
		},
		
		setQuality: function (value) {
			if (value == null) value = ImageResizer.prototype.quality;
			
			this.quality = value;
			return this;
		},
		
		setFileType: function (value) {
			if (value == null) value = ImageResizer.prototype.fileType;
			
			this.fileType = value;
			return this;
		},
		
		getFile: function (result, fileName, fileType, quality) {
			var image = result.image;
			var exif = result.exif;
			
			return pica.toBlob(image, fileType, quality)
				.then(function (blob) {
					if (fileType === 'image/jpeg' && typeof exif !== 'undefined') {
						blob = ExifUtil.setExifData(blob, exif);
					}
					
					var matches = fileName.match(/(.+)(\..+?)$/);
					
					// If we inserted EXIF data blob has been converted to a Promise
					return Promise.resolve(blob)
						.then(function (blob) {
							return FileUtil.blobToFile(blob, matches[1] + '_autoscaled')
						});
				});
		},
		
		resize: function (file, maxWidth, maxHeight, quality, force, cancelPromise) {
			maxWidth = maxWidth || this.maxWidth;
			maxHeight = maxHeight || this.maxHeight;
			quality = quality || this.quality;
			force = force || false;
			
			var reader = new FileReader();
			var image = new Image();
			var exif = Promise.resolve();
			
			reader.onloadend = function () {
				image.src = reader.result;
			};
			
			if (file.type === 'image/jpeg') {
				// Extract EXIF data
				exif = ExifUtil.getExifBytesFromJpeg(file);
			}
			
			reader.readAsDataURL(file);
			
			return exif.then(function (exif) {
				return new Promise(function (resolve, reject) {
					image.onerror = reject;
					
					image.onload = function () {
						var canvas = document.createElement('canvas');
						
						// Prevent upscalingq
						var newWidth = Math.min(maxWidth, image.width);
						var newHeight = Math.min(maxHeight, image.height);
						
						if (image.width < newWidth && image.height < newHeight && !force) {
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
						
						resolve(pica.resize(image, canvas, options).then(function (result) {
							return { image: result, exif: exif };
						}));
					}
				})
			});
		}
	};
	
	return ImageResizer;
});
