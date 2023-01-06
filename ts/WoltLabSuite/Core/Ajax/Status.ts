/**
 * Provides the AJAX status overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ajax/Status
 */

class AjaxStatus {
  private _activeRequests = 0;
  private readonly _overlay: Element;
  private _timer: number | null = null;

  constructor() {
    this._overlay = document.createElement("div");
    this._overlay.classList.add("spinner");
    this._overlay.setAttribute("role", "status");

    const loadingIndicator = document.createElement("woltlab-core-loading-indicator");
    loadingIndicator.size = 48;
    this._overlay.append(loadingIndicator);

    document.body.append(this._overlay);
  }

  show(): void {
    this._activeRequests++;

    if (this._timer === null) {
      this._timer = window.setTimeout(() => {
        if (this._activeRequests) {
          this._overlay.classList.add("active");
        }

        this._timer = null;
      }, 250);
    }
  }

  hide(): void {
    if (--this._activeRequests === 0) {
      if (this._timer !== null) {
        window.clearTimeout(this._timer);
        this._timer = null;
      }

      this._overlay.classList.remove("active");
    }
  }
}

let status: AjaxStatus;
function getStatus(): AjaxStatus {
  if (status === undefined) {
    status = new AjaxStatus();
  }

  return status;
}

/**
 * Shows the loading overlay.
 */
export function show(): void {
  getStatus().show();
}

/**
 * Hides the loading overlay.
 */
export function hide(): void {
  getStatus().hide();
}
