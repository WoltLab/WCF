"use strict";

/**
 * Location-related classes for WCF
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Location = { };

/**
 * Provides location-related utility functions.
 */
WCF.Location.Util = {
	/**
	 * Passes the user's current latitude and longitude to the given function
	 * as parameters. If the user's current position cannot be determined,
	 * undefined will be passed as both parameters.
	 * 
	 * @param	function	callback
	 * @param	integer		timeout
	 */
	getLocation: function(callback, timeout) {
		var $accessUserLocation = WCF.Location.GoogleMaps.Settings.get('accessUserLocation');
		if (navigator.geolocation && $accessUserLocation !== null && $accessUserLocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
				callback(position.coords.latitude, position.coords.longitude);
			}, function() {
				callback(undefined, undefined);
			}, {
				timeout: timeout || 5000
			});
		}
		else {
			callback(undefined, undefined);
		}
	}
};

/**
 * Namespace for Google Maps-related classes.
 */
WCF.Location.GoogleMaps = { };

/**
 * Handles the global Google Maps settings.
 */
WCF.Location.GoogleMaps.Settings = {
	/**
	 * Google Maps settings
	 * @var	object
	 */
	_settings: { },
	
	/**
	 * Returns the value of a certain setting or null if it doesn't exist.
	 * 
	 * If no parameter is given, all settings are returned.
	 * 
	 * @param	string		setting
	 * @return	mixed
	 */
	get: function(setting) {
		if (setting === undefined) {
			return this._settings;
		}
		
		if (this._settings[setting] !== undefined) {
			return this._settings[setting];
		}
		
		return null;
	},
	
	/**
	 * Sets the value of a certain setting.
	 * 
	 * @param	mixed		setting
	 * @param	mixed		value
	 */
	set: function(setting, value) {
		if ($.isPlainObject(setting)) {
			for (var index in setting) {
				this._settings[index] = setting[index];
			}
		}
		else {
			this._settings[setting] = value;
		}
	}
};

/**
 * Handles a Google Maps map.
 */
