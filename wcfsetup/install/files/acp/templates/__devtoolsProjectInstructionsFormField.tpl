<div class="row rowColGap formGrid">
	<dl class="col-xs-12 col-md-10">
		<dt></dt>
		<dd>
			<select id="{@$field->getPrefixedId()}_instructionsType">
				<option value="" selected disabled>{lang}wcf.acp.devtools.project.instructions.type{/lang}</option>
				<option value="install">{lang}wcf.acp.devtools.project.instructions.type.install{/lang}</option>
				<option value="update">{lang}wcf.acp.devtools.project.instructions.type.update{/lang}</option>
			</select>
		</dd>
	</dl>
	
	<dl class="col-xs-12 col-md-5" style="display: none;">
		<dt></dt>
		<dd>
			<input type="text" id="{@$field->getPrefixedId()}_fromVersion" class="long" placeholder="{lang}wcf.acp.devtools.project.instructions.update.fromVersion{/lang}">
			<small>{lang}wcf.acp.devtools.project.instructions.update.fromVersion.description{/lang}</small>
		</dd>
	</dl>
	
	<dl class="col-xs-12 col-md-2 text-right">
		<dt></dt>
		<dd>
			<a href="#" class="button small" id="{@$field->getPrefixedId()}_addButton">{lang}wcf.global.button.add{/lang}</a>
		</dd>
	</dl>
</div>

<ul id="{@$field->getPrefixedId()}_instructionsList"></ul>

{capture assign='instructionsTemplate'}
	<header class="sectionHeader">
		<h2 class="sectionTitle">
			{literal}{if $type === 'update'}{/literal}
				<span class="icon icon16 fa-pencil pointer jsTooltip" id="{@$field->getPrefixedId()}_instructions{literal}{$instructionsId}{/literal}_editButton" title="{lang}wcf.global.button.edit{/lang}"></span>
				<span class="icon icon16 fa-times pointer jsTooltip" id="{@$field->getPrefixedId()}_instructions{literal}{$instructionsId}{/literal}_deleteButton" title="{lang}wcf.global.button.delete{/lang}"></span>
			{literal}{/if}{/literal}
			{literal}<span class="jsInstructionsTitle">{$sectionTitle}</span>{/literal}
		</h2>
		<p class="sectionDescription">{lang}wcf.acp.devtools.project.instructions.instructions.description{/lang}</p>
	</header>
	
	<div id="{@$field->getPrefixedId()}_instructions{literal}{$instructionsId}{/literal}_instructionListContainer">
		<ol class="sortableList nativeList" id="{@$field->getPrefixedId()}_instructions{literal}{$instructionsId}{/literal}_instructionList"></ol>
	</div>
	
	<div class="row rowColGap formGrid">
		<dl class="col-xs-12 col-md-3">
			<dt></dt>
			<dd>
				<select id="{@$field->getPrefixedId()}_instructions{literal}{$instructionsId}{/literal}_pip">
					<option value="" selected disabled>{lang}wcf.acp.devtools.project.instruction.packageInstallationPlugin{/lang}</option>
					{foreach from=$packageInstallationPlugins item=packageInstallationPlugin}
						<option value="{@$packageInstallationPlugin->pluginName}">{@$packageInstallationPlugin->pluginName}</option>
					{/foreach}
				</select>
			</dd>
		</dl>
		
		<dl class="col-xs-12 col-md-9">
			<dt></dt>
			<dd>
				<input type="text" id="{@$field->getPrefixedId()}_instructions{literal}{$instructionsId}{/literal}_value" class="long" placeholder="{lang}wcf.acp.devtools.project.instruction.value{/lang}">
				<small id="{@$field->getPrefixedId()}_instructions{literal}{$instructionsId}{/literal}_valueDescription">{lang}wcf.acp.devtools.project.instruction.value.description{/lang}</small>
			</dd>
		</dl>
		
		<dl class="col-xs-12 col-md-2" style="display: none;">
			<dt></dt>
			<dd>
				<select id="{@$field->getPrefixedId()}_instructions{literal}{$instructionsId}{/literal}_application">
					<option value="" selected disabled>{lang}wcf.acp.devtools.project.instruction.application{/lang}</option>
					{foreach from=$apps item=app}
						<option value="{@$app->getAbbreviation()}">{@$app->getAbbreviation()}</option>
					{/foreach}
				</select>
			</dd>
		</dl>
		
		<dl class="col-xs-12 col-md-10">
			<dt></dt>
			<dd>
				<label><input type="checkbox" id="{@$field->getPrefixedId()}_instructions{literal}{$instructionsId}{/literal}_runStandalone" value="1"> {lang}wcf.acp.devtools.project.instruction.runStandalone{/lang}</label>
				<small>{lang}wcf.acp.devtools.project.instruction.runStandalone.description{/lang}</small>
			</dd>
		</dl>
		
		<dl class="col-xs-12 col-md-2 text-right">
			<dt></dt>
			<dd>
				<a href="#" class="button small" id="{@$field->getPrefixedId()}_instructions{literal}{$instructionsId}{/literal}_addButton">{lang}wcf.global.button.add{/lang}</a>
			</dd>
		</dl>
	</div>
{/capture}

