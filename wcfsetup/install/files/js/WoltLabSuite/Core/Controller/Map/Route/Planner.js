/**
 * Map route planner based on Google Maps.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 * @deprecated 6.0 This feature is discontinued, opening Google Maps in a separate window already offers a route planer.
 */
define(["require", "exports", "tslib", "../../../Ajax/Status", "../../../Dom/Util", "../../../Language", "../../../Ui/Dialog"], function (require, exports, tslib_1, AjaxStatus, Util_1, Language, Dialog_1) {
    "use strict";
    AjaxStatus = tslib_1.__importStar(AjaxStatus);
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class ControllerMapRoutePlanner {
        button;
        destination;
        didInitDialog = false;
        directionsRenderer = undefined;
        directionsService = undefined;
        googleLink = undefined;
        lastOrigin = undefined;
        map = undefined;
        originInput = undefined;
        travelMode = undefined;
        constructor(buttonId, destination) {
            const button = document.getElementById(buttonId);
            if (button === null) {
                throw new Error(`Unknown button with id '${buttonId}'`);
            }
            this.button = button;
            this.button.addEventListener("click", (ev) => this.openDialog(ev));
            this.destination = destination;
        }
        /**
         * Calculates the route based on the given result of a location search.
         */
        _calculateRoute(data) {
            const dialog = Dialog_1.default.getDialog(this).dialog;
            if (data.label) {
                this.originInput.value = data.label;
            }
            if (this.map === undefined) {
                const mapContainer = dialog.querySelector(".googleMap");
                this.map = new google.maps.Map(mapContainer, {
                    disableDoubleClickZoom: window.WCF.Location.GoogleMaps.Settings.get("disableDoubleClickZoom"),
                    gestureHandling: window.WCF.Location.GoogleMaps.Settings.get("draggable") ? "auto" : "none",
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    scaleControl: window.WCF.Location.GoogleMaps.Settings.get("scaleControl"),
                    scrollwheel: window.WCF.Location.GoogleMaps.Settings.get("scrollwheel"),
                    // see https://developers.google.com/maps/documentation/javascript/advanced-markers/migration
                    mapId: "DEMO_MAP_ID",
                });
                this.directionsService = new google.maps.DirectionsService();
                this.directionsRenderer = new google.maps.DirectionsRenderer();
                this.directionsRenderer.setMap(this.map);
                const directionsContainer = dialog.querySelector(".googleMapsDirections");
                this.directionsRenderer.setPanel(directionsContainer);
                this.googleLink = dialog.querySelector(".googleMapsDirectionsGoogleLink");
            }
            const request = {
                destination: this.destination,
                origin: data.location,
                provideRouteAlternatives: true,
                travelMode: google.maps.TravelMode[this.travelMode.value.toUpperCase()],
            };
            AjaxStatus.show();
            // .route() returns a promise, but we rely on the callback API for compatibility reasons.
            void this.directionsService.route(request, (result, status) => this.setRoute(result, status));
            this.googleLink.href = this.getGoogleMapsLink(data.location, this.travelMode.value);
            this.lastOrigin = data.location;
        }
        /**
         * Returns the Google Maps link based on the given optional directions origin
         * and optional travel mode.
         */
        getGoogleMapsLink(origin, travelMode) {
            if (origin) {
                let link = `https://www.google.com/maps/dir/?api=1&origin=${origin.lat()},${origin.lng()}&destination=${this.destination.lat()},${this.destination.lng()}`;
                if (travelMode) {
                    link += `&travelmode=${travelMode}`;
                }
                return link;
            }
            return `https://www.google.com/maps/search/?api=1&query=${this.destination.lat()},${this.destination.lng()}`;
        }
        /**
         * Initializes the route planning dialog.
         */
        initDialog() {
            if (!this.didInitDialog) {
                const dialog = Dialog_1.default.getDialog(this).dialog;
                // make input element a location search
                this.originInput = dialog.querySelector('input[name="origin"]');
                new window.WCF.Location.GoogleMaps.LocationSearch(this.originInput, (data) => this._calculateRoute(data));
                this.travelMode = dialog.querySelector('select[name="travelMode"]');
                this.travelMode.addEventListener("change", this.updateRoute.bind(this));
                this.didInitDialog = true;
            }
        }
        /**
         * Opens the route planning dialog.
         */
        openDialog(event) {
            event.preventDefault();
            Dialog_1.default.open(this);
        }
        /**
         * Handles the response of the direction service.
         */
        setRoute(result, status) {
            AjaxStatus.hide();
            if (status === google.maps.DirectionsStatus.OK) {
                Util_1.default.show(this.map.getDiv().parentElement);
                google.maps.event.trigger(this.map, "resize");
                this.directionsRenderer.setDirections(result);
                Util_1.default.show(this.travelMode.closest("dl"));
                Util_1.default.show(this.googleLink);
                Util_1.default.innerError(this.originInput, false);
            }
            else {
                // map irrelevant errors to not found error
                if (status !== google.maps.DirectionsStatus.OVER_QUERY_LIMIT &&
                    status !== google.maps.DirectionsStatus.REQUEST_DENIED) {
                    status = google.maps.DirectionsStatus.NOT_FOUND;
                }
                Util_1.default.innerError(this.originInput, Language.get(`wcf.map.route.error.${status.toLowerCase()}`));
            }
        }
        /**
         * Updates the route after the travel mode has been changed.
         */
        updateRoute() {
            this._calculateRoute({
                location: this.lastOrigin,
            });
        }
        /**
         * Sets up the route planner dialog.
         */
        _dialogSetup() {
            return {
                id: this.button.id + "Dialog",
                options: {
                    onShow: this.initDialog.bind(this),
                    title: Language.get("wcf.map.route.planner"),
                },
                source: `
<div class="googleMapsDirectionsContainer" style="display: none;">
  <div class="googleMap"></div>
  <div class="googleMapsDirections"></div>
</div>
<small class="googleMapsDirectionsGoogleLinkContainer">
  <a href="${this.getGoogleMapsLink()}" class="googleMapsDirectionsGoogleLink" target="_blank" style="display: none;">${Language.get("wcf.map.route.viewOnGoogleMaps")}</a>
</small>
<dl>
  <dt>${Language.get("wcf.map.route.origin")}</dt>
  <dd>
    <input type="text" name="origin" class="long" autofocus>
  </dd>
</dl>
<dl style="display: none;">
  <dt>${Language.get("wcf.map.route.travelMode")}</dt>
  <dd>
    <select name="travelMode">
      <option value="driving">${Language.get("wcf.map.route.travelMode.driving")}</option>
      <option value="walking">${Language.get("wcf.map.route.travelMode.walking")}</option>
      <option value="bicycling">${Language.get("wcf.map.route.travelMode.bicycling")}</option>
      <option value="transit">${Language.get("wcf.map.route.travelMode.transit")}</option>
    </select>
  </dd>
</dl>`,
            };
        }
    }
    return ControllerMapRoutePlanner;
});
