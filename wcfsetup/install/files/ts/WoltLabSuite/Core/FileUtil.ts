/**
 * Provides helper functions for file handling.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/FileUtil
 */

import * as StringUtil from './StringUtil';

const _fileExtensionIconMapping = new Map<string, string>(Object.entries({
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
}));

const _mimeTypeExtensionMapping = new Map<string, string>(Object.entries({
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
}));

/**
 * Formats the given filesize.
 */
export function formatFilesize(byte: number, precision: number): string {
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

/**
 * Returns the icon name for given filename.
 *
 * Note: For any file icon name like `fa-file-word`, only `word`
 * will be returned by this method.
 */
export function getIconNameByFilename(filename: string): string {
  const lastDotPosition = filename.lastIndexOf('.');
  if (lastDotPosition !== -1) {
    const extension = filename.substr(lastDotPosition + 1);

    if (_fileExtensionIconMapping.has(extension)) {
      return _fileExtensionIconMapping.get(extension) as string;
    }
  }

  return '';
}

/**
 * Returns a known file extension including a leading dot or an empty string.
 */
export function getExtensionByMimeType(mimetype: string): string {
  if (_mimeTypeExtensionMapping.has(mimetype)) {
    return '.' + _mimeTypeExtensionMapping.get(mimetype);
  }

  return '';
}


/**
 * Constructs a File object from a Blob
 *
 * @param       blob            the blob to convert
 * @param       filename        the filename
 * @returns     {File}          the File object
 */
export function blobToFile(blob: Blob, filename: string): File {
  const ext = getExtensionByMimeType(blob.type);

  return new File([blob], filename + ext, {type: blob.type});
}
