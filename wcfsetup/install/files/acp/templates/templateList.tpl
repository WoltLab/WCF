{include file='header' pageTitle="wcf.acp.template.list"}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\template\\TemplateAction', '.jsTemplateRow');
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
			{if $pages == $pageNo}
				options.updatePageNumber = -1;
			{/if}
		{else}
			options.emptyMessage = '{lang}wcf.global.noItems{/lang}';
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#templateTableContainer'), 'jsTemplateRow', options);
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.template.list{/lang}</h1>
</header>

{include file='formError'}

<form method="post" action="{link controller='TemplateList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap">
			<dl class="col-xs-12 col-md-4">
				<dt><label for="templateGroupID">{lang}wcf.acp.template.group{/lang}</label></dt>
				<dd>
					<select name="templateGroupID" id="templateGroupID">
						<option value="0">{lang}wcf.acp.template.group.default{/lang}</option>
						{htmlOptions options=$availableTemplateGroups selected=$templateGroupID disableEncoding=true}
					</select>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt><label for="application">{lang}wcf.acp.template.application{/lang}</label></dt>
				<dd>
					<select name="application" id="application">
						<option value="">{lang}wcf.acp.template.application.all{/lang}</option>
						{foreach from=$availableApplications key=abbreviation item=availableApplication}
							<option value="{$abbreviation}"{if $abbreviation == $application} selected="selected"{/if}>{$availableApplication}</option>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt><label for="searchTemplateName">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="searchTemplateName" name="searchTemplateName" value="{$searchTemplateName}" class="long" />
				</dd>
			</dl>
			
			{event name='filterFields'}
		</div>
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<div class="contentNavigation">
	{assign var='linkParameters' value=''}
	{if $templateGroupID}{capture append=linkParameters}&templateGroupID={@$templateGroupID}{/capture}{/if}
	{if $searchTemplateName}{capture append=linkParameters}&searchTemplateName={@$searchTemplateName|rawurlencode}{/capture}{/if}
	{if $application}{capture append=linkParameters}&application={$application}{/capture}{/if}
	
	{pages print=true assign=pagesLinks controller="TemplateList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
	
	<nav>
		<ul>
			<li><a href="{link controller='TemplateAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.template.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div id="templateTableContainer" class="section tabularBox">
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
							<a href="{link controller='TemplateAdd'}copy={@$template->templateID}{/link}" title="{lang}wcf.acp.template.copy{/lang}" class="jsTooltip"><span class="icon icon16 fa-files-o"></span></a>
							
							{if $template->templateGroupID}
								<a href="{link controller='TemplateDiff' id=$template->templateID}{/link}" title="{lang}wcf.acp.template.diff{/lang}" class="jsTooltip"><span class="icon icon16 fa-exchange"></span></a>
								<a href="{link controller='TemplateEdit' id=$template->templateID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$template->templateID}" data-confirm-message="{lang}wcf.acp.template.delete.sure{/lang}"></span>
							{else}
								<span class="icon icon16 fa-exchange disabled" title="{lang}wcf.acp.template.diff{/lang}"></span>
								<span class="icon icon16 fa-pencil disabled" title="{lang}wcf.global.button.edit{/lang}"></span>
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$template->templateID}</td>
						<td class="columnTitle columnTemplateName">{if $template->application != 'wcf'}<span class="badge label">{$template->application}</span> {/if}{if $template->templateGroupID}<a href="{link controller='TemplateEdit' id=$template->templateID}{/link}">{$template->templateName}</a>{else}{$template->templateName}{/if}</td>
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
				<li><a href="{link controller='TemplateAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.template.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
