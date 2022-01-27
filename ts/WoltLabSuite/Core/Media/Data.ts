/**
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/Data
 * @woltlabExcludeBundle all
 */

import MediaUpload from "./Upload";
import { FileElements, UploadOptions } from "../Upload/Data";
import MediaEditor from "./Editor";
import MediaManager from "./Manager/Base";
import { RedactorEditor } from "../Ui/Redactor/Editor";
import { I18nValues } from "../Language/Input";

export interface Media {
  altText: I18nValues | string;
  caption: I18nValues | string;
  categoryID: number;
  elementTag: string;
  captionEnableHtml: number;
  filename: string;
  formattedFilesize: string;
  languageID: number | null;
  isImage: number;
  isMultilingual: number;
  link: string;
  mediaID: number;
  smallThumbnailLink: string;
  smallThumbnailType: string;
  tinyThumbnailLink: string;
  tinyThumbnailType: string;
  title: I18nValues | string;
}

export interface MediaManagerOptions {
  dialogTitle: string;
  imagesOnly: boolean;
  minSearchLength: number;
}

export const enum MediaInsertType {
  Separate = "separate",
}

export interface MediaManagerEditorOptions extends MediaManagerOptions {
  buttonClass?: string;
  callbackInsert: (media: Map<number, Media>, insertType: MediaInsertType, thumbnailSize?: string) => void;
  editor?: RedactorEditor;
}

export interface MediaManagerSelectOptions extends MediaManagerOptions {
  buttonClass?: string;
}

export interface MediaEditorCallbackObject {
  _editorClose?: () => void;
  _editorSuccess?: (Media, number?, boolean?) => void;
}

export interface MediaUploadSuccessEventData {
  files: FileElements;
  isMultiFileUpload: boolean;
  media: Media[];
  upload: MediaUpload;
  uploadId: number;
}

export interface MediaUploadOptions extends UploadOptions {
  elementTagSize: number;
  mediaEditor?: MediaEditor;
  mediaManager?: MediaManager;
}

export interface MediaListUploadOptions extends MediaUploadOptions {
  categoryId?: number;
}

export interface MediaUploadAjaxResponseData {
  returnValues: {
    errors: MediaUploadError[];
    media: Media[];
  };
}

export interface MediaUploadError {
  errorType: string;
  filename: string;
}
