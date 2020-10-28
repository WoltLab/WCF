/**
 * Provides helper functions for Exif metadata handling.
 *
 * @author	Tim Duesterhus, Maximilian Mader
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Image/ExifUtil
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setExifData = exports.removeExifData = exports.getExifBytesFromJpeg = void 0;
    var Tag;
    (function (Tag) {
        Tag[Tag["SOI"] = 216] = "SOI";
        Tag[Tag["APP0"] = 224] = "APP0";
        Tag[Tag["APP1"] = 225] = "APP1";
        Tag[Tag["APP2"] = 226] = "APP2";
        Tag[Tag["APP3"] = 227] = "APP3";
        Tag[Tag["APP4"] = 228] = "APP4";
        Tag[Tag["APP5"] = 229] = "APP5";
        Tag[Tag["APP6"] = 230] = "APP6";
        Tag[Tag["APP7"] = 231] = "APP7";
        Tag[Tag["APP8"] = 232] = "APP8";
        Tag[Tag["APP9"] = 233] = "APP9";
        Tag[Tag["APP10"] = 234] = "APP10";
        Tag[Tag["APP11"] = 235] = "APP11";
        Tag[Tag["APP12"] = 236] = "APP12";
        Tag[Tag["APP13"] = 237] = "APP13";
        Tag[Tag["APP14"] = 238] = "APP14";
        Tag[Tag["COM"] = 254] = "COM";
    })(Tag || (Tag = {}));
    // Known sequence signatures
    const _signatureEXIF = "Exif";
    const _signatureXMP = "http://ns.adobe.com/xap/1.0/";
    const _signatureXMPExtension = "http://ns.adobe.com/xmp/extension/";
    function isExifSignature(signature) {
        return signature === _signatureEXIF || signature === _signatureXMP || signature === _signatureXMPExtension;
    }
    function concatUint8Arrays(...arrays) {
        let offset = 0;
        const length = arrays.reduce((sum, array) => sum + array.length, 0);
        const result = new Uint8Array(length);
        arrays.forEach((array) => {
            result.set(array, offset);
            offset += array.length;
        });
        return result;
    }
    async function blobToUint8(blob) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.addEventListener("error", () => {
                reader.abort();
                reject(reader.error);
            });
            reader.addEventListener("load", () => {
                resolve(new Uint8Array(reader.result));
            });
            reader.readAsArrayBuffer(blob);
        });
    }
    /**
     * Extracts the EXIF / XMP sections of a JPEG blob.
     */
    async function getExifBytesFromJpeg(blob) {
        if (!(blob instanceof Blob) && !(blob instanceof File)) {
            throw new TypeError("The argument must be a Blob or a File");
        }
        const bytes = await blobToUint8(blob);
        let exif = new Uint8Array(0);
        if (bytes[0] !== 0xff && bytes[1] !== Tag.SOI) {
            throw new Error("Not a JPEG");
        }
        for (let i = 2; i < bytes.length;) {
            // each sequence starts with 0xFF
            if (bytes[i] !== 0xff)
                break;
            const length = 2 + ((bytes[i + 2] << 8) | bytes[i + 3]);
            // Check if the next byte indicates an EXIF sequence
            if (bytes[i + 1] === Tag.APP1) {
                let signature = "";
                for (let j = i + 4; bytes[j] !== 0 && j < bytes.length; j++) {
                    signature += String.fromCharCode(bytes[j]);
                }
                // Only copy Exif and XMP data
                if (isExifSignature(signature)) {
                    // append the found EXIF sequence, usually only a single EXIF (APP1) sequence should be defined
                    const sequence = bytes.slice(i, length + i);
                    exif = concatUint8Arrays(exif, sequence);
                }
            }
            i += length;
        }
        return exif;
    }
    exports.getExifBytesFromJpeg = getExifBytesFromJpeg;
    /**
     * Removes all EXIF and XMP sections of a JPEG blob.
     */
    async function removeExifData(blob) {
        if (!(blob instanceof Blob) && !(blob instanceof File)) {
            throw new TypeError("The argument must be a Blob or a File");
        }
        const bytes = await blobToUint8(blob);
        if (bytes[0] !== 0xff && bytes[1] !== Tag.SOI) {
            throw new Error("Not a JPEG");
        }
        let result = bytes;
        for (let i = 2; i < result.length;) {
            // each sequence starts with 0xFF
            if (result[i] !== 0xff)
                break;
            const length = 2 + ((result[i + 2] << 8) | result[i + 3]);
            // Check if the next byte indicates an EXIF sequence
            if (result[i + 1] === Tag.APP1) {
                let signature = "";
                for (let j = i + 4; result[j] !== 0 && j < result.length; j++) {
                    signature += String.fromCharCode(result[j]);
                }
                // Only remove known signatures
                if (isExifSignature(signature)) {
                    const start = result.slice(0, i);
                    const end = result.slice(i + length);
                    result = concatUint8Arrays(start, end);
                }
                else {
                    i += length;
                }
            }
            else {
                i += length;
            }
        }
        return new Blob([result], { type: blob.type });
    }
    exports.removeExifData = removeExifData;
    /**
     * Overrides the APP1 (EXIF / XMP) sections of a JPEG blob with the given data.
     */
    async function setExifData(blob, exif) {
        blob = await removeExifData(blob);
        const bytes = await blobToUint8(blob);
        let offset = 2;
        // check if the second tag is the JFIF tag
        if (bytes[2] === 0xff && bytes[3] === Tag.APP0) {
            offset += 2 + ((bytes[4] << 8) | bytes[5]);
        }
        const start = bytes.slice(0, offset);
        const end = bytes.slice(offset);
        const result = concatUint8Arrays(start, exif, end);
        return new Blob([result], { type: blob.type });
    }
    exports.setExifData = setExifData;
});
