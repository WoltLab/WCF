/**
 * Data handler for a Google Maps form builder field in an Ajax form.
 *
 * @author    Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 */
import { FormBuilderData } from "../Data";
import Value from "./Value";
import type WoltlabCoreGoogleMapsElement from "WoltLabSuite/Core/Component/GoogleMaps/woltlab-core-google-maps";

class GoogleMaps extends Value {
  protected _getData(): FormBuilderData {
    const map = document.getElementById(this._fieldId + "_map") as WoltlabCoreGoogleMapsElement;

    return {
      [this._fieldId]: (this._field as HTMLInputElement).value,
      [this._fieldId + "_coordinates"]: `${map.lat},${map.lng}`,
    };
  }
}

export = GoogleMaps;
