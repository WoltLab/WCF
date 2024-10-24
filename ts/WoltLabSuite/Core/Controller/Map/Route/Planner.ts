/**
 * Map route planner based on Google Maps.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 * @deprecated 6.0 This feature is discontinued, opening Google Maps in a separate window already offers a route planer.
 */

import * as AjaxStatus from "../../../Ajax/Status";
import DomUtil from "../../../Dom/Util";
import * as Language from "../../../Language";
import UiDialog from "../../../Ui/Dialog";
import { DialogCallbackObject, DialogCallbackSetup } from "../../../Ui/Dialog/Data";

interface LocationData {
  label?: string;
  location: google.maps.LatLng;
}

class ControllerMapRoutePlanner implements DialogCallbackObject {
  private readonly button: HTMLElement;
  private readonly destination: google.maps.LatLng;
  private didInitDialog = false;
  private directionsRenderer?: google.maps.DirectionsRenderer = undefined;
  private directionsService?: google.maps.DirectionsService = undefined;
  private googleLink?: HTMLAnchorElement = undefined;
  private lastOrigin?: google.maps.LatLng = undefined;
  private map?: google.maps.Map = undefined;
  private originInput?: HTMLInputElement = undefined;
  private travelMode?: HTMLSelectElement = undefined;

  constructor(buttonId: string, destination: google.maps.LatLng) {
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
  _calculateRoute(data: LocationData): void {
    const dialog = UiDialog.getDialog(this)!.dialog;

    if (data.label) {
      this.originInput!.value = data.label;
    }

    if (this.map === undefined) {
      const mapContainer = dialog.querySelector(".googleMap") as HTMLElement;
      this.map = new google.maps.Map(mapContainer, {
        disableDoubleClickZoom: window.WCF.Location.GoogleMaps.Settings.get("disableDoubleClickZoom"),
        draggable: window.WCF.Location.GoogleMaps.Settings.get("draggable"),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        scaleControl: window.WCF.Location.GoogleMaps.Settings.get("scaleControl"),
        scrollwheel: window.WCF.Location.GoogleMaps.Settings.get("scrollwheel"),
        // see https://developers.google.com/maps/documentation/javascript/advanced-markers/migration
        mapId: "DEMO_MAP_ID",
      });

      this.directionsService = new google.maps.DirectionsService();
      this.directionsRenderer = new google.maps.DirectionsRenderer();

      this.directionsRenderer.setMap(this.map);
      const directionsContainer = dialog.querySelector(".googleMapsDirections") as HTMLElement;
      this.directionsRenderer.setPanel(directionsContainer);

      this.googleLink = dialog.querySelector(".googleMapsDirectionsGoogleLink") as HTMLAnchorElement;
    }

    const request = {
      destination: this.destination,
      origin: data.location,
      provideRouteAlternatives: true,
      travelMode: google.maps.TravelMode[this.travelMode!.value.toUpperCase()],
    };

    AjaxStatus.show();
    // .route() returns a promise, but we rely on the callback API for compatibility reasons.
    void this.directionsService!.route(request, (result, status) => this.setRoute(result, status));

    this.googleLink!.href = this.getGoogleMapsLink(data.location, this.travelMode!.value);

    this.lastOrigin = data.location;
  }

  /**
   * Returns the Google Maps link based on the given optional directions origin
   * and optional travel mode.
   */
  private getGoogleMapsLink(origin?: google.maps.LatLng, travelMode?: string): string {
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
  private initDialog(): void {
    if (!this.didInitDialog) {
      const dialog = UiDialog.getDialog(this)!.dialog;

      // make input element a location search
      this.originInput = dialog.querySelector('input[name="origin"]') as HTMLInputElement;
      new window.WCF.Location.GoogleMaps.LocationSearch(this.originInput, (data) => this._calculateRoute(data));

      this.travelMode = dialog.querySelector('select[name="travelMode"]') as HTMLSelectElement;
      this.travelMode.addEventListener("change", this.updateRoute.bind(this));

      this.didInitDialog = true;
    }
  }

  /**
   * Opens the route planning dialog.
   */
  private openDialog(event: Event): void {
    event.preventDefault();

    UiDialog.open(this);
  }

  /**
   * Handles the response of the direction service.
   */
  private setRoute(result: google.maps.DirectionsResult | null, status: google.maps.DirectionsStatus): void {
    AjaxStatus.hide();

    if (status === google.maps.DirectionsStatus.OK) {
      DomUtil.show(this.map!.getDiv().parentElement!);

      google.maps.event.trigger(this.map!, "resize");

      this.directionsRenderer!.setDirections(result);

      DomUtil.show(this.travelMode!.closest("dl")!);
      DomUtil.show(this.googleLink!);

      DomUtil.innerError(this.originInput!, false);
    } else {
      // map irrelevant errors to not found error
      if (
        status !== google.maps.DirectionsStatus.OVER_QUERY_LIMIT &&
        status !== google.maps.DirectionsStatus.REQUEST_DENIED
      ) {
        status = google.maps.DirectionsStatus.NOT_FOUND;
      }

      DomUtil.innerError(this.originInput!, Language.get(`wcf.map.route.error.${status.toLowerCase()}`));
    }
  }

  /**
   * Updates the route after the travel mode has been changed.
   */
  private updateRoute(): void {
    this._calculateRoute({
      location: this.lastOrigin!,
    });
  }

  /**
   * Sets up the route planner dialog.
   */
  _dialogSetup(): ReturnType<DialogCallbackSetup> {
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
  <a href="${this.getGoogleMapsLink()}" class="googleMapsDirectionsGoogleLink" target="_blank" style="display: none;">${Language.get(
    "wcf.map.route.viewOnGoogleMaps",
  )}</a>
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

export = ControllerMapRoutePlanner;
