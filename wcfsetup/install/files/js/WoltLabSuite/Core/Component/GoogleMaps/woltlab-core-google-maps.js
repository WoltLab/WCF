define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreGoogleMapsElement = void 0;
    let initCalled = false;
    const callbackPromise = new Promise((resolve) => {
        window.woltlab_core_google_maps_callback = resolve;
    });
    const loadGoogleMaps = (apiKey) => {
        if (!initCalled) {
            const script = document.createElement("script");
            script.src =
                "https://maps.googleapis.com/maps/api/js?" +
                    (apiKey ? `key=${apiKey}&` : "") +
                    "callback=woltlab_core_google_maps_callback";
            document.head.appendChild(script);
            initCalled = true;
        }
        return callbackPromise;
    };
    class WoltlabCoreGoogleMapsElement extends HTMLElement {
        #map;
        #mapLoaded;
        #mapLoadedResolve;
        #rendered = false;
        #geocoder;
        constructor() {
            super();
            this.#mapLoaded = new Promise((resolve) => {
                this.#mapLoadedResolve = resolve;
            });
        }
        connectedCallback() {
            if (!this.hidden) {
                this.#render();
            }
        }
        attributeChangedCallback(name, oldValue, newValue) {
            if (name === "hidden" && newValue === null) {
                this.#render();
            }
        }
        static get observedAttributes() {
            return ["hidden"];
        }
        #render() {
            if (this.#rendered) {
                return;
            }
            this.#validate();
            void loadGoogleMaps(this.apiKey).then(() => {
                this.#map = new google.maps.Map(this, {
                    zoom: this.zoom,
                    center: {
                        lat: this.lat,
                        lng: this.lng,
                    },
                });
                if (this.#mapLoadedResolve) {
                    this.#mapLoadedResolve();
                    this.#mapLoadedResolve = undefined;
                }
            });
            this.#rendered = true;
        }
        async addMarker(latitude, longitude, title, focus) {
            await this.#mapLoaded;
            const marker = new google.maps.Marker({
                map: this.map,
                position: new google.maps.LatLng(latitude, longitude),
                title,
            });
            if (focus) {
                this.map.setCenter(marker.getPosition());
            }
        }
        async addDraggableMarker(latitude, longitude) {
            await this.#mapLoaded;
            if (latitude === undefined) {
                latitude = this.lat;
            }
            if (longitude === undefined) {
                longitude = this.lng;
            }
            const marker = new google.maps.Marker({
                map: this.map,
                position: new google.maps.LatLng(latitude, longitude),
                draggable: true,
                clickable: false,
            });
            this.map.setCenter(marker.getPosition());
            return marker;
        }
        #validate() {
            if (!this.apiKey) {
                //throw new TypeError("Must provide an api key.");
            }
        }
        get apiKey() {
            return this.getAttribute("api-key") || "";
        }
        get map() {
            return this.#map;
        }
        get lat() {
            return this.getAttribute("lat") ? parseFloat(this.getAttribute("lat")) : 0;
        }
        get lng() {
            return this.getAttribute("lng") ? parseFloat(this.getAttribute("lng")) : 0;
        }
        get zoom() {
            return this.getAttribute("zoom") ? parseInt(this.getAttribute("zoom")) : 13;
        }
        async getGeocoder() {
            await this.#mapLoaded;
            if (this.#geocoder === undefined) {
                this.#geocoder = new google.maps.Geocoder();
            }
            return this.#geocoder;
        }
    }
    exports.WoltlabCoreGoogleMapsElement = WoltlabCoreGoogleMapsElement;
    window.customElements.define("woltlab-core-google-maps", WoltlabCoreGoogleMapsElement);
    exports.default = WoltlabCoreGoogleMapsElement;
});
