<ol class="nativeList" id="{$field->getPrefixedId()}_packageList"></ol>

{include file='shared_formFieldErrors'}

<div class="row rowColGap formGrid">
	<dl class="col-xs-12 col-md-11">
		<dt></dt>
		<dd>
			<input type="text" id="{$field->getPrefixedId()}_packageIdentifier" class="long" placeholder="{lang}wcf.acp.devtools.project.packageIdentifier{/lang}">
			<small>{lang}wcf.acp.devtools.project.optionalPackage.packageIdentifier.description{/lang}</small>
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
	require(['Language', 'WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/OptionalPackages'], function(Language, OptionalPackagesFormField) {
		Language.addObject({
			'wcf.acp.devtools.project.packageIdentifier.error.duplicate': '{jslang}wcf.acp.devtools.project.packageIdentifier.error.duplicate{/jslang}',
			'wcf.acp.devtools.project.packageIdentifier.error.format': '{jslang}wcf.acp.devtools.project.packageIdentifier.error.format{/jslang}',
			'wcf.acp.devtools.project.packageIdentifier.error.maximumLength': '{jslang}wcf.acp.devtools.project.packageIdentifier.error.maximumLength{/jslang}',
			'wcf.acp.devtools.project.packageIdentifier.error.minimumLength': '{jslang}wcf.acp.devtools.project.packageIdentifier.error.minimumLength{/jslang}',
			'wcf.acp.devtools.project.packageVersion.error.format': '{jslang}wcf.acp.devtools.project.packageVersion.error.format{/jslang}',
			'wcf.acp.devtools.project.packageVersion.error.maximumLength': '{jslang}wcf.acp.devtools.project.packageVersion.error.maximumLength{/jslang}',
			'wcf.acp.devtools.project.optionalPackage.optionalPackage': '{jslang __literal=true}wcf.acp.devtools.project.optionalPackage.optionalPackage{/jslang}'
		});
		
		new OptionalPackagesFormField('{@$field->getPrefixedId()|encodeJS}', [
			{implode from=$field->getValue() item=optionalPackage}
			{
				packageIdentifier: '{$optionalPackage[packageIdentifier]}'
			}
			{/implode}
		]);
	});
</script>
