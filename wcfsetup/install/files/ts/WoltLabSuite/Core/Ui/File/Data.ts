// This helper interface exists to prevent a circular dependency
// between `./Delete` and `./Upload`

export interface FileUploadHandler {
  checkMaxFiles(): void;
}
