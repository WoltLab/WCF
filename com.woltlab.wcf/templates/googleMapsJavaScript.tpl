<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/markerClusterer{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
<script data-relocate="true" src="http://maps.google.com/maps/api/js?sensor=false&amp;language={@$__wcf->language->getFixedLanguageCode()}"></script>
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Location.GoogleMaps.Settings.set({
			disableDoubleClickZoom: {if GOOGLE_MAPS_ENABLE_DOUBLE_CLICK_ZOOM}0{else}1{/if},
			draggable: {@GOOGLE_MAPS_ENABLE_DRAGGING},
			mapType: '{@GOOGLE_MAPS_TYPE}',
			scaleControl: {@GOOGLE_MAPS_ENABLE_SCALE_CONTROL},
			scrollwheel: {@GOOGLE_MAPS_ENABLE_SCROLL_WHEEL_ZOOM},
			type: '{@GOOGLE_MAPS_TYPE}',
			zoom: {@GOOGLE_MAPS_ZOOM}
		});
		
		{event name='javascriptInit'}
	});
	//]]>
</script>
