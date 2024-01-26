{include file='header' pageTitle="wcf.acp.template.list"}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.template.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='TemplateAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.template.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formError'}

<form method="post" action="{link controller='TemplateList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			{if $availableTemplateGroups|count}
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<select name="templateGroupID" id="templateGroupID">
							<option value="0">{lang}wcf.acp.template.group.default{/lang}</option>
							{htmlOptions options=$availableTemplateGroups selected=$templateGroupID disableEncoding=true}
						</select>
					</dd>
				</dl>
			{/if}
			
			{if $availableApplications|count > 1}
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<select name="application" id="application">
							<option value="">{lang}wcf.acp.template.application{/lang}</option>
							{foreach from=$availableApplications key=abbreviation item=availableApplication}
								<option value="{$abbreviation}"{if $abbreviation == $application} selected{/if}>{$availableApplication}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
			{/if}
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="searchTemplateName" name="searchTemplateName" value="{$searchTemplateName}" placeholder="{lang}wcf.global.name{/lang}" class="long">
				</dd>
			</dl>
			
			{event name='filterFields'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</section>
</form>

{hascontent}
	<div class="paginationTop">
		{content}
			{assign var='linkParameters' value=''}
			{if $templateGroupID}{capture append=linkParameters}&templateGroupID={@$templateGroupID}{/capture}{/if}
			{if $searchTemplateName}{capture append=linkParameters}&searchTemplateName={@$searchTemplateName|rawurlencode}{/capture}{/if}
			{if $application}{capture append=linkParameters}&application={$application}{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller="TemplateList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div id="templateTableContainer" class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\template\TemplateAction">
			<thead>
				<tr>
					<th class="columnID columnTemplateID{if $sortField == 'templateID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='TemplateList'}pageNo={@$pageNo}&sortField=templateID&sortOrder={if $sortField == 'templateID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnTemplateName{if $sortField == 'templateName'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateList'}pageNo={@$pageNo}&sortField=templateName&sortOrder={if $sortField == 'templateName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnDate columnLastModificationTime{if $sortField == 'lastModificationTime'} active {@$sortOrder}{/if}"><a href="{link controller='TemplateList'}pageNo={@$pageNo}&sortField=lastModificationTime&sortOrder={if $sortField == 'lastModificationTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.template.lastModificationTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=template}
					{if $template->canCopy()}
						<tr class="jsTemplateRow jsObjectActionObject" data-object-id="{@$template->getObjectID()}">
							<td class="columnIcon">
								<a href="{link controller='TemplateAdd'}copy={@$template->templateID}{/link}" title="{lang}wcf.acp.template.copy{/lang}" class="jsTooltip">{icon name='copy'}</a>
								
								{if $template->templateGroupID}
									<a href="{link controller='TemplateDiff' id=$template->templateID}{/link}" title="{lang}wcf.acp.template.diff{/lang}" class="jsTooltip">{icon name='right-left'}</a>
									<a href="{link controller='TemplateEdit' id=$template->templateID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip">{icon name='pencil'}</a>
									{objectAction action="delete" objectTitle=$template->templateName}
								{else}
									<span class="disabled" title="{lang}wcf.acp.template.diff{/lang}">
										{icon name='arrow-right-arrow-left'}
									</span>
									<span class="disabled" title="{lang}wcf.global.button.edit{/lang}">
										{icon name='pencil'}
									</span>
									<span class="disabled" title="{lang}wcf.global.button.delete{/lang}">
										{icon name='xmark'}
									</span>
								{/if}
								
								{event name='rowButtons'}
							</td>
							<td class="columnID">{@$template->templateID}</td>
							<td class="columnTitle columnTemplateName">{if $template->application != 'wcf'}<span class="badge label">{$template->application}</span> {/if}{if $template->templateGroupID}<a href="{link controller='TemplateEdit' id=$template->templateID}{/link}">{$template->templateName}</a>{else}{$template->templateName}{/if}</td>
							<td class="columnDate columnLastModificationTime">{@$template->lastModificationTime|time}</td>
							
							{event name='columns'}
						</tr>
					{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='TemplateAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.template.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
