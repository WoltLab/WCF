import { dboAction } from "../../Ajax";
import WoltlabCoreGoogleMapsElement from "./woltlab-core-google-maps";

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
  #previousNorthEast: google.maps.LatLng;
  #previousSouthWest: google.maps.LatLng;
  #objectIDs: number[] = [];

  constructor(map: google.maps.Map, actionClassName: string, additionalParameters: AdditionalParameters) {
    this.#map = map;
    this.#actionClassName = actionClassName;
    this.#additionalParameters = additionalParameters;

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

    if (data.infoWindow) {
      const infoWindow = new google.maps.InfoWindow({
        content: data.infoWindow,
      });

      marker.addListener("click", () => {
        infoWindow.open(this.#map, marker);
      });
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
