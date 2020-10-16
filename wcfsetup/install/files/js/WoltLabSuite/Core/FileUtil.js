/**
 * Provides helper functions for file handling.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/FileUtil
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
define(["require", "exports", "./Dictionary", "./StringUtil"], function (require, exports, Dictionary_1, StringUtil) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.blobToFile = exports.getExtensionByMimeType = exports.getIconNameByFilename = exports.formatFilesize = void 0;
    Dictionary_1 = __importDefault(Dictionary_1);
    StringUtil = __importStar(StringUtil);
    const _fileExtensionIconMapping = Dictionary_1.default.fromObject({
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
        webp: 'image',
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
        odt: 'word',
    });
    const _mimeTypeExtensionMapping = Dictionary_1.default.fromObject({
        // archive
        'application/zip': 'zip',
        'application/x-zip-compressed': 'zip',
        'application/rar': 'rar',
        'application/vnd.rar': 'rar',
        'application/x-rar-compressed': 'rar',
        'application/x-tar': 'tar',
        'application/x-gzip': 'gz',
        'application/gzip': 'gz',
        // audio
        'audio/mpeg': 'mp3',
        'audio/mp3': 'mp3',
        'audio/ogg': 'ogg',
        'audio/x-wav': 'wav',
        // code
        'application/x-php': 'php',
        'text/html': 'html',
        'application/javascript': 'js',
        // excel
        'application/vnd.ms-excel': 'xls',
        'application/vnd.oasis.opendocument.spreadsheet': 'ods',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'xlsx',
        // image
        'image/gif': 'gif',
        'image/jpeg': 'jpg',
        'image/png': 'png',
        'image/x-ms-bmp': 'bmp',
        'image/bmp': 'bmp',
        'image/webp': 'webp',
        // video
        'video/x-msvideo': 'avi',
        'video/x-ms-wmv': 'wmv',
        'video/quicktime': 'mov',
        'video/mp4': 'mp4',
        'video/mpeg': 'mpg',
        'video/x-flv': 'flv',
        // pdf
        'application/pdf': 'pdf',
        // powerpoint
        'application/vnd.ms-powerpoint': 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'pptx',
        // text
        'text/plain': 'txt',
        // word
        'application/msword': 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx',
        'application/vnd.oasis.opendocument.text': 'odt',
    });
    /**
     * Formats the given filesize.
     */
    function formatFilesize(byte, precision) {
        if (precision === undefined) {
            precision = 2;
        }
        let symbol = 'Byte';
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
    }
    exports.formatFilesize = formatFilesize;
    /**
     * Returns the icon name for given filename.
     *
     * Note: For any file icon name like `fa-file-word`, only `word`
     * will be returned by this method.
     */
    function getIconNameByFilename(filename) {
        const lastDotPosition = filename.lastIndexOf('.');
        if (lastDotPosition !== -1) {
            const extension = filename.substr(lastDotPosition + 1);
            if (_fileExtensionIconMapping.has(extension)) {
                return _fileExtensionIconMapping.get(extension);
            }
        }
        return '';
    }
    exports.getIconNameByFilename = getIconNameByFilename;
    /**
     * Returns a known file extension including a leading dot or an empty string.
     */
    function getExtensionByMimeType(mimetype) {
        if (_mimeTypeExtensionMapping.has(mimetype)) {
            return '.' + _mimeTypeExtensionMapping.get(mimetype);
        }
        return '';
    }
    exports.getExtensionByMimeType = getExtensionByMimeType;
    /**
     * Constructs a File object from a Blob
     *
     * @param       blob            the blob to convert
     * @param       filename        the filename
     * @returns     {File}          the File object
     */
    function blobToFile(blob, filename) {
        const ext = getExtensionByMimeType(blob.type);
        return new File([blob], filename + ext, { type: blob.type });
    }
    exports.blobToFile = blobToFile;
});
