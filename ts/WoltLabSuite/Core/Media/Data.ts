/**
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 * @deprecated 6.3 Use `WoltLabSuite/Core/Api/Media/GetMedia` and `WoltLabSuite/Core/Component/Media/Manager` instead.
 */

import type { CKEditor } from "../Component/Ckeditor";

export interface Media {
  altText: string;
  categoryID: number | null;
  elementTag: string;
  filename: string;
  formattedFilesize: string;
  isImage: number;
  link: string;
  mediaID: number;
  smallThumbnailLink: string;
  tinyThumbnailLink: string;
  title: string;
}

export interface MediaManagerOptions {
  dialogTitle: string;
  imagesOnly: boolean;
}

export const enum MediaInsertType {
  Separate = "separate",
}

export interface MediaManagerEditorOptions extends MediaManagerOptions {
  buttonClass?: string;
  callbackInsert: (media: Map<number, Media>, insertType: MediaInsertType, thumbnailSize?: string) => void;
  ckeditor?: CKEditor;
}

export interface MediaManagerSelectOptions extends MediaManagerOptions {
  buttonClass?: string;
}
