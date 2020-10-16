export interface RequestPayload {
  [key: string]: any;
}

export type RequestData = FormData | RequestPayload;

export interface ResponseData {
  [key: string]: any;
}

export type CallbackFailure = (data: ResponseData, responseText: string, xhr: XMLHttpRequest, requestData: RequestData) => boolean;
export type CallbackFinalize = (xhr: XMLHttpRequest) => void;
export type CallbackProgress = (event: ProgressEvent) => void;
export type CallbackSuccess = (data: ResponseData, responseText: string, xhr: XMLHttpRequest, requestData: RequestData) => void;
export type CallbackUploadProgress = (event: ProgressEvent) => void;
export type CallbackSetup = () => RequestOptions;

export interface CallbackObject {
  _ajaxFailure?: CallbackFailure;
  _ajaxFinalize?: CallbackFinalize;
  _ajaxProgress?: CallbackProgress;
  _ajaxSuccess: CallbackSuccess;
  _ajaxUploadProgress?: CallbackUploadProgress;
  _ajaxSetup: CallbackSetup;
}

export interface RequestOptions {
  // request data
  data?: RequestData,
  contentType?: string,
  responseType?: string,
  type?: string,
  url?: string,
  withCredentials?: boolean,

  // behavior
  autoAbort?: boolean,
  ignoreError?: boolean,
  pinData?: boolean,
  silent?: boolean,
  includeRequestedWith?: boolean,

  // callbacks
  failure?: CallbackFailure,
  finalize?: CallbackFinalize,
  success?: CallbackSuccess,
  progress?: CallbackProgress,
  uploadProgress?: CallbackUploadProgress,

  callbackObject?: CallbackObject | null,
}
