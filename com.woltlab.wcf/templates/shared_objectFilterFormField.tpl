<div id="{$field->getPrefixedId()}" class="objectFilter__container"></div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/Object/Filter/Builder"], ({ setup }) => {
		{jsphrase name='wcf.objectFilter.addFilter'}
		{jsphrase name='wcf.objectFilter.joiner'}
		setup(
			document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}'),
			'{unsafe:$field->getEndpoint()|encodeJS}',
			{unsafe:$field->toJson()},
		);
	});
</script>
