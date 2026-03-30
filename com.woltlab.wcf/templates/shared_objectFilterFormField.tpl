<div id="{$field->getPrefixedId()}"></div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/Object/Filter/Builder"], ({ setup }) => {
		setup(
			document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}'),
			'{link controller='ObjectFilterBuilder' forceFrontend=true}{/link}',
			{unsafe:$field->toJson()},
		);
	});
</script>
