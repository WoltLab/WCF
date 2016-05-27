{include file='header' pageTitle='wcf.acp.cronjob.log'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.cronjob.log.clear.confirm': '{lang}wcf.acp.cronjob.log.clear.confirm{/lang}',
			'wcf.acp.cronjob.log.error.details': '{lang}wcf.acp.cronjob.log.error.details{/lang}'
		});
		
		new WCF.ACP.Cronjob.LogList();
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cronjob.log{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $objects|count}
						<li><a title="{lang}wcf.acp.cronjob.log.clear{/lang}" class="button jsCronjobLogDelete"><span class="icon icon16 fa-times"></span> <span>{lang}wcf.acp.cronjob.log.clear{/lang}</span></a></li>
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="CronjobLogList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnCronjobID{if $sortField == 'cronjobID'} active {@$sortOrder}{/if}"><a href="{link controller='CronjobLogList'}pageNo={@$pageNo}&sortField=cronjobID&sortOrder={if $sortField == 'cronjobID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnClassName{if $sortField == 'className'} active {@$sortOrder}{/if}"><a href="{link controller='CronjobLogList'}pageNo={@$pageNo}&sortField=className&sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.className{/lang}</a></th>
					<th class="columnText columnDescription{if $sortField == 'description'} active {@$sortOrder}{/if}"><a href="{link controller='CronjobLogList'}pageNo={@$pageNo}&sortField=description&sortOrder={if $sortField == 'description' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.description{/lang}</a></th>
					<th class="columnDate columnExecTime{if $sortField == 'execTime'} active {@$sortOrder}{/if}"><a href="{link controller='CronjobLogList'}pageNo={@$pageNo}&sortField=execTime&sortOrder={if $sortField == 'execTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.log.execTime{/lang}</a></th>
					<th class="columnText columnSuccess{if $sortField == 'success'} active {@$sortOrder}{/if}"><a href="{link controller='CronjobLogList'}pageNo={@$pageNo}&sortField=success&sortOrder={if $sortField == 'success' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.log.status{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=cronjobLog}
					<tr>
						<td class="columnID columnCronjobID">{@$cronjobLog->cronjobID}</td>
						<td class="columnTitle columnClassName">{$cronjobLog->className}</td>
						<td class="columnText columnDescription">{$cronjobLog->description|language}</td>
						<td class="columnDate columnExecTime">{if $cronjobLog->execTime}{@$cronjobLog->execTime|time}{/if}</td>
						
						<td class="columnText columnSuccess">
							{if $cronjobLog->success}
								<span class="badge green">{lang}wcf.acp.cronjob.log.success{/lang}</span>
							{elseif $cronjobLog->error}
								<a class="badge red jsTooltip jsCronjobError" title="{lang}wcf.acp.cronjob.log.error.showDetails{/lang}">{lang}wcf.acp.cronjob.log.error{/lang}</a>
								<span style="display: none">{@$cronjobLog->error}</span>
							{/if}
						</td>
						
						{event name='columns'}
					</tr>
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
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}
						{if $objects|count}
							<li><a title="{lang}wcf.acp.cronjob.log.clear{/lang}" class="button jsCronjobLogDelete"><span class="icon icon16 fa-times"></span> <span>{lang}wcf.acp.cronjob.log.clear{/lang}</span></a></li>
						{/if}
						
						{event name='contentFooterNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info">{lang}wcf.acp.cronjob.log.noEntries{/lang}</p>
{/if}

{include file='footer'}