WCF.Location.GoogleMaps.Map = Class.extend({
	/**
	 * map object for the displayed map
	 * @var	google.maps.Map
	 */
	_map: null,
	
	/**
	 * list of markers on the map
	 * @var	array<google.maps.Marker>
	 */
	_markers: [ ],
	
	/**
	 * Initalizes a new WCF.Location.Map object.
	 * 
	 * @param	string		mapContainerID
	 * @param	object		mapOptions
	 */
	init: function(mapContainerID, mapOptions) {
		this._mapContainer = $('#' + mapContainerID);
		this._mapOptions = $.extend(true, this._getDefaultMapOptions(), mapOptions);
		
		this._map = new google.maps.Map(this._mapContainer[0], this._mapOptions);
		this._markers = [ ];
		
		// fix maps in mobile sidebars by refreshing the map when displaying
		// the map
		if (this._mapContainer.parents('.sidebar').length) {
			enquire.register('(max-width: 767px)', {
				setup: $.proxy(this._addSidebarMapListener, this),
				deferSetup: true
			});
		}
		
		this.refresh();
	},
	
	/**
	 * Adds the event listener to a marker to show the associated info window.
	 * 
	 * @param	google.maps.Marker	marker
	 * @param	google.maps.InfoWindow	infoWindow
	 */
	_addInfoWindowEventListener: function(marker, infoWindow) {
		google.maps.event.addListener(marker, 'click', $.proxy(function() {
			infoWindow.open(this._map, marker);
		}, this));
	},
	
	/**
	 * Adds click listener to mobile sidebar toggle button to refresh map.
	 */
	_addSidebarMapListener: function() {
		$('.content > .mobileSidebarToggleButton').click($.proxy(this.refresh, this));
	},
	
	/**
	 * Returns the default map options.
	 * 
	 * @return	object
	 */
	_getDefaultMapOptions: function() {
		var $defaultMapOptions = { };
		
		// dummy center value
		$defaultMapOptions.center = new google.maps.LatLng(WCF.Location.GoogleMaps.Settings.get('defaultLatitude'), WCF.Location.GoogleMaps.Settings.get('defaultLongitude'));
		
		// double click to zoom
		$defaultMapOptions.disableDoubleClickZoom = WCF.Location.GoogleMaps.Settings.get('disableDoubleClickZoom');
		
		// draggable
		$defaultMapOptions.draggable = WCF.Location.GoogleMaps.Settings.get('draggable');
		
		// map type
		switch (WCF.Location.GoogleMaps.Settings.get('mapType')) {
			case 'map':
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
			break;
			
			case 'satellite':
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
			break;
			
			case 'physical':
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
			break;
			
			case 'hybrid':
			default:
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
			break;
		}
		
		/// map type controls
		$defaultMapOptions.mapTypeControl = WCF.Location.GoogleMaps.Settings.get('mapTypeControl') != 'off';
		if ($defaultMapOptions.mapTypeControl) {
			switch (WCF.Location.GoogleMaps.Settings.get('mapTypeControl')) {
				case 'dropdown':
					$defaultMapOptions.mapTypeControlOptions = {
						style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
					};
				break;
				
				case 'horizontalBar':
					$defaultMapOptions.mapTypeControlOptions = {
						style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR
					};
				break;
				
				default:
					$defaultMapOptions.mapTypeControlOptions = {
						style: google.maps.MapTypeControlStyle.DEFAULT
					};
				break;
			}
		}
		
		// scale control
		$defaultMapOptions.scaleControl = WCF.Location.GoogleMaps.Settings.get('scaleControl');
		$defaultMapOptions.scrollwheel = WCF.Location.GoogleMaps.Settings.get('scrollwheel');
		
		// zoom
		$defaultMapOptions.zoom = WCF.Location.GoogleMaps.Settings.get('zoom');
		
		return $defaultMapOptions;
	},
	
	/**
	 * Adds a draggable marker at the given position to the map and returns
	 * the created marker object.
	 * 
	 * @param	float		latitude
	 * @param	float		longitude
	 * @return	google.maps.Marker
	 */
	addDraggableMarker: function(latitude, longitude) {
		var $marker = new google.maps.Marker({
			clickable: false,
			draggable: true,
			map: this._map,
			position: new google.maps.LatLng(latitude, longitude),
			zIndex: 1
		});
		
		this._markers.push($marker);
		
		return $marker;
	},
	
	/**
	 * Adds a marker with the given data to the map and returns the created
	 * marker object.
	 * 
	 * @param	float		latitude
	 * @param	float		longitude
	 * @param	string		title
	 * @param	mixed		icon
	 * @param	string		information
	 * @return	google.maps.Marker
	 */
	addMarker: function(latitude, longitude, title, icon, information) {
		var $marker = new google.maps.Marker({
			map: this._map,
			position: new google.maps.LatLng(latitude, longitude),
			title: title
		});
		
		// add icon
		if (icon) {
			$marker.setIcon(icon);
		}
		
		// add info window for marker information
		if (information) {
			var $infoWindow = new google.maps.InfoWindow({
				content: information
			});
			this._addInfoWindowEventListener($marker, $infoWindow);
			
			// add info window object to marker object
			$marker.infoWindow = $infoWindow;
		}
		
		this._markers.push($marker);
		
		return $marker;
	},
	
	/**
	 * Returns all markers on the map.
	 * 
	 * @return	array<google.maps.Marker>
	 */
	getMarkers: function() {
		return this._markers;
	},
	
	/**
	 * Returns the Google Maps map object.
	 * 
	 * @return	google.maps.Map
	 */
	getMap: function() {
		return this._map;
	},
	
	/**
	 * Refreshes the map.
	 */
	refresh: function() {
		// save current center since resize does not preserve it
		var $center = this._map.getCenter();
		
		google.maps.event.trigger(this._map, 'resize');
		
		// set center to old value again
		this._map.setCenter($center);
	},
	
	/**
	 * Refreshes the boundaries of the map to show all markers.
	 */
	refreshBounds: function() {
		var $minLatitude = null;
		var $maxLatitude = null;
		var $minLongitude = null;
		var $maxLongitude = null;
		
		for (var $index in this._markers) {
			var $marker = this._markers[$index];
			var $latitude = $marker.getPosition().lat();
			var $longitude = $marker.getPosition().lng();
			
			if ($minLatitude === null) {
				$minLatitude = $maxLatitude = $latitude;
				$minLongitude = $maxLongitude = $longitude;
			}
			else {
				if ($minLatitude > $latitude) {
					$minLatitude = $latitude;
				}
				else if ($maxLatitude < $latitude) {
					$maxLatitude = $latitude;
				}
				
				if ($minLongitude > $latitude) {
					$minLongitude = $latitude;
				}
				else if ($maxLongitude < $longitude) {
					$maxLongitude = $longitude;
				}
			}
		}
		
		this._map.fitBounds(new google.maps.LatLngBounds(
			new google.maps.LatLng($minLatitude, $minLongitude),
			new google.maps.LatLng($maxLatitude, $maxLongitude)
		));
	},
	
	/**
	 * Removes all markers from the map.
	 */
	removeMarkers: function() {
		for (var $index in this._markers) {
			this._markers[$index].setMap(null);
		}
		
		this._markers = [ ];
	},
	
	/**
	 * Changes the bounds of the map.
	 * 
	 * @param	object		northEast
	 * @param	object		southWest
	 */
	setBounds: function(northEast, southWest) {
		this._map.fitBounds(new google.maps.LatLngBounds(
			new google.maps.LatLng(southWest.latitude, southWest.longitude),
			new google.maps.LatLng(northEast.latitude, northEast.longitude)
		));
	},
	
	/**
	 * Sets the center of the map to the given position.
	 * 
	 * @param	float		latitude
	 * @param	float		longitude
	 */
	setCenter: function(latitude, longitude) {
		this._map.setCenter(new google.maps.LatLng(latitude, longitude));
	}
});