{capture assign='instructionsEditDialogContent'}
	<div class="section">
		<dl>
			<dt>{lang}wcf.acp.devtools.project.instructions.update.fromVersion{/lang}</dt>
			<dd>
				<input type="text" name="fromVersion" class="long" value="{literal}{$fromVersion}{/literal}" autofocus />
				<small>{lang}wcf.acp.devtools.project.instructions.update.fromVersion.description{/lang}</small>
			</dd>
		</dl>
	</div>
	
	<div class="formSubmit">
		<button data-type="submit" class="buttonPrimary">{lang}wcf.global.button.submit{/lang}</button>
	</div>
{/capture}

{capture assign='instructionEditDialogContent'}
	<div class="section">
		<dl>
			<dt>{lang}wcf.acp.devtools.project.instruction.packageInstallationPlugin{/lang}</dt>
			<dd>
				<select name="pip">
					<option value="" selected>{lang}wcf.global.noSelection{/lang}</option>
					{foreach from=$packageInstallationPlugins item=packageInstallationPlugin}
						<option value="{@$packageInstallationPlugin->pluginName}">{@$packageInstallationPlugin->pluginName}</option>
					{/foreach}
				</select>
			</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.devtools.project.instruction.value{/lang}</dt>
			<dd>
				<input type="text" name="value" class="long" value="{literal}{$value}{/literal}">
				<small class="jsInstructionValueDescription">{lang}wcf.acp.devtools.project.instruction.value.description{/lang}</small>
			</dd>
		</dl>
		
		<dl style="display: none;">
			<dt>{lang}wcf.acp.devtools.project.instruction.application{/lang}</dt>
			<dd>
				<select name="application">
					<option value="" selected>{lang}wcf.global.noSelection{/lang}</option>
					{foreach from=$apps item=app}
						<option value="{@$app->getAbbreviation()}">{@$app->getAbbreviation()}</option>
					{/foreach}
				</select>
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="runStandalone" value="1"{literal}{if $runStandalone} checked{/if}{/literal}> {lang}wcf.acp.devtools.project.instruction.runStandalone{/lang}</label>
				<small>{lang}wcf.acp.devtools.project.instruction.runStandalone.description{/lang}</small>
			</dd>
		</dl>
	</div>
	
	<div class="formSubmit">
		<button data-type="submit" class="buttonPrimary">{lang}wcf.global.button.submit{/lang}</button>
	</div>
{/capture}

