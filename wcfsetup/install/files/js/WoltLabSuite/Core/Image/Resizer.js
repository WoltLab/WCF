/**
 * This module allows resizing and conversion of HTMLImageElements to Blob and File objects
 *
 * @author	Tim Duesterhus, Maximilian Mader
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Image/Resizer
 */
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
define(["require", "exports", "../FileUtil", "./ExifUtil", "pica"], function (require, exports, FileUtil, ExifUtil, pica_1) {
    "use strict";
    FileUtil = __importStar(FileUtil);
    ExifUtil = __importStar(ExifUtil);
    pica_1 = __importDefault(pica_1);
    const pica = new pica_1.default({ features: ["js", "wasm", "ww"] });
    const DEFAULT_WIDTH = 800;
    const DEFAULT_HEIGHT = 600;
    const DEFAULT_QUALITY = 0.8;
    const DEFAULT_FILETYPE = "image/jpeg";
    class ImageResizer {
        constructor() {
            this.maxWidth = DEFAULT_WIDTH;
            this.maxHeight = DEFAULT_HEIGHT;
            this.quality = DEFAULT_QUALITY;
            this.fileType = DEFAULT_FILETYPE;
        }
        /**
         * Sets the default maximum width for this instance
         */
        setMaxWidth(value) {
            if (value == null)
                value = DEFAULT_WIDTH;
            this.maxWidth = value;
            return this;
        }
        /**
         * Sets the default maximum height for this instance
         */
        setMaxHeight(value) {
            if (value == null)
                value = DEFAULT_HEIGHT;
            this.maxHeight = value;
            return this;
        }
        /**
         * Sets the default quality for this instance
         */
        setQuality(value) {
            if (value == null)
                value = DEFAULT_QUALITY;
            this.quality = value;
            return this;
        }
        /**
         * Sets the default file type for this instance
         */
        setFileType(value) {
            if (value == null)
                value = DEFAULT_FILETYPE;
            this.fileType = value;
            return this;
        }
        /**
         * Converts the given object of exif data and image data into a File.
         */
        async saveFile(data, fileName, fileType = this.fileType, quality = this.quality) {
            const basename = fileName.match(/(.+)(\..+?)$/);
            let blob = await pica.toBlob(data.image, fileType, quality);
            if (fileType === "image/jpeg" && typeof data.exif !== "undefined") {
                blob = await ExifUtil.setExifData(blob, data.exif);
            }
            return FileUtil.blobToFile(blob, basename[1]);
        }
        /**
         * Loads the given file into an image object and parses Exif information.
         */
        async loadFile(file) {
            let exifBytes = Promise.resolve(undefined);
            let fileData = file;
            if (file.type === "image/jpeg") {
                // Extract EXIF data
                exifBytes = ExifUtil.getExifBytesFromJpeg(file);
                // Strip EXIF data
                fileData = await ExifUtil.removeExifData(fileData);
            }
            const imageLoader = new Promise(function (resolve, reject) {
                const reader = new FileReader();
                const image = new Image();
                reader.addEventListener("load", function () {
                    image.src = reader.result;
                });
                reader.addEventListener("error", function () {
                    reader.abort();
                    reject(reader.error);
                });
                image.addEventListener("error", reject);
                image.addEventListener("load", function () {
                    resolve(image);
                });
                reader.readAsDataURL(fileData);
            });
            const [exif, image] = await Promise.all([exifBytes, imageLoader]);
            return { exif, image };
        }
        /**
         * Downscales an image given as File object.
         */
        async resize(image, maxWidth = this.maxWidth, maxHeight = this.maxHeight, quality = this.quality, force = false, cancelPromise) {
            const canvas = document.createElement("canvas");
            if (window.createImageBitmap) {
                const bitmap = await createImageBitmap(image);
                if (bitmap.height != image.height)
                    throw new Error("Chrome Bug #1069965");
            }
            // Prevent upscaling
            const newWidth = Math.min(maxWidth, image.width);
            const newHeight = Math.min(maxHeight, image.height);
            if (image.width <= newWidth && image.height <= newHeight && !force) {
                return undefined;
            }
            // Keep image ratio
            const ratio = Math.min(newWidth / image.width, newHeight / image.height);
            canvas.width = Math.floor(image.width * ratio);
            canvas.height = Math.floor(image.height * ratio);
            // Map to Pica's quality
            let resizeQuality = 1;
            if (quality >= 0.8) {
                resizeQuality = 3;
            }
            else if (quality >= 0.4) {
                resizeQuality = 2;
            }
            const options = {
                quality: resizeQuality,
                cancelToken: cancelPromise,
                alpha: true,
            };
            return pica.resize(image, canvas, options);
        }
    }
    return ImageResizer;
});