/**
 * Handles a large map with many markers where (new) markers are loaded via AJAX.
 */
WCF.Location.GoogleMaps.LargeMap = WCF.Location.GoogleMaps.Map.extend({
	/**
	 * name of the PHP class executing the 'getMapMarkers' action
	 * @var	string
	 */
	_actionClassName: null,
	
	/**
	 * additional parameters for executing the 'getMapMarkers' action
	 * @var	object
	 */
	_additionalParameters: { },
	
	/**
	 * indicates if the maps center can be set by location search
	 * @var	WCF.Location.GoogleMaps.LocationSearch
	 */
	_locationSearch: null,
	
	/**
	 * selector for the location search input
	 * @var	string
	 */
	_locationSearchInputSelector: null,
	
	/**
	 * cluster handling the markers on the map
	 * @var	MarkerClusterer
	 */
	_markerClusterer: null,
	
	/**
	 * ids of the objects which are already displayed
	 * @var	array<integer>
	 */
	_objectIDs: [ ],
	
	/**
	 * previous coordinates of the north east map boundary
	 * @var	google.maps.LatLng
	 */
	_previousNorthEast: null,
	
	/**
	 * previous coordinates of the south west map boundary
	 * @var	google.maps.LatLng
	 */
	_previousSouthWest: null,
	
	/**
	 * @see	WCF.Location.GoogleMaps.Map.init()
	 */
	init: function(mapContainerID, mapOptions, actionClassName, locationSearchInputSelector, additionalParameters) {
		this._super(mapContainerID, mapOptions);
		
		this._actionClassName = actionClassName;
		this._locationSearchInputSelector = locationSearchInputSelector || '';
		this._additionalParameters = additionalParameters || { };
		this._objectIDs = [ ];
		
		if (this._locationSearchInputSelector) {
			this._locationSearch = new WCF.Location.GoogleMaps.LocationSearch(locationSearchInputSelector, $.proxy(this._centerMap, this));
		}
		
		this._markerClusterer = new MarkerClusterer(this._map, this._markers, {
			maxZoom: 17,
			imagePath: WCF.Location.GoogleMaps.Settings.get('markerClustererImagePath') + 'm'
		});
		
		this._markerSpiderfier = new OverlappingMarkerSpiderfier(this._map, {
			keepSpiderfied: true,
			markersWontHide: true,
			markersWontMove: true
		});
		this._markerSpiderfier.addListener('click', $.proxy(function(marker) {
			if (marker.infoWindow) {
				marker.infoWindow.open(this._map, marker);
			}
		}, this));
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false,
			success: $.proxy(this._success, this)
		});
		
		this._previousNorthEast = null;
		this._previousSouthWest = null;
		google.maps.event.addListener(this._map, 'idle', $.proxy(this._loadMarkers, this));
	},
	
	/**
	 * @see	WCF.Location.GoogleMaps.Map.addMarker()
	 */
	_addInfoWindowEventListener: function(marker, infoWindow) {
		// does nothing, is handled by the event listener of the marker
		// spiderfier
	},
	
	/**
	 * Centers the map based on a location search result.
	 * 
	 * @param	object		data
	 */
	_centerMap: function(data) {
		this.setCenter(data.location.lat(), data.location.lng());
		
		$(this._locationSearchInputSelector).val(data.label);
	},
	
	/**
	 * Loads markers if the map is reloaded. Returns true if new markers will
	 * be loaded and false if for the current map bounds, the markers have already
	 * been loaded.
	 * 
	 * @return	boolean
	 */
	_loadMarkers: function() {
		var $northEast = this._map.getBounds().getNorthEast();
		var $southWest = this._map.getBounds().getSouthWest();
		
		// check if the user has zoomed in, then all markers are already
		// displayed
		if (this._previousNorthEast && this._previousNorthEast.lat() >= $northEast.lat() && this._previousNorthEast.lng() >= $northEast.lng() && this._previousSouthWest.lat() <= $southWest.lat() && this._previousSouthWest.lng() <= $southWest.lng()) {
			return false;
		}
		
		this._previousNorthEast = $northEast;
		this._previousSouthWest = $southWest;
		
		this._proxy.setOption('data', {
			actionName: 'getMapMarkers',
			className: this._actionClassName,
			parameters: $.extend(this._additionalParameters, {
				excludedObjectIDs: this._objectIDs,
				eastLongitude: $northEast.lng(),
				northLatitude: $northEast.lat(),
				southLatitude: $southWest.lat(),
				westLongitude: $southWest.lng()
			})
		});
		this._proxy.sendRequest();
		
		return true;
	},
	
	/**
	 * Handles a successful AJAX request.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues && data.returnValues.markers) {
			for (var $index in data.returnValues.markers) {
				var $markerInfo = data.returnValues.markers[$index];
				
				this.addMarker($markerInfo.latitude, $markerInfo.longitude, $markerInfo.title, null, $markerInfo.infoWindow);
				
				if ($markerInfo.objectID) {
					this._objectIDs.push($markerInfo.objectID);
				}
				else if ($markerInfo.objectIDs) {
					this._objectIDs = this._objectIDs.concat($markerInfo.objectIDs);
				}
			}
		}
	},
	
	/**
	 * @see	WCF.Location.GoogleMaps.Map.addMarker()
	 */
	addMarker: function(latitude, longitude, title, icon, information) {
		var $marker = this._super(latitude, longitude, title, icon, information);
		this._markerClusterer.addMarker($marker);
		this._markerSpiderfier.addMarker($marker);
		
		return $marker;
	}
});

