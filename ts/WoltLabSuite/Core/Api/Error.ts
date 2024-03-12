type RequestFailureType = "api_error" | "invalid_request_error";

export class ApiError {
  constructor(
    public readonly type: RequestFailureType,
    public readonly code: string,
    public readonly message: string,
    public readonly param: string,
  ) {}
}
