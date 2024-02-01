{include file='header' pageTitle='wcf.acp.template.group.'|concat:$action}

{if $action === 'edit'}
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/Template/Group/Copy'], function (Language, AcpUiTemplateGroupCopy) {
			Language.addObject({
				'wcf.acp.template.group.copy': '{jslang}wcf.acp.template.group.copy{/jslang}',
				'wcf.acp.template.group.name.error.notUnique': '{jslang}wcf.acp.template.group.name.error.notUnique{/jslang}',
				'wcf.acp.template.group.folderName': '{jslang}wcf.acp.template.group.folderName{/jslang}',
				'wcf.acp.template.group.folderName.error.invalid': '{jslang}wcf.acp.template.group.folderName.error.invalid{/jslang}',
				'wcf.acp.template.group.folderName.error.notUnique': '{jslang}wcf.acp.template.group.folderName.error.notUnique{/jslang}',
				'wcf.global.name': '{jslang}wcf.global.name{/jslang}'
			});
			
			AcpUiTemplateGroupCopy.init({$templateGroupID});
		});
	</script>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.template.group.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action === 'edit'}<li><a href="#" class="jsButtonCopy button">{icon name='copy'} <span>{lang}wcf.acp.template.group.copy{/lang}</span></a></li>{/if}
			<li><a href="{link controller='TemplateGroupList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.template.group.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='TemplateGroupAdd'}{/link}{else}{link controller='TemplateGroupEdit' id=$templateGroupID}{/link}{/if}">
	<div class="section">
		{if $availableTemplateGroups|count}
			<dl>
				<dt><label for="parentTemplateGroupID">{lang}wcf.acp.template.group.parentTemplateGroup{/lang}</label></dt>
				<dd>
					<select name="parentTemplateGroupID" id="parentTemplateGroupID">
						<option value="0">{lang}wcf.acp.template.group.default{/lang}</option>
						{htmlOptions options=$availableTemplateGroups selected=$parentTemplateGroupID disableEncoding=true}
					</select>
					{if $errorField == 'parentTemplateGroupID'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.template.group.parentTemplateGroupID.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}
		
		<dl{if $errorField == 'templateGroupName'} class="formError"{/if}>
			<dt><label for="templateGroupName">{lang}wcf.global.name{/lang}</label></dt>
			<dd>
				<input type="text" id="templateGroupName" name="templateGroupName" value="{$templateGroupName}" required class="long">
				{if $errorField == 'templateGroupName'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.template.group.name.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'templateGroupFolderName'} class="formError"{/if}>
			<dt><label for="templateGroupFolderName">{lang}wcf.acp.template.group.folderName{/lang}</label></dt>
			<dd>
				<input type="text" id="templateGroupFolderName" name="templateGroupFolderName" value="{$templateGroupFolderName}" required class="long">
				{if $errorField == 'templateGroupFolderName'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.template.group.folderName.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