/**
 * Extends the large map implementation by treating non-draggable markers as location
 * suggestions.
 */
WCF.Location.GoogleMaps.SuggestionMap = WCF.Location.GoogleMaps.LargeMap.extend({
	/**
	 * maps control showing/hiding location suggestions
	 * @var	jQuery
	 */
	_locationSuggestionsButton: null,
	
	/**
	 * function called when a location is selected
	 * @var	function
	 */
	_suggestionSelectionCallback: null,
	
	/**
	 * @see	WCF.Location.GoogleMaps.LargeMap.init()
	 */
	init: function(mapContainerID, mapOptions, actionClassName, locationSearchInputSelector, additionalParameters) {
		this._super(mapContainerID, mapOptions, actionClassName, locationSearchInputSelector, additionalParameters);
		
		var $locationSuggestionDiv = $('<div class="gmnoprint googleMapsCustomControlContainer"><div class="gm-style-mtc"><div class="googleMapsCustomControl">' + WCF.Language.get('wcf.map.showLocationSuggestions') + '</div></div></div>');
		this._locationSuggestionsButton = $locationSuggestionDiv.find('.googleMapsCustomControl').click($.proxy(this._toggleLocationSuggestions, this));
		
		this._map.controls[google.maps.ControlPosition.TOP_RIGHT].push($locationSuggestionDiv.get(0));
	},
	
	/**
	 * @see	WCF.Location.GoogleMaps.LargeMap._loadMarkers()
	 */
	_loadMarkers: function() {
		if (!this._locationSuggestionsButton.hasClass('active')) return;
		
		if (!this._super()) {
			this._loadSuggestions = false;
		}
	},
	
	/**
	 * @see	WCF.Location.GoogleMaps.LargeMap._loadMarkers()
	 */
	_success: function(data, textStatus, jqXHR) {
		var $oldLength = this._markers.length;
		this._super(data, textStatus, jqXHR);
		
		if (this._loadSuggestions && $oldLength == this._markers.length) {
			this._loadSuggestions = false;
			new WCF.System.Notification(WCF.Language.get('wcf.map.noLocationSuggestions'), 'info').show();
		}
	},
	
	/**
	 * Handles clicks on the location suggestions button.
	 */
	_toggleLocationSuggestions: function() {
		var $showSuggestions = !this._locationSuggestionsButton.hasClass('active');
		if ($showSuggestions) {
			this._loadSuggestions = true;
		}
		
		this.showSuggestions($showSuggestions);
	},
	
	/**
	 * @see	WCF.Location.GoogleMaps.Map.addMarker()
	 */
	addMarker: function(latitude, longitude, title, icon, information) {
		var $infoWindow = $(information);
		var $useLocation = $('<a class="googleMapsUseLocationSuggestionLink" />').text(WCF.Language.get('wcf.map.useLocationSuggestion')).click(this._suggestionSelectionCallback);
		$infoWindow.append($('<p />').append($useLocation));
		
		var $marker = this._super(latitude, longitude, title, '//mt.google.com/vt/icon/name=icons/spotlight/spotlight-waypoint-a.png', $infoWindow.get(0));
		
		$useLocation.data('marker', $marker);
		
		return $marker;
	},
	
	/**
	 * Sets the function called when a location is selected.
	 * 
	 * @param	function		callback
	 */
	setSuggestionSelectionCallback: function(callback) {
		this._suggestionSelectionCallback = callback;
	},
	
	/**
	 * Shows or hides the location suggestions.
	 * 
	 * @param	boolean		showSuggestions
	 */
	showSuggestions: function(showSuggestions) {
		// missing argument means showing the suggestions
		if (showSuggestions === undefined) showSuggestions = true;
		
		this._locationSuggestionsButton.toggleClass('active', showSuggestions);
		
		var $clusterMarkers = [ ];
		for (var $i = 0, $length = this._markers.length; $i < $length; $i++) {
			var $marker = this._markers[$i];
			
			// ignore draggable markers
			if (!$marker.draggable) {
				$marker.setVisible(showSuggestions);
				if (showSuggestions) {
					$clusterMarkers.push($marker);
				}
			}
		}
		
		this._markerClusterer.clearMarkers();
		if (showSuggestions) {
			this._markerClusterer.addMarkers($clusterMarkers);
		}
		
		this._loadMarkers();
	}
});