<script data-relocate="true">
	require([
		'Language',
		'WoltLabSuite/Core/Acp/Form/Builder/Field/Devtools/Project/Instructions',
		'WoltLabSuite/Core/Template'
	], function(
		Language,
		InstructionsFormField,
		Template
	) {
		Language.addObject({
			'wcf.acp.devtools.project.instruction.delete.confirmMessages': '{lang}wcf.acp.devtools.project.instruction.delete.confirmMessages{/lang}',
			'wcf.acp.devtools.project.instruction.edit': '{lang}wcf.acp.devtools.project.instruction.edit{/lang}',
			'wcf.acp.devtools.project.instruction.instruction': '{capture assign=__languageItem}{lang __literal=true}wcf.acp.devtools.project.instruction.instruction{/lang}{/capture}{@$__languageItem|encodeJS}',
			'wcf.acp.devtools.project.instruction.value.description': '{lang}wcf.acp.devtools.project.instruction.value.description{/lang}',
			'wcf.acp.devtools.project.instruction.value.description.defaultFilename': '{lang __literal=true}wcf.acp.devtools.project.instruction.value.description.defaultFilename{/lang}',
			'wcf.acp.devtools.project.instructions.delete.confirmMessages': '{lang}wcf.acp.devtools.project.instructions.delete.confirmMessages{/lang}',
			'wcf.acp.devtools.project.instructions.edit': '{lang}wcf.acp.devtools.project.instructions.edit{/lang}',
			'wcf.acp.devtools.project.instructions.instructions.description': '{lang}wcf.acp.devtools.project.instructions.instructions.description{/lang}',
			'wcf.acp.devtools.project.instructions.type.install.title': '{lang}wcf.acp.devtools.project.instructions.type.install.title{/lang}',
			'wcf.acp.devtools.project.instructions.type.update.error.duplicate': '{lang}wcf.acp.devtools.project.instructions.type.update.error.duplicate{/lang}',
			'wcf.acp.devtools.project.instructions.type.update.title': '{lang __literal=true}wcf.acp.devtools.project.instructions.type.update.title{/lang}',
			'wcf.global.form.error.noValidSelection': '{lang}wcf.global.form.error.noValidSelection{/lang}'
		});
		
		var instructionsTemplate = new Template('{@$instructionsTemplate|encodeJS}');
		var instructionsEditDialogTemplate = new Template('{@$instructionsEditDialogContent|encodeJS}');
		var instructionEditDialogTemplate = new Template('{@$instructionEditDialogContent|encodeJS}');
		
		new InstructionsFormField(
			'{@$field->getPrefixedId()}',
			instructionsTemplate,
			instructionsEditDialogTemplate,
			instructionEditDialogTemplate,
			{
				{implode from=$packageInstallationPlugins item=packageInstallationPlugin}
					'{$packageInstallationPlugin->pluginName}': '{$packageInstallationPlugin->getDefaultFilename()}'
				{/implode}
			},
			[
				{implode from=$field->getValue() key=instructionsKey item=instructions}
					{
						errors: [
							{assign var='__instructionsHasError' value=false}
							{foreach from=$field->getValidationErrors() item=validationError}
								{if $validationError->getInformation()[instructions]|isset && $validationError->getInformation()[instructions] === $instructionsKey && !$validationError->getInformation()[instruction]|isset}
									{if $__instructionsHasError},{/if}
									'{@$validationError->getMessage()|encodeJS}'
									
									{assign var='__instructionsHasError' value=true}
								{/if}
							{/foreach}
						],
						{if $instructions[type] === 'update'}
							fromVersion: '{$instructions[fromVersion]}',
						{/if}
						instructions: [
							{if !$instructions[instructions]|empty}
								{implode from=$instructions[instructions] key=instructionKey item=instruction}
									{
										application: '{if $instruction[application]|isset}{$instruction[application]}{/if}',
										errors: [
											{assign var='__instructionHasError' value=false}
											{foreach from=$field->getValidationErrors() item=validationError}
												{if $validationError->getInformation()[instructions]|isset && $validationError->getInformation()[instructions] === $instructionsKey && $validationError->getInformation()[instruction]|isset && $validationError->getInformation()[instruction] === $instructionKey}
													{if $__instructionHasError},{/if}
													'{@$validationError->getMessage()|encodeJS}'
													
													{assign var='__instructionHasError' value=true}
												{/if}
											{/foreach}
										],
										pip: '{$instruction[pip]}',
										runStandalone: {$instruction[runStandalone]|intval},
										value: '{$instruction[value]|encodeJS}'
									}
								{/implode}
							{/if}
						],
						type: '{@$instructions[type]}'
					}
				{/implode}
			]
		);
	});
</script>
