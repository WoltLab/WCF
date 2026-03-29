<button type="button" id="{$field->getPrefixedId()}_addFilter" class="button" data-endpoint="{link controller='ObjectFilterBuilder' forceFrontend=true}{/link}">TODO: add object filter</button>
<div id="{$field->getPrefixedId()}"></div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/Object/Filter/Builder"], ({ setup }) => {
		setup(
			document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}'),
			document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}_addFilter')
		);
	});
</script>
