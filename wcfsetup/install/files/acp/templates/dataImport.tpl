{include file='header' pageTitle='wcf.acp.dataImport'}

<script>
	//<![CDATA[
	$(function() {
		{if $queue|isset}
			WCF.Language.addObject({
				'wcf.acp.dataImport': '{lang}wcf.acp.dataImport{/lang}',
				{implode from=$importers item=importer}'wcf.acp.dataImport.data.{@$importer}': '{lang}wcf.acp.dataImport.data.{@$importer}{/lang}'{/implode}
			});
			
			var $queues = [ {implode from=$queue item=item}'{@$item}'{/implode} ];
			new WCF.ACP.Import.Manager($queues);
		{/if}
		
		$('.jsImportSection').change(function(event) {
			var $section = $(event.currentTarget);
			window.dtdesign = $section;
			if ($section.is(':checked')) {
				$section.parent().next().find('input[type=checkbox]').prop('checked', 'checked');
			}
			else {
				$section.parent().next().find('input[type=checkbox]').prop('checked', false);
			}
		});

		$('.jsImportItem').change(function(event) {
			var $item = $(event.currentTarget);
			if ($item.is(':checked')) {
				$item.parents('.jsImportCollection').find('.jsImportSection').prop('checked', 'checked');
			}
			else {
				var $collection = $item.parents('.jsImportCollection');
				var $checkedItems = $collection.find('.jsImportItem:checked');
				if (!$checkedItems.length) {
					$collection.find('.jsImportSection').prop('checked', false);
				}
			}
		});
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.dataImport{/lang}</h1>
	{if $exporterName}
		<p>{lang}wcf.acp.dataImport.exporter.{@$exporterName}{/lang}</p>
	{/if}
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if !$exporterName}
	{if !$availableExporters|count}
		<p class="info">{lang}wcf.acp.dataImport.selectExporter.noExporters{/lang}</p>
	{else}
		<form method="get" action="{link controller='DataImport'}{/link}">
			<div class="container containerPadding marginTop">
				<fieldset>
					<legend>{lang}wcf.acp.dataImport.selectExporter{/lang}</legend>
					
					<dl{if $errorField == 'exporterName'} class="formError"{/if}>
						<dt><label for="exporterName">{lang}wcf.acp.dataImport.exporter{/lang}</label></dt>
						<dd>
							<select name="exporterName" id="exporterName">
								{foreach from=$availableExporters key=availableExporterName item=availableExporter}
									<option value="{@$availableExporterName}">{lang}wcf.acp.dataImport.exporter.{@$availableExporterName}{/lang}</option>
								{/foreach}
							</select>
							{if $errorField == 'exporterName'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{else}
										{lang}wcf.acp.dataImport.exporterName.error.{@$errorType}{/lang}
									{/if}
								</small>
							{/if}
						</dd>
					</dl>
					
					{event name='selectExporterFields'}
				</fieldset>
			</div>
		
			<div class="formSubmit">
				{@SID_INPUT_TAG}
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			</div>
		</form>
	{/if}
{else}
	<form method="post" action="{link controller='DataImport'}{/link}">
		<div class="container containerPadding marginTop">
			<fieldset>
				<legend>{lang}wcf.acp.dataImport.configure.data{/lang}</legend>
				
				<small>{lang}wcf.acp.dataImport.configure.data.description{/lang}</small>
				
				{foreach from=$supportedData key=objectTypeName item=objectTypes}
					<dl class="wide">
						<dd class="jsImportCollection">
							<label><input type="checkbox" name="selectedData[]" value="{@$objectTypeName}" class="jsImportSection"{if $objectTypeName|in_array:$selectedData}checked="checked" {/if}/> {lang}wcf.acp.dataImport.data.{@$objectTypeName}{/lang}</label>
							<p>
								{foreach from=$objectTypes item=objectTypeName}
									<label><input type="checkbox" name="selectedData[]" value="{@$objectTypeName}" class="jsImportItem"{if $objectTypeName|in_array:$selectedData}checked="checked" {/if}/> {lang}wcf.acp.dataImport.data.{@$objectTypeName}{/lang}</label>
								{/foreach}
							</p>
						</dd>
					</dl>
				{/foreach}
			</fieldset>
			
			{*<fieldset>
				<legend>{lang}wcf.acp.dataImport.configure.settings{/lang}</legend>
				
				
			</fieldset>*}
			
			<fieldset{if $errorField == 'database'} class="formError"{/if}>
				<legend>{lang}wcf.acp.dataImport.configure.database{/lang}</legend>
				
				<dl>
					<dt><label for="dbHost">{lang}wcf.acp.dataImport.configure.database.host{/lang}</label></dt>
					<dd>
						<input type="text" id="dbHost" name="dbHost" value="{$dbHost}" class="long" />
					</dd>
				</dl>
				
				<dl>
					<dt><label for="dbUser">{lang}wcf.acp.dataImport.configure.database.user{/lang}</label></dt>
					<dd>
						<input type="text" id="dbUser" name="dbUser" value="{$dbUser}" class="medium" />
					</dd>
				</dl>
				
				<dl>
					<dt><label for="dbPassword">{lang}wcf.acp.dataImport.configure.database.password{/lang}</label></dt>
					<dd>
						<input type="password" id="dbPassword" name="dbPassword" value="{$dbPassword}" class="medium" />
					</dd>
				</dl>
				
				<dl>
					<dt><label for="dbName">{lang}wcf.acp.dataImport.configure.database.name{/lang}</label></dt>
					<dd>
						<input type="text" id="dbName" name="dbName" value="{$dbName}" class="medium" />
					</dd>
				</dl>
				
				<dl>
					<dt><label for="dbPrefix">{lang}wcf.acp.dataImport.configure.database.prefix{/lang}</label></dt>
					<dd>
						<input type="text" id="dbPrefix" name="dbPrefix" value="{$dbPrefix}" class="short" />
						{if $errorField == 'database'}
							<small class="innerError">{lang}wcf.acp.dataImport.configure.database.error{/lang}</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.dataImport.configure.fileSystem{/lang}</legend>
				
				<dl{if $errorField == 'fileSystemPath'} class="formError"{/if}>
					<dt><label for="fileSystemPath">{lang}wcf.acp.dataImport.configure.fileSystem.path{/lang}</label></dt>
					<dd>
						<input type="text" id="fileSystemPath" name="fileSystemPath" value="{$fileSystemPath}" class="long" />
						{if $errorField == 'fileSystemPath'}
							<small class="innerError">{lang}wcf.acp.dataImport.configure.fileSystem.path.error{/lang}</small>
						{/if}
						<small>{lang}wcf.acp.dataImport.configure.fileSystem.path.description{/lang}</small>
					</dd>
				</dl>
			</fieldset>
		</div>
	
		<div class="formSubmit">
			<input type="hidden" name="exporterName" value="{$exporterName}" />
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		</div>
	</form>
{/if}

{include file='footer'}
