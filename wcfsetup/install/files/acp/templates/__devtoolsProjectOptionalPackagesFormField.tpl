<ol class="nativeList" id="{@$field->getPrefixedId()}_packageList"></ol>

{include file='__formFieldErrors'}

<div class="row rowColGap formGrid">
	<dl class="col-xs-12 col-md-11">
		<dt></dt>
		<dd>
			<input type="text" id="{@$field->getPrefixedId()}_packageIdentifier" class="long" placeholder="{lang}wcf.acp.devtools.project.packageIdentifier{/lang}">
			<small>{lang}wcf.acp.devtools.project.optionalPackage.packageIdentifier.description{/lang}</small>
		</dd>
	</dl>
	
	<dl class="col-xs-12 col-md-1">
		<dt></dt>
		<dd>
			<a href="#" class="button small" id="{@$field->getPrefixedId()}_addButton">{lang}wcf.global.button.add{/lang}</a>
		</dd>
	</dl>
</div>

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/OptionalPackages'], function(Language, OptionalPackagesFormField) {
		Language.addObject({
			'wcf.acp.devtools.project.packageIdentifier.error.duplicate': '{lang}wcf.acp.devtools.project.packageIdentifier.error.duplicate{/lang}',
			'wcf.acp.devtools.project.packageIdentifier.error.format': '{lang}wcf.acp.devtools.project.packageIdentifier.error.format{/lang}',
			'wcf.acp.devtools.project.packageIdentifier.error.maximumLength': '{lang}wcf.acp.devtools.project.packageIdentifier.error.maximumLength{/lang}',
			'wcf.acp.devtools.project.packageIdentifier.error.minimumLength': '{lang}wcf.acp.devtools.project.packageIdentifier.error.minimumLength{/lang}',
			'wcf.acp.devtools.project.packageVersion.error.format': '{lang}wcf.acp.devtools.project.packageVersion.error.format{/lang}',
			'wcf.acp.devtools.project.packageVersion.error.maximumLength': '{lang}wcf.acp.devtools.project.packageVersion.error.maximumLength{/lang}',
			'wcf.acp.devtools.project.optionalPackage.optionalPackage': '{lang __literal=true}wcf.acp.devtools.project.optionalPackage.optionalPackage{/lang}'
		});
		
		new OptionalPackagesFormField('{@$field->getPrefixedId()}', [
			{implode from=$field->getValue() item=optionalPackage}
			{
				packageIdentifier: '{$optionalPackage[packageIdentifier]}'
			}
			{/implode}
		]);
	});
</script>
