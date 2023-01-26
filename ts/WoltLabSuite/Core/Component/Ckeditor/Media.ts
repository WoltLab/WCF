import * as EventHandler from "../../Event/Handler";

type UploadResult = {
  [key: string]: unknown;
  urls: {
    default: string;
  };
};

type MediaData = {
  mediaId: number;
  mediaSize: string;
  url: string;
};

export type MediaDragAndDropEventData = {
  abortController?: AbortController;
  file: File;
  promise?: Promise<MediaData>;
};

export function uploadMedia(elementId: string, file: File, abortController?: AbortController): Promise<UploadResult> {
  const data: MediaDragAndDropEventData = { abortController, file };
  EventHandler.fire("com.woltlab.wcf.ckeditor5", `dragAndDrop_${elementId}`, data);

  // The media system works differently compared to the
  // attachments, because uploading a file will offer
  // the user to insert the content in different formats.
  //
  // Rejecting the upload promise will cause CKEditor to
  // stop caring about the file so that we regain control.
  return Promise.reject();
}