/**
 * Provides suggestions for map locations.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Ui/Search/Input"], function (require, exports, tslib_1, Input_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Input_1 = tslib_1.__importDefault(Input_1);
    class Suggestion extends Input_1.default {
        #geocoder;
        constructor(element, options, geocoder) {
            super(element, options);
            this.#geocoder = geocoder;
        }
        search(value) {
            void this.#geocoder.geocode({ address: value }, (results, status) => {
                if (status === google.maps.GeocoderStatus.OK) {
                    const data = {
                        actionName: "",
                        objectIDs: [],
                        returnValues: {},
                    };
                    results.forEach((value) => {
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
    function setup(element, geocoder, callbackSelect) {
        new Suggestion(element, {
            callbackSelect,
        }, geocoder);
    }
});
