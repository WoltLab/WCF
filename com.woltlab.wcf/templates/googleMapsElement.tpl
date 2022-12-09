{if !MESSAGE_ENABLE_USER_CONSENT || ($__wcf->user->userID && $__wcf->user->getUserOption('enableEmbeddedMedia'))}
    {assign var='googleMapsHidden' value=false}
{else}
    {assign var='googleMapsHidden' value=true}
{/if}

<woltlab-core-google-maps
    id="{$googleMapsElementID}"
    class="googleMap"
    api-key="{GOOGLE_MAPS_API_KEY}"
    zoom="{GOOGLE_MAPS_ZOOM}"
    lat="{GOOGLE_MAPS_DEFAULT_LATITUDE}"
    lng="{GOOGLE_MAPS_DEFAULT_LONGITUDE}"
    {if $googleMapsHidden}hidden{/if}
></woltlab-core-google-maps>

{if $googleMapsHidden}
    {include file='messageUserConsent' host="maps.google.com" url="https://www.google.com/maps/" target=$googleMapsElementID sandbox=true}
{/if}
