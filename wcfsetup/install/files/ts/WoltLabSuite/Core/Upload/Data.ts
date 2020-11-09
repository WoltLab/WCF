export interface UploadOptions {
  // name of the PHP action
  action: string;
  className: string;
  // is true if multiple files can be uploaded at once
  multiple: boolean;
  // array of acceptable file types, null if any file type is acceptable
  acceptableFiles: string[] | null;
  // name of the upload field
  name: string;
  // is true if every file from a multi-file selection is uploaded in its own request
  singleFileRequests: boolean;
  // url for uploading file
  url: string;
}

export type FileElements = HTMLElement[];

export type FileLikeObject = { name: string };

export type FileCollection = File[] | FileLikeObject[] | FileList;

export type UploadId = number | number[] | null;
