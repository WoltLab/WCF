type RequestFailureType = "api_error" | "invalid_request_error";

export class ApiError {
  constructor(
    public readonly type: RequestFailureType,
    public readonly code: string,
    public readonly message: string,
    public readonly param: string,
    public readonly statusCode: number,
  ) {}

  getValidationError(): ValidationError | undefined {
    if (this.type !== "invalid_request_error" || this.statusCode !== 400) {
      return undefined;
    }

    return new ValidationError(this.code, this.message, this.param);
  }
}

class ValidationError {
  constructor(
    public readonly code: string,
    public readonly message: string,
    public readonly param: string,
  ) {}
}
