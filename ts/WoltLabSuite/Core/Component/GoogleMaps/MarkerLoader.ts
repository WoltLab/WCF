/**
 * Handles a large map with many markers where (new) markers are loaded via AJAX.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { dboAction } from "../../Ajax";
import WoltlabCoreGoogleMapsElement from "./woltlab-core-google-maps";
import { dialogFactory } from "../Dialog";
import DomUtil from "../../Dom/Util";
import WoltlabCoreDialogElement from "../../Element/woltlab-core-dialog";
import { MarkerClusterer } from "@googlemaps/markerclusterer";
import { getMapMarkers, MarkerData } from "../../Api/GoogleMaps/GetMapMarkers";
import "./woltlab-core-google-maps";

type AdditionalParameters = Record<string, unknown>;

type ResponseGetMapMarkers = {
  markers: MarkerData[];
};

/**
 * Fetches the markers located within the given boundaries, excluding the
 * markers that have already been loaded.
 */
type MarkerFetcher = (
  northEast: google.maps.LatLng,
  southWest: google.maps.LatLng,
  excludedObjectIDs: number[],
) => Promise<ResponseGetMapMarkers>;

class MarkerLoader {
  readonly #map: google.maps.Map;
  readonly #fetchMarkers: MarkerFetcher;
  readonly #clusterer: MarkerClusterer;
  #previousNorthEast: google.maps.LatLng;
  #previousSouthWest: google.maps.LatLng;
  #objectIDs: number[] = [];

  constructor(map: google.maps.Map, fetchMarkers: MarkerFetcher) {
    this.#map = map;
    this.#fetchMarkers = fetchMarkers;

    this.#clusterer = new MarkerClusterer({
      map,
    });

    void this.#initLoadMarkers();
  }

  async #initLoadMarkers(): Promise<void> {
    if (this.#map.getBounds()) {
      // The map has already been loaded and the 'idle'
      // event listener is therefore not called initially.
      await this.#loadMarkers();
    }

    this.#map.addListener("idle", () => {
      void this.#loadMarkers();
    });
  }

  async #loadMarkers(): Promise<void> {
    const northEast = this.#map.getBounds()!.getNorthEast();
    const southWest = this.#map.getBounds()!.getSouthWest();

    if (!this.#checkPreviousLocation(northEast, southWest)) {
      return;
    }

    const response = await this.#fetchMarkers(northEast, southWest, this.#objectIDs);

    response.markers.forEach((data) => {
      this.#addMarker(data);
    });

    this.#clusterer.render();
  }

  #addMarker(data: MarkerData) {
    const marker = new google.maps.marker.AdvancedMarkerElement({
      map: this.#map,
      position: new google.maps.LatLng(data.latitude, data.longitude),
      title: data.title,
    });

    this.#clusterer.addMarker(marker, true);

    if (data.infoWindow) {
      const content = document.createElement("div");
      content.classList.add("googleMapsInfoWindow");
      DomUtil.setInnerHtml(content, data.infoWindow);

      const infoWindow = new google.maps.InfoWindow({
        headerContent: data.title,
        content,
      });

      marker.addListener("click", () => {
        infoWindow.open(this.#map, marker);
      });

      if (data.dialog) {
        let dialog: WoltlabCoreDialogElement;
        infoWindow.addListener("domready", () => {
          const button = content.querySelector<HTMLElement>(".jsButtonShowDialog");
          button?.addEventListener("click", () => {
            if (!dialog) {
              dialog = dialogFactory().fromHtml(data.dialog!).withoutControls();
            }
            dialog.show(button.dataset.title || button.textContent);
          });
        });
      }
    }

    if (data.objectID) {
      this.#objectIDs.push(data.objectID);
    }

    if (data.objectIDs) {
      this.#objectIDs.push(...data.objectIDs);
    }
  }

  /**
   * Checks if the user has zoomed in, then all markers are already displayed.
   */
  #checkPreviousLocation(northEast: google.maps.LatLng, southWest: google.maps.LatLng): boolean {
    if (
      this.#previousNorthEast &&
      this.#previousNorthEast.lat() >= northEast.lat() &&
      this.#previousNorthEast.lng() >= northEast.lng() &&
      this.#previousSouthWest.lat() <= southWest.lat() &&
      this.#previousSouthWest.lng() <= southWest.lng()
    ) {
      return false;
    }

    this.#previousNorthEast = northEast;
    this.#previousSouthWest = southWest;

    return true;
  }
}

/**
 * Loads the markers using the legacy `getMapMarkers` DBO action of the given class.
 *
 * @deprecated 6.3 use `setupWithEndpoint()` with a dedicated RPC endpoint instead
 */
export async function setup(
  googleMaps: WoltlabCoreGoogleMapsElement,
  actionClassName: string,
  additionalParameters: AdditionalParameters,
): Promise<void> {
  const map = await googleMaps.getMap();
  new MarkerLoader(map, (northEast, southWest, excludedObjectIDs) => {
    return dboAction("getMapMarkers", actionClassName)
      .payload({
        ...additionalParameters,
        excludedObjectIDs: JSON.stringify(excludedObjectIDs),
        eastLongitude: northEast.lng(),
        northLatitude: northEast.lat(),
        southLatitude: southWest.lat(),
        westLongitude: southWest.lng(),
      })
      .dispatch() as Promise<ResponseGetMapMarkers>;
  });
}

/**
 * Loads the markers using an RPC endpoint, e.g. `calendar/events/map-markers`.
 *
 * @since 6.3
 */
export async function setupWithEndpoint(
  googleMaps: WoltlabCoreGoogleMapsElement,
  endpoint: string,
  additionalParameters: AdditionalParameters = {},
): Promise<void> {
  const map = await googleMaps.getMap();
  new MarkerLoader(map, (northEast, southWest, excludedObjectIDs) => {
    return getMapMarkers(
      endpoint,
      {
        northLatitude: northEast.lat(),
        southLatitude: southWest.lat(),
        eastLongitude: northEast.lng(),
        westLongitude: southWest.lng(),
      },
      excludedObjectIDs,
      additionalParameters,
    );
  });
}
