{include file='header' pageTitle='wcf.acp.template.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.template.{$action}{/lang}</h1>
		{if $action == 'edit'}<p class="contentHeaderDescription">{$template->getPath()}</p>{/if}
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}<li><a href="{link controller='TemplateDiff' id=$template->templateID}{/link}" class="button">{icon name='right-left'} <span>{lang}wcf.acp.template.diff{/lang}</span></a></li>{/if}
			<li><a href="{link controller='TemplateList'}{if $action == 'edit'}templateGroupID={@$template->templateGroupID}{/if}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.template.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

{if $availableTemplateGroups|count}
	<form method="post" action="{if $action == 'add'}{link controller='TemplateAdd'}{/link}{else}{link controller='TemplateEdit' id=$templateID}{/link}{/if}">
		<div class="section">
			<dl>
				<dt><label for="templateGroupID">{lang}wcf.acp.template.group{/lang}</label></dt>
				<dd>
					<select name="templateGroupID" id="templateGroupID" required>
						<option value="">{lang}wcf.global.noSelection{/lang}</option>
						{htmlOptions options=$availableTemplateGroups selected=$templateGroupID disableEncoding=true}
					</select>
					{if $errorField == 'templateGroupID'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.template.templateGroupID.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'tplName'} class="formError"{/if}>
				<dt><label for="tplName">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="tplName" name="tplName" value="{$tplName}" required class="long">
					{if $errorField == 'tplName'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.template.name.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='dataFields'}
		</div>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.template.source{/lang}</h2>
			
			<dl class="wide">
				<dt><label for="templateSource">{lang}wcf.acp.template.source{/lang}</label></dt>
				<dd dir="ltr">
					<textarea id="templateSource" name="templateSource" cols="40" rows="20">{$templateSource}</textarea>
					{include file='shared_codemirror' codemirrorMode='smarty' codemirrorSelector='#templateSource'}
				</dd>
			</dl>
		</section>
		
		{event name='sections'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{if $copy}<input type="hidden" name="copy" value="{$copy}">{/if}
			{csrfToken}
		</div>
	</form>
{else}
	<woltlab-core-notice type="error">{lang}wcf.acp.template.error.noGroups{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
