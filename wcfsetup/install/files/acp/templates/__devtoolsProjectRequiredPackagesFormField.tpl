<ol class="nativeList" id="{@$field->getPrefixedId()}_packageList"></ol>

{include file='__formFieldErrors'}

<div class="row rowColGap formGrid">
	<dl class="col-xs-12 col-md-4">
		<dt></dt>
		<dd>
			<input type="text" id="{@$field->getPrefixedId()}_packageIdentifier" class="long" placeholder="{lang}wcf.acp.devtools.project.packageIdentifier{/lang}">
		</dd>
	</dl>
	
	<dl class="col-xs-12 col-md-2">
		<dt></dt>
		<dd>
			<input type="text" id="{@$field->getPrefixedId()}_minVersion" class="long" placeholder="{lang}wcf.acp.devtools.project.requiredPackage.minVersion{/lang}">
		</dd>
	</dl>
	
	<dl class="col-xs-12 col-md-5">
		<dt></dt>
		<dd>
			<label><input type="checkbox" id="{@$field->getPrefixedId()}_file" value="1"> {lang}wcf.acp.devtools.project.requiredPackage.file{/lang}</label>
			<small>{lang}wcf.acp.devtools.project.requiredPackage.file.description{/lang}</small>
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
	require(['Language', 'WoltLabSuite/Core/Form/Builder/Field/Devtools/Project/RequiredPackages'], function(Language, RequiredPackagesFormField) {
		Language.addObject({
			'wcf.acp.devtools.project.packageIdentifier.error.duplicate': '{lang}wcf.acp.devtools.project.packageIdentifier.error.duplicate{/lang}',
			'wcf.acp.devtools.project.packageIdentifier.error.format': '{lang}wcf.acp.devtools.project.packageIdentifier.error.format{/lang}',
			'wcf.acp.devtools.project.packageIdentifier.error.maximumLength': '{lang}wcf.acp.devtools.project.packageIdentifier.error.maximumLength{/lang}',
			'wcf.acp.devtools.project.packageIdentifier.error.minimumLength': '{lang}wcf.acp.devtools.project.packageIdentifier.error.minimumLength{/lang}',
			'wcf.acp.devtools.project.packageVersion.error.format': '{lang}wcf.acp.devtools.project.packageVersion.error.format{/lang}',
			'wcf.acp.devtools.project.packageVersion.error.maximumLength': '{lang}wcf.acp.devtools.project.packageVersion.error.maximumLength{/lang}',
			'wcf.acp.devtools.project.requiredPackage.requiredPackage': '{capture assign=__languageItem}{lang __literal=true}wcf.acp.devtools.project.requiredPackage.requiredPackage{/lang}{/capture}{@$__languageItem|encodeJS}'
		});
		
		new RequiredPackagesFormField('{@$field->getPrefixedId()}', [
			{implode from=$field->getValue() item=requiredPackage}
				{
					file: '{$requiredPackage[file]}',
					minVersion: '{$requiredPackage[minVersion]}',
					packageIdentifier: '{$requiredPackage[packageIdentifier]}'
				}
			{/implode}
		]);
	});
</script>
