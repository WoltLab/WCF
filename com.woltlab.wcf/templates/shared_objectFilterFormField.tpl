<div id="{$field->getPrefixedId()}"></div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/Object/Filter/Builder"], ({ setup }) => {
		{jsphrase name='wcf.objectFilter.addFilter'}
		setup(
			document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}'),
			'{unsafe:$field->getEndpoint()|encodeJS}',
			{unsafe:$field->toJson()},
		);
	});
</script>