/**
 * Provides location searches based on google.maps.Geocoder.
 */
WCF.Location.GoogleMaps.LocationSearch = WCF.Search.Base.extend({
	/**
	 * Google Maps geocoder object
	 * @var	google.maps.Geocoder
	 */
	_geocoder: null,
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function(searchInput, callback, excludedSearchValues, commaSeperated, showLoadingOverlay) {
		this._super(searchInput, callback, excludedSearchValues, commaSeperated, showLoadingOverlay);
		
		this.setDelay(500);
		this._geocoder = new google.maps.Geocoder();
	},
	
	/**
	 * @see	WCF.Search.Base._createListItem()
	 */
	_createListItem: function(geocoderResult) {
		var $listItem = $('<li><span>' + WCF.String.escapeHTML(geocoderResult.formatted_address) + '</span></li>').appendTo(this._list);
		$listItem.data('location', geocoderResult.geometry.location).data('label', geocoderResult.formatted_address).click($.proxy(this._executeCallback, this));
		
		this._itemCount++;
		
		return $listItem;
	},
	
	/**
	 * @see	WCF.Search.Base._keyUp()
	 */
	_keyUp: function(event) {
		// handle arrow keys and return key
		switch (event.which) {
			case $.ui.keyCode.LEFT:
			case $.ui.keyCode.RIGHT:
				return;
			break;
			
			case $.ui.keyCode.UP:
				this._selectPreviousItem();
				return;
			break;
			
			case $.ui.keyCode.DOWN:
				this._selectNextItem();
				return;
			break;
			
			case $.ui.keyCode.ENTER:
				return this._selectElement(event);
			break;
		}
		
		var $content = this._getSearchString(event);
		if ($content === '') {
			this._clearList(true);
		}
		else if ($content.length >= this._triggerLength) {
			if (this._delay) {
				if (this._timer !== null) {
					this._timer.stop();
				}
				
				this._timer = new WCF.PeriodicalExecuter($.proxy(function() {
					this._geocoder.geocode({
						address: $content
					}, $.proxy(this._success, this));
					
					this._timer.stop();
					this._timer = null;
				}, this), this._delay);
			}
			else {
				this._geocoder.geocode({
					address: $content
				}, $.proxy(this._success, this));
			}
		}
		else {
			// input below trigger length
			this._clearList(false);
		}
	},
	
	/**
	 * Handles a successfull geocoder request.
	 * 
	 * @param	array		results
	 * @param	integer		status
	 */
	_success: function(results, status) {
		this._clearList(false);
		
		if (status != google.maps.GeocoderStatus.OK) {
			return;
		}
		
		if ($.getLength(results)) {
			var $count = 0;
			for (var $index in results) {
				this._createListItem(results[$index]);
				
				if (++$count == 10) {
					break;
				}
			}
		}
		else if (!this._handleEmptyResult()) {
			return;
		}
		
		WCF.CloseOverlayHandler.addCallback('WCF.Search.Base', $.proxy(function() { this._clearList(); }, this));
		
		var $containerID = this._searchInput.parents('.dropdown').wcfIdentify();
		if (!WCF.Dropdown.getDropdownMenu($containerID).hasClass('dropdownOpen')) {
			WCF.Dropdown.toggleDropdown($containerID);
		}
		
		// pre-select first item
		this._itemIndex = -1;
		if (!WCF.Dropdown.getDropdown($containerID).data('disableAutoFocus')) {
			this._selectNextItem();
		}
	}
});

