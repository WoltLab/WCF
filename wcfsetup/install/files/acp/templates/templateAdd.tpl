{include file='header' pageTitle='wcf.acp.template.'|concat:$action}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.template.{$action}{/lang}</h1>
	{if $action == 'edit'}<p>{$template->getPath()}</p>{/if}
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='TemplateList'}{if $action == 'edit'}templateGroupID={@$template->templateGroupID}{/if}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.template.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

{if $availableTemplateGroups|count}
	<form method="post" action="{if $action == 'add'}{link controller='TemplateAdd'}{/link}{else}{link controller='TemplateEdit' id=$templateID}{/link}{/if}">
		<div class="container containerPadding marginTop">
			<fieldset>
				<legend>{lang}wcf.global.form.data{/lang}</legend>
				
				<dl>
					<dt><label for="templateGroupID">{lang}wcf.acp.template.group{/lang}</label></dt>
					<dd>
						<select name="templateGroupID" id="templateGroupID">
							{foreach from=$availableTemplateGroups item=availableTemplateGroup}
								<option value="{@$availableTemplateGroup->templateGroupID}"{if $availableTemplateGroup->templateGroupID == $templateGroupID} selected="selected"{/if}>{$availableTemplateGroup->templateGroupName}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				
				<dl{if $errorField == 'tplName'} class="formError"{/if}>
					<dt><label for="tplName">{lang}wcf.global.name{/lang}</label></dt>
					<dd>
						<input type="text" id="tplName" name="tplName" value="{$tplName}" required="required" class="long" />
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
			</fieldset>
			
			
			<fieldset>
				<legend><label for="templateSource">{lang}wcf.acp.template.source{/lang}</label></legend>
			
				<dl class="wide">
					<dt><label for="templateSource">{lang}wcf.acp.template.source{/lang}</label></dt>
					<dd>
						<textarea id="templateSource" name="templateSource" cols="40" rows="20">{$templateSource}</textarea>
						{include file='codemirror' codemirrorMode='smarty' codemirrorSelector='#templateSource'}
					</dd>
				</dl>
			</fieldset>
				
			
			{event name='fieldsets'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{if $copy}<input type="hidden" name="copy" value="{@$copy}" />{/if}
		</div>
	</form>
{else}
	<p class="error">{lang}wcf.acp.template.error.noGroups{/lang}</p>
{/if}


{include file='footer'}
