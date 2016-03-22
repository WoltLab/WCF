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
	<h1 class="contentTitle">{lang}wcf.acp.cronjob.log{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="CronjobLogList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $objects|count}
						<li><a title="{lang}wcf.acp.cronjob.log.clear{/lang}" class="button jsCronjobLogDelete"><span class="icon icon16 fa-times"></span> <span>{lang}wcf.acp.cronjob.log.clear{/lang}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{hascontent}
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
				{content}
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
				{/content}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a title="{lang}wcf.acp.cronjob.log.clear{/lang}" class="button jsCronjobLogDelete"><span class="icon icon16 fa-times"></span> <span>{lang}wcf.acp.cronjob.log.clear{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.acp.cronjob.log.noEntries{/lang}</p>
{/hascontent}

{include file='footer'}
