<div class="dropdown" id="{$containerID}">
	<button type="button" class="button dropdownToggle" aria-label="{lang}wcf.global.button.more{/lang}">
		{icon name='ellipsis-vertical'}
	</button>

	<ul class="dropdownMenu">
		{unsafe:$contextMenuOptions}
	</ul>
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/Interaction/StandaloneButton'], ({ StandaloneButton }) => {
		new StandaloneButton(
			document.getElementById('{unsafe:$containerID|encodeJS}'),
			'{unsafe:$providerClassName|encodeJS}',
			'{unsafe:$objectID|encodeJS}',
			'{unsafe:$redirectUrl|encodeJS}'
		);
	});
</script>

{unsafe:$initializationCode}
