/**
 * Binds to button-like elements with the attribute [data-formbuilder] and invokes
 * the endpoint to request the form builder dialog.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { dialogFactory } from "../Dialog";
import { wheneverSeen } from "../../Helper/Selector";

const reponseIdentifier = "__Psr15DialogFormResponse";

type Psr15DialogFormResponse = {
  payload:
    | {
        reload: true;
      }
    | {
        redirectUrl: string;
      };
  __Psr15DialogFormResponse: true;
};

async function requestForm(element: HTMLElement): Promise<void> {
  const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint(element.dataset.endpoint!);
  if (!ok) {
    return;
  }

  const event = new CustomEvent<unknown>("formBuilder:result", {
    cancelable: true,
    detail: {
      result,
    },
  });
  element.dispatchEvent(event);

  if (event.defaultPrevented) {
    return;
  }

  if (typeof result === "object" && result !== null && Object.hasOwn(result, reponseIdentifier)) {
    const payload = (result as Psr15DialogFormResponse).payload;
    if ("reload" in payload) {
      window.location.reload();
    } else {
      window.location.href = payload.redirectUrl;
    }

    return;
  }
}

export function setup(): void {
  wheneverSeen("[data-formbuilder]", (element) => {
    if (element.tagName !== "A" && element.tagName !== "BUTTON") {
      throw new TypeError("Cannot initialize the FormBuilder on non button-like elements", {
        cause: {
          element,
        },
      });
    }

    if (!element.dataset.endpoint) {
      throw new Error("Missing the [data-endpoint] attribute.", {
        cause: {
          element,
        },
      });
    }

    element.addEventListener("click", (event) => {
      event.preventDefault();

      void requestForm(element);
    });
  });
}