/**
 * Handles setting a single location on a Google Map.
 */
WCF.Location.GoogleMaps.LocationInput = Class.extend({
	/**
	 * location search object
	 * @var	WCF.Location.GoogleMaps.LocationSearch
	 */
	_locationSearch: null,
	
	/**
	 * related map object
	 * @var	WCF.Location.GoogleMaps.Map
	 */
	_map: null,
	
	/**
	 * draggable marker to set the location
	 * @var	google.maps.Marker
	 */
	_marker: null,
	
	/**
	 * Initializes a new WCF.Location.GoogleMaps.LocationInput object.
	 * 
	 * @param	string		mapContainerID
	 * @param	object		mapOptions
	 * @param	string		searchInput
	 * @param	float		latitude
	 * @param	float		longitude
	 * @param	string		actionClassName
	 */
	init: function(mapContainerID, mapOptions, searchInput, latitude, longitude, actionClassName) {
		this._searchInput = searchInput;
		
		if (actionClassName) {
			this._map = new WCF.Location.GoogleMaps.SuggestionMap(mapContainerID, mapOptions, actionClassName);
			this._map.setSuggestionSelectionCallback($.proxy(this._useSuggestion, this));
		}
		else {
			this._map = new WCF.Location.GoogleMaps.Map(mapContainerID, mapOptions);
		}
		
		this._locationSearch = new WCF.Location.GoogleMaps.LocationSearch(searchInput, $.proxy(this._setMarkerByLocation, this));
		
		if (latitude && longitude) {
			this._marker = this._map.addDraggableMarker(latitude, longitude);
		}
		else {
			this._marker = this._map.addDraggableMarker(WCF.Location.GoogleMaps.Settings.get('defaultLatitude'), WCF.Location.GoogleMaps.Settings.get('defaultLongitude'));
			
			WCF.Location.Util.getLocation($.proxy(function(latitude, longitude) {
				if (latitude !== undefined && longitude !== undefined) {
					WCF.Location.GoogleMaps.Util.moveMarker(this._marker, latitude, longitude);
					WCF.Location.GoogleMaps.Util.focusMarker(this._marker);
				}
			}, this));
		}
		
		this._marker.addListener('dragend', $.proxy(this._updateLocation, this));
	},
	
	/**
	 * Uses a suggestion by clicking on the "Use suggestion" link in the marker's
	 * info window.
	 * 
	 * @param	Event		event
	 */
	_useSuggestion: function(event) {
		var $marker = $(event.currentTarget).data('marker');
		
		this._marker.setPosition($marker.getPosition());
		this._updateLocation();
		
		// hide suggestions
		this._map.showSuggestions(false);
	},
	
	/**
	 * Updates location on marker position change.
	 */
	_updateLocation: function() {
		WCF.Location.GoogleMaps.Util.reverseGeocoding($.proxy(function(result) {
			if (result !== null) {
				$(this._searchInput).val(result);
			}
		}, this), this._marker);
	},
	
	/**
	 * Sets the marker based on an entered location.
	 * 
	 * @param	object		data
	 */
	_setMarkerByLocation: function(data) {
		this._marker.setPosition(data.location);
		WCF.Location.GoogleMaps.Util.focusMarker(this._marker);
		
		$(this._searchInput).val(data.label);
	},
	
	/**
	 * Returns the related map.
	 * 
	 * @return	WCF.Location.GoogleMaps.Map
	 */
	getMap: function() {
		return this._map;
	},
	
	/**
	 * Returns the draggable marker used to set the location.
	 * 
	 * @return	google.maps.Marker
	 */
	getMarker: function() {
		return this._marker;
	}
});

