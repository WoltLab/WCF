{include file='header' pageTitle="wcf.acp.template.list"}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\template\\TemplateAction', '.jsTemplateRow');
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.template.list{/lang}</h1>
	</hgroup>
</header>

<form method="post" action="{link controller='TemplateList'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.template.list.filter{/lang}</legend>
		
			<dl>
				<dt><label for="templateGroupID">{lang}wcf.acp.template.group{/lang}</label></dt>
				<dd>
					<select name="templateGroupID" id="templateGroupID">
						<option value="0">{lang}wcf.acp.template.group.default{/lang}</option>
						{foreach from=$availableTemplateGroups item=availableTemplateGroup}
							<option value="{@$availableTemplateGroup->templateGroupID}"{if $availableTemplateGroup->templateGroupID == $templateGroupID} selected="selected"{/if}>{$availableTemplateGroup->templateGroupName}</option>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="searchTemplateName">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="searchTemplateName" name="searchTemplateName" value="{$searchTemplateName}" class="long" />
				</dd>
			</dl>
		</fieldset>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>

<div class="contentNavigation">
	{assign var='linkParameters' value=''}
	{if $templateGroupID}{capture append=linkParameters}&templateGroupID={@$templateGroupID}{/capture}{/if}
	{if $searchTemplateName}{capture append=linkParameters}&searchTemplateName={@$searchTemplateName|rawurlencode}{/capture}{/if}
		
	{pages print=true assign=pagesLinks controller="TemplateList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
	
	<nav>
		<ul>
			<li><a href="{link controller='TemplateAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.template.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<hgroup>
			<h1>{lang}wcf.acp.template.list{/lang} <span class="badge badgeInverse jsDataCount">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnTemplateID{if $sortField == 'templateID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='TemplateList'}pageNo={@$pageNo}&sortField=templateID&sortOrder={if $sortField == 'templateID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnTemplateName{if $sortField == 'templateName'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateList'}pageNo={@$pageNo}&sortField=templateName&sortOrder={if $sortField == 'templateName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnDate columnLastModificationTime{if $sortField == 'lastModificationTime'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateList'}pageNo={@$pageNo}&sortField=lastModificationTime&sortOrder={if $sortField == 'lastModificationTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.template.lastModificationTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=template}
					<tr class="jsTemplateRow">
						<td class="columnIcon">
							<a href="{link controller='TemplateAdd'}copy={@$template->templateID}{/link}" title="{lang}wcf.acp.template.copy{/lang}" class="jsTooltip"><span class="icon icon16 icon-copy"></span></a>
							
							{if $template->templateGroupID}
								<a href="{link controller='TemplateEdit' id=$template->templateID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
								<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$template->templateID}" data-confirm-message="{lang}wcf.acp.template.delete.sure{/lang}"></span>
							{else}
								<span class="icon icon16 icon-pencil disabled" title="{lang}wcf.global.button.edit{/lang}"></span>
								<span class="icon icon16 icon-remove disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$template->templateID}</td>
						<td class="columnTitle columnTemplateName">{if $template->packageDir}[{$template->getPackageAbbreviation()}] {/if}{if $template->templateGroupID}<a href="{link controller='TemplateEdit' id=$template->templateID}{/link}">{$template->templateName}</a>{else}{$template->templateName}{/if}</td>
						<td class="columnDate columnLastModificationTime">{@$template->lastModificationTime|time}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a href="{link controller='TemplateAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.template.add{/lang}</span></a></li>
			
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.acp.template.noItems{/lang}</p>
{/if}

{include file='footer'}
