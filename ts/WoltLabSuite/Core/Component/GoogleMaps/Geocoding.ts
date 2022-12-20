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
          setLocation(results![0].geometry.location.lat(), results![0].geometry.location.lng());
        }
      });
    });

    function moveMarker(address: string): void {
      void geocoder.geocode({ address }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK) {
          marker.setPosition(results![0].geometry.location);
          (marker.getMap() as google.maps.Map).setCenter(marker.getPosition()!);
          setLocation(results![0].geometry.location.lat(), results![0].geometry.location.lng());
        }
      });
    }

    function setLocation(lat: number, lng: number): void {
      element.dataset.googleMapsLat = lat.toString();
      element.dataset.googleMapsLng = lng.toString();
    }

    if (element.value) {
      moveMarker(element.value);
    }

    setupSuggestion(element, geocoder, (item: HTMLElement) => {
      moveMarker(item.dataset.label!);
      return true;
    });
  });
}