/**
 * Provides utility functions for Google Maps maps.
 */
WCF.Location.GoogleMaps.Util = {
	/**
	 * geocoder instance
	 * @var	google.maps.Geocoder
	 */
	_geocoder: null,
	
	/**
	 * Focuses the given marker's map on the marker.
	 * 
	 * @param	google.maps.Marker	marker
	 */
	focusMarker: function(marker) {
		marker.getMap().setCenter(marker.getPosition());
	},
	
	/**
	 * Returns the latitude and longitude of the given marker.
	 * 
	 * @return	object
	 */
	getMarkerPosition: function(marker) {
		return {
			latitude: marker.getPosition().lat(),
			longitude: marker.getPosition().lng()
		};
	},
	
	/**
	 * Moves the given marker to the given position.
	 * 
	 * @param	google.maps.Marker		marker
	 * @param	float				latitude
	 * @param	float				longitude
	 * @param	boolean				dragend		indicates if "dragend" event is fired
	 */
	moveMarker: function(marker, latitude, longitude, triggerDragend) {
		marker.setPosition(new google.maps.LatLng(latitude, longitude));
		
		if (triggerDragend) {
			google.maps.event.trigger(marker, 'dragend');
		}
	},
	
	/**
	 * Performs a reverse geocoding request.
	 * 
	 * @param	object			callback
	 * @param	google.maps.Marker	marker
	 * @param	string			latitude
	 * @param	string			longitude
	 * @param	boolean			fullResult
	 */
	reverseGeocoding: function(callback, marker, latitude, longitude, fullResult) {
		if (marker) {
			latitude = marker.getPosition().lat();
			longitude = marker.getPosition().lng();
		}
		
		if (this._geocoder === null) {
			this._geocoder = new google.maps.Geocoder();
		}
		
		var $latLng = new google.maps.LatLng(latitude, longitude);
		this._geocoder.geocode({ latLng: $latLng }, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				callback((fullResult ? results : results[0].formatted_address));
			}
			else {
				callback(null);
			}
		});
	}
};
