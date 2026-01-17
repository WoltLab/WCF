{capture assign='googleMapsElementID'}{$field->getPrefixedId()}_map{/capture}

<input
	type="text"
	id="{$field->getPrefixedId()}"
	name="{$field->getPrefixedId()}"
	{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}
	value="{$field->getValue()}"
	{if $field->isAutofocused()} autofocus{/if}
	{if $field->isRequired()} required{/if}
	{if $field->isImmutable()} disabled{/if}
	{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}
	{if $field->getDocument()->isAjax()} data-dialog-submit-on-enter="true"{/if}
	{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}
	data-google-maps-geocoding="{$googleMapsElementID}"
	data-google-maps-geocoding-store="{$field->getPrefixedId()}_"
	data-google-maps-marker
>

{include file='shared_googleMapsElement' accessUserLocation=true googleMapsLat=$field->getLatitude() googleMapsLng=$field->getLongitude()}
