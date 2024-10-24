/**
 * Provides geocoding functions for searching map locations.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { wheneverFirstSeen } from "../../Helper/Selector";
import { setup as setupSuggestion } from "./Geocoding/Suggestion";
import { addDraggableMarker } from "./Marker";
import type WoltlabCoreGoogleMapsElement from "./woltlab-core-google-maps";

import "./woltlab-core-google-maps";

export type MoveMarkerEventPayload = {
  latitude: number;
  longitude: number;
};

export type ResolveEventPayload = {
  callback: (location: string) => void;
  latitude: number;
  longitude: number;
};

class Geocoding {
  readonly #element: HTMLInputElement;
  readonly #map: WoltlabCoreGoogleMapsElement;
  #marker?: google.maps.marker.AdvancedMarkerElement;
  #initialMarkerPosition?: google.maps.LatLng | google.maps.LatLngLiteral | null;

  constructor(element: HTMLInputElement, map: WoltlabCoreGoogleMapsElement) {
    this.#element = element;
    this.#map = map;

    this.#initEvents();

    void this.#map.getGeocoder().then((geocoder) => {
      if (this.#element.hasAttribute("data-google-maps-marker")) {
        void this.#setupMarker();
      }

      if (element.value) {
        void this.#moveMarkerToAddress(element.value);
      }

      setupSuggestion(this.#element, geocoder, (item: HTMLElement) => {
        void this.#moveMarkerToAddress(item.dataset.label!);

        return true;
      });
    });
  }

  #initEvents(): void {
    this.#element.addEventListener("geocoding:move-marker", (event: CustomEvent<MoveMarkerEventPayload>) => {
      void this.#moveMarkerToLocation(new google.maps.LatLng(event.detail.latitude, event.detail.longitude));
    });

    this.#element.addEventListener("geocoding:resolve", (event: CustomEvent<ResolveEventPayload>) => {
      void this.#map.getGeocoder().then((geocoder) => {
        const location = new google.maps.LatLng(event.detail.latitude, event.detail.longitude);
        void geocoder.geocode({ location }, (results, status) => {
          if (status === google.maps.GeocoderStatus.OK) {
            event.detail.callback(results![0].formatted_address);
          }
        });
      });
    });

    this.#element.addEventListener("geocoding:reset-marker", () => {
      if (this.#initialMarkerPosition) {
        void this.#moveMarkerToLocation(new google.maps.LatLng(this.#initialMarkerPosition));
      }
    });
  }

  async #setupMarker(): Promise<void> {
    this.#marker = await addDraggableMarker(this.#map);
    this.#initialMarkerPosition = this.#marker?.position;

    this.#marker.addListener("dragend", () => {
      void this.#map.getGeocoder().then((geocoder) => {
        void geocoder.geocode({ location: this.#marker!.position! }, (results, status) => {
          if (status === google.maps.GeocoderStatus.OK) {
            this.#element.value = results![0].formatted_address;
            this.#setLocation(results![0].geometry.location.lat(), results![0].geometry.location.lng());
          }
        });
      });
    });
  }

  async #moveMarkerToLocation(location: google.maps.LatLng): Promise<void> {
    if (this.#marker) {
      this.#marker.position = location;
    }
    (await this.#map.getMap()).setCenter(location);
    this.#setLocation(location.lat(), location.lng());
  }

  async #moveMarkerToAddress(address: string): Promise<void> {
    const geocoder = await this.#map.getGeocoder();
    void geocoder.geocode({ address }, async (results, status) => {
      if (status === google.maps.GeocoderStatus.OK) {
        if (this.#marker) {
          this.#marker.position = results![0].geometry.location;
        }

        (await this.#map.getMap()).setCenter(results![0].geometry.location);
        this.#setLocation(results![0].geometry.location.lat(), results![0].geometry.location.lng());
      }
    });
  }

  #setLocation(lat: number, lng: number): void {
    this.#element.dataset.googleMapsLat = lat.toString();
    this.#element.dataset.googleMapsLng = lng.toString();

    const prefix = this.#element.dataset.googleMapsGeocodingStore;
    if (prefix != null && this.#element.form) {
      this.#store(prefix, lat, lng);
    }
  }

  #store(prefix: string, lat: number, lng: number): void {
    const name = prefix + "coordinates";
    let input = this.#element.form!.querySelector<HTMLInputElement>(`input[name="${name}"]`);
    if (!input) {
      input = document.createElement("input");
      input.type = "hidden";
      input.name = name;
      this.#element.form!.append(input);
    }

    input.value = `${lat},${lng}`;
  }
}

export function setup(): void {
  wheneverFirstSeen("[data-google-maps-geocoding]", (element: HTMLInputElement) => {
    const map = document.getElementById(element.dataset.googleMapsGeocoding!) as WoltlabCoreGoogleMapsElement;

    new Geocoding(element, map);
  });
}
