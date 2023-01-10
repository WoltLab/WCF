/**
 * Provides geocoding functions for searching map locations.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Component/GoogleMaps/Geocoding
 */

import { wheneverFirstSeen } from "../../Helper/Selector";
import { setup as setupSuggestion } from "./Geocoding/Suggestion";
import { addDraggableMarker } from "./Marker";
import type WoltlabCoreGoogleMapsElement from "./woltlab-core-google-maps";

import "./woltlab-core-google-maps";

class Geocoding {
  readonly #element: HTMLInputElement;
  readonly #geocoder: google.maps.Geocoder;
  readonly #map: WoltlabCoreGoogleMapsElement;
  #marker?: google.maps.Marker;

  constructor(element: HTMLInputElement, map: WoltlabCoreGoogleMapsElement, geocoder: google.maps.Geocoder) {
    this.#element = element;
    this.#map = map;
    this.#geocoder = geocoder;

    if (this.#element.hasAttribute("data-google-maps-marker")) {
      void this.#setupMarker();
    }

    if (element.value) {
      this.#moveMarker(element.value);
    }

    setupSuggestion(this.#element, this.#geocoder, (item: HTMLElement) => {
      this.#moveMarker(item.dataset.label!);

      return true;
    });
  }

  async #setupMarker(): Promise<void> {
    this.#marker = await addDraggableMarker(this.#map);
    this.#marker.addListener("dragend", () => {
      void this.#geocoder.geocode({ location: this.#marker!.getPosition() }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK) {
          this.#element.value = results![0].formatted_address;
          this.#setLocation(results![0].geometry.location.lat(), results![0].geometry.location.lng());
        }
      });
    });
  }

  #moveMarker(address: string): void {
    void this.#geocoder.geocode({ address }, async (results, status) => {
      if (status === google.maps.GeocoderStatus.OK) {
        this.#marker?.setPosition(results![0].geometry.location);

        (await this.#map.getMap()).setCenter(results![0].geometry.location);
        this.#setLocation(results![0].geometry.location.lat(), results![0].geometry.location.lng());
      }
    });
  }

  #setLocation(lat: number, lng: number): void {
    this.#element.dataset.googleMapsLat = lat.toString();
    this.#element.dataset.googleMapsLng = lng.toString();

    if (this.#element.hasAttribute("data-google-maps-geocoding-store") && this.#element.form) {
      this.#store(this.#element.dataset.googleMapsGeocdingStore || '', lat, lng);
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
  wheneverFirstSeen("[data-google-maps-geocoding]", async (element: HTMLInputElement) => {
    const map = document.getElementById(element.dataset.googleMapsGeocoding!) as WoltlabCoreGoogleMapsElement;
    const geocoder = await map.getGeocoder();

    new Geocoding(element, map, geocoder);
  });
}
