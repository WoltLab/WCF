import { wheneverFirstSeen } from "../../Helper/Selector";
import { setup as setupSuggestion } from "./Geocoding/Suggestion";
import { addDraggableMarker } from "./Marker";
import type WoltlabCoreGoogleMapsElement from "./woltlab-core-google-maps";

import "./woltlab-core-google-maps";

export function setup(): void {
  wheneverFirstSeen("[data-google-maps-geocoding]", async (element: HTMLInputElement) => {
    const map = document.getElementById(element.dataset.googleMapsGeocoding!) as WoltlabCoreGoogleMapsElement;

    const marker = await addDraggableMarker(map);
    const geocoder = await map.getGeocoder();
    marker.addListener("dragend", () => {
      void geocoder.geocode({ location: marker.getPosition() }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK) {
          element.value = results![0].formatted_address;
        }
      });
    });

    setupSuggestion(element, geocoder);
  });
}
