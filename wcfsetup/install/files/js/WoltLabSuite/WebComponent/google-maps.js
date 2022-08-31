"use strict";
{
    let initCalled = false;
    const callbackPromise = new Promise((resolve) => {
        window.__initGoogleMaps = resolve;
    });
    const loadGoogleMaps = (apiKey) => {
        if (!initCalled) {
            const script = document.createElement("script");
            script.src =
                "https://maps.googleapis.com/maps/api/js?" + (apiKey ? `key=${apiKey}&` : "") + "callback=__initGoogleMaps";
            document.head.appendChild(script);
            initCalled = true;
        }
        return callbackPromise;
    };
    class GoogleMaps extends HTMLElement {
        constructor() {
            super();
            this._map = undefined;
            this.mapLoadedResolve = undefined;
            this.mapLoaded = new Promise((resolve) => {
                this.mapLoadedResolve = resolve;
            });
        }
        connectedCallback() {
            this.validate();
            void loadGoogleMaps(this.apiKey).then(() => {
                this._map = new google.maps.Map(this, {
                    zoom: 13,
                    center: {
                        lat: 0,
                        lng: 0,
                    },
                });
                if (this.mapLoadedResolve) {
                    this.mapLoadedResolve();
                    this.mapLoadedResolve = undefined;
                }
            });
        }
        async addMarker(latitude, longitude, title, focus) {
            await this.mapLoaded;
            const marker = new google.maps.Marker({
                map: this.map,
                position: new google.maps.LatLng(latitude, longitude),
                title: title,
            });
            if (focus) {
                this.map.setCenter(marker.getPosition());
            }
        }
        validate() {
            if (!this.apiKey) {
                //throw new TypeError("Must provide an api key.");
            }
        }
        get apiKey() {
            return this.getAttribute("api-key") || "";
        }
        get map() {
            return this._map;
        }
    }
    window.customElements.define("google-maps", GoogleMaps);
}
