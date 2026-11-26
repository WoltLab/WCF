<ol class="nativeList" id="{$field->getPrefixedId()}_packageList"></ol>

<div class="row rowColGap formGrid">
	<dl class="col-xs-12 col-md-11">
		<dt></dt>
		<dd>
			<input type="text" id="{$field->getPrefixedId()}_packageIdentifier" class="long" placeholder="{lang}wcf.acp.devtools.project.packageIdentifier{/lang}">
		</dd>
	</dl>

	<dl class="col-xs-12 col-md-1">
		<dt></dt>
		<dd>
			<a href="#" class="button small" id="{$field->getPrefixedId()}_addButton">{lang}wcf.global.button.add{/lang}</a>
		</dd>
	</dl>
</div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/SuggestedPackages"], ({ SuggestedPackages }) => {
		{jsphrase name='wcf.acp.devtools.project.packageIdentifier.error.duplicate'}
		{jsphrase name='wcf.acp.devtools.project.packageIdentifier.error.format'}
		{jsphrase name='wcf.acp.devtools.project.packageIdentifier.error.maximumLength'}
		{jsphrase name='wcf.acp.devtools.project.packageIdentifier.error.minimumLength'}

		new SuggestedPackages("{unsafe:$field->getPrefixedId()|encodeJS}", [
			{implode from=$field->getValue() item=suggestedPackage}
			{
				packageIdentifier: "{$suggestedPackage[packageIdentifier]}"
			}
			{/implode}
		]);
	});
</script>
