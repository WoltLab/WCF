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
import "./woltlab-core-google-maps";

type AdditionalParameters = Record<string, unknown>;

type MarkerData = {
  dialog?: string;
  infoWindow: string;
  items: number;
  latitude: number;
  location: string;
  longitude: number;
  objectIDs?: number[];
  objectID?: number;
  title: string;
};

type ResponseGetMapMarkers = {
  markers: MarkerData[];
};

class MarkerLoader {
  readonly #map: google.maps.Map;
  readonly #actionClassName: string;
  readonly #additionalParameters: AdditionalParameters;
  readonly #clusterer: MarkerClusterer;
  #previousNorthEast: google.maps.LatLng;
  #previousSouthWest: google.maps.LatLng;
  #objectIDs: number[] = [];

  constructor(map: google.maps.Map, actionClassName: string, additionalParameters: AdditionalParameters) {
    this.#map = map;
    this.#actionClassName = actionClassName;
    this.#additionalParameters = additionalParameters;

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

    const response = (await dboAction("getMapMarkers", this.#actionClassName)
      .payload({
        ...this.#additionalParameters,
        excludedObjectIDs: JSON.stringify(this.#objectIDs),
        eastLongitude: northEast.lng(),
        northLatitude: northEast.lat(),
        southLatitude: southWest.lat(),
        westLongitude: southWest.lng(),
      })
      .dispatch()) as ResponseGetMapMarkers;

    response.markers.forEach((data) => {
      this.#addMarker(data);
    });
  }

  #addMarker(data: MarkerData) {
    const marker = new google.maps.Marker({
      map: this.#map,
      position: new google.maps.LatLng(data.latitude, data.longitude),
      title: data.title,
    });

    this.#clusterer.addMarker(marker);

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
            dialog.show(button.dataset.title || button.textContent!);
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

export async function setup(
  googleMaps: WoltlabCoreGoogleMapsElement,
  actionClassName: string,
  additionalParameters: AdditionalParameters,
): Promise<void> {
  const map = await googleMaps.getMap();
  new MarkerLoader(map, actionClassName, additionalParameters);
}
