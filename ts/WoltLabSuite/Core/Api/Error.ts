/**
 * Represents an error from a failed request to an API endpoint.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

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
