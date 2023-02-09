/**
 * Provides suggestions for map locations.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { CallbackSelect, SearchInputOptions } from "../../../Ui/Search/Data";
import UiSearchInput from "../../../Ui/Search/Input";

class Suggestion extends UiSearchInput {
  readonly #geocoder: google.maps.Geocoder;

  constructor(element: HTMLInputElement, options: SearchInputOptions, geocoder: google.maps.Geocoder) {
    super(element, options);
    this.#geocoder = geocoder;
  }

  protected search(value: string): void {
    void this.#geocoder.geocode({ address: value }, (results, status) => {
      if (status === google.maps.GeocoderStatus.OK) {
        const data = {
          actionName: "",
          objectIDs: [],
          returnValues: {},
        };

        results!.forEach((value) => {
          data.returnValues[value.formatted_address] = {
            label: value.formatted_address,
            objectID: value.formatted_address,
          };
        });

        this._ajaxSuccess(data);
      }
    });
  }
}

export function setup(
  element: HTMLInputElement,
  geocoder: google.maps.Geocoder,
  callbackSelect?: CallbackSelect,
): void {
  new Suggestion(
    element,
    {
      callbackSelect,
    },
    geocoder,
  );
}
