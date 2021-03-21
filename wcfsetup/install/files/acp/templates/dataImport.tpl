{include file='header' pageTitle='wcf.acp.dataImport'}

<script data-relocate="true">
	$(function() {
		{if $queue|isset}
			WCF.Language.addObject({
				'wcf.acp.dataImport': '{jslang}wcf.acp.dataImport{/jslang}',
				'wcf.acp.dataImport.completed': '{jslang}wcf.acp.dataImport.completed{/jslang}',
				{implode from=$importers item=importer}'wcf.acp.dataImport.data.{@$importer}': '{jslang}wcf.acp.dataImport.data.{@$importer}{/jslang}'{/implode}
			});
			
			var $queues = [ {implode from=$queue item=item}'{@$item}'{/implode} ];
			new WCF.ACP.Import.Manager($queues, '{link controller='RebuildData' encode=false}{/link}');
		{/if}
		
		$('.jsImportSection').change(function(event) {
			var $section = $(event.currentTarget);
			
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
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.dataImport{/lang}</h1>
		{if $exporterName}
			<p class="contentHeaderDescription">{lang}wcf.acp.dataImport.exporter.{@$exporterName}{/lang}</p>
		{/if}
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='formError'}

{if !$exporterName}
	{if !$availableExporters|count}
		<p class="info">{lang}wcf.acp.dataImport.selectExporter.noExporters{/lang}</p>
	{else}
		{if $showMappingNotice}
			<p class="warning">{lang}wcf.acp.dataImport.existingMapping.notice{/lang}</p>
			<script data-relocate="true">
				require(['Ajax', 'Ui/Confirmation'], (Ajax, UiConfirmation) => {
					document.getElementById('deleteMapping').addEventListener('click', () => {
						UiConfirmation.show({
							confirm() {
								Ajax.apiOnce({
									data: {
										actionName: 'resetMapping',
										className: 'wcf\\system\\importer\\ImportHandler',
									},
									success() {
										window.location.reload();
									},
									url: 'index.php/AJAXInvoke/?t=' + SECURITY_TOKEN,
								});
							},
							message: '{jslang}wcf.acp.dataImport.existingMapping.confirmMessage{/jslang}'
						});
						
						return false;
					});
				});
			</script>
		{/if}
		
		<form method="post" action="{link controller='DataImport'}{/link}">
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.acp.dataImport.selectExporter{/lang}</h2>
				
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
			</section>
			
			<div class="formSubmit">
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
				<input type="hidden" name="sourceSelection" value="1">
				{csrfToken}
			</div>
		</form>
	{/if}
{else}
	<form method="post" action="{link controller='DataImport'}{/link}">
		<section class="section">
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.acp.dataImport.configure.data{/lang}</h2>
				<p class="sectionDescription">{lang}wcf.acp.dataImport.configure.data.description{/lang}</p>
			</header>
			
			{foreach from=$supportedData key=objectTypeName item=objectTypes}
				<dl class="wide">
					<dt></dt>
					<dd class="jsImportCollection">
						<label><input type="checkbox" name="selectedData[]" value="{@$objectTypeName}" class="jsImportSection"{if $objectTypeName|in_array:$selectedData} checked{/if}> {lang}wcf.acp.dataImport.data.{@$objectTypeName}{/lang}</label>
						<p>
							{foreach from=$objectTypes item=objectTypeName}
								<label><input type="checkbox" name="selectedData[]" value="{@$objectTypeName}" class="jsImportItem"{if $objectTypeName|in_array:$selectedData} checked{/if}> {lang}wcf.acp.dataImport.data.{@$objectTypeName}{/lang}</label>
							{/foreach}
						</p>
					</dd>
				</dl>
			{/foreach}
			
			{event name='dataFields'}
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.dataImport.configure.settings{/lang}</h2>
			
			<dl>
				<dt><label for="userMergeMode">{lang}wcf.acp.dataImport.configure.settings.userMergeMode{/lang}</label></dt>
				<dd>
					<label><input type="radio" id="userMergeMode" name="userMergeMode" value="4"{if $userMergeMode == 4} checked{/if}> {lang}wcf.acp.dataImport.configure.settings.userMergeMode.4{/lang}</label>
					<label><input type="radio" name="userMergeMode" value="5"{if $userMergeMode == 5} checked{/if}> {lang}wcf.acp.dataImport.configure.settings.userMergeMode.5{/lang}</label>
				</dd>
			</dl>
			
			{event name='settingFields'}
		</section>
		
		<section class="section{if $errorField == 'database'} formError{/if}">
			<h2 class="sectionTitle">{lang}wcf.acp.dataImport.configure.database{/lang}</h2>
			
			<dl>
				<dt><label for="dbHost">{lang}wcf.acp.dataImport.configure.database.host{/lang}</label></dt>
				<dd>
					<input type="text" id="dbHost" name="dbHost" value="{$dbHost}" class="long">
				</dd>
			</dl>
			
			<dl>
				<dt><label for="dbUser">{lang}wcf.acp.dataImport.configure.database.user{/lang}</label></dt>
				<dd>
					<input type="text" id="dbUser" name="dbUser" value="{$dbUser}" class="medium">
				</dd>
			</dl>
			
			<dl>
				<dt><label for="dbPassword">{lang}wcf.acp.dataImport.configure.database.password{/lang}</label></dt>
				<dd>
					<input type="password" id="dbPassword" name="dbPassword" value="{$dbPassword}" class="medium" autocomplete="off">
				</dd>
			</dl>
			
			<dl>
				<dt><label for="dbName">{lang}wcf.acp.dataImport.configure.database.name{/lang}</label></dt>
				<dd>
					<input type="text" id="dbName" name="dbName" value="{$dbName}" class="medium">
				</dd>
			</dl>
			
			<dl>
				<dt><label for="dbPrefix">{lang}wcf.acp.dataImport.configure.database.prefix{/lang}</label></dt>
				<dd>
					<input type="text" id="dbPrefix" name="dbPrefix" value="{$dbPrefix}" class="short">
					{if $errorField == 'database'}
						<small class="innerError">{lang}wcf.acp.dataImport.configure.database.error{/lang}</small>
					{/if}
				</dd>
			</dl>
			
			{event name='databaseFields'}
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.dataImport.configure.fileSystem{/lang}</h2>
			
			<dl{if $errorField == 'fileSystemPath'} class="formError"{/if}>
				<dt><label for="fileSystemPath">{lang}wcf.acp.dataImport.configure.fileSystem.path{/lang}</label></dt>
				<dd>
					<input type="text" id="fileSystemPath" name="fileSystemPath" value="{$fileSystemPath}" class="long">
					{if $errorField == 'fileSystemPath'}
						<small class="innerError">{lang}wcf.acp.dataImport.configure.fileSystem.path.error{/lang}</small>
					{/if}
					<small>{lang}wcf.acp.dataImport.configure.fileSystem.path.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='fileSystemFields'}
		</section>
		
		{event name='sections'}
		
		<div class="formSubmit">
			<input type="hidden" name="exporterName" value="{$exporterName}">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</form>
{/if}

{include file='footer'}
