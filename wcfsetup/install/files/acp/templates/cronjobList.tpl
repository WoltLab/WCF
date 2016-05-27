{include file='header' pageTitle='wcf.acp.cronjob.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\cronjob\\CronjobAction', '.jsCronjobRow');
		new WCF.Action.Toggle('wcf\\data\\cronjob\\CronjobAction', '.jsCronjobRow');
		
		new WCF.ACP.Cronjob.ExecutionHandler();
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cronjob.list{/lang}</h1>
		<p class="contentHeaderDescription">{lang}wcf.acp.cronjob.subtitle{/lang}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CronjobAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.cronjob.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="CronjobList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{hascontent}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnCronjobID{if $sortField == 'cronjobID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=cronjobID&sortOrder={if $sortField == 'cronjobID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnDate columnStartMinute{if $sortField == 'startMinute'} active {@$sortOrder}{/if}" title="{lang}wcf.acp.cronjob.startMinute{/lang}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=startMinute&sortOrder={if $sortField == 'startMinute' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.startMinuteShort{/lang}</a></th>
					<th class="columnDate columnStartHour{if $sortField == 'startHour'} active {@$sortOrder}{/if}" title="{lang}wcf.acp.cronjob.startHour{/lang}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=startHour&sortOrder={if $sortField == 'startHour' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.startHourShort{/lang}</a></th>
					<th class="columnDate columnStartDom{if $sortField == 'startDom'} active {@$sortOrder}{/if}" title="{lang}wcf.acp.cronjob.startDom{/lang}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=startDom&sortOrder={if $sortField == 'startDom' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.startDomShort{/lang}</a></th>
					<th class="columnDate columnStartMonth{if $sortField == 'startMonth'} active {@$sortOrder}{/if}" title="{lang}wcf.acp.cronjob.startMonth{/lang}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=startMonth&sortOrder={if $sortField == 'startMonth' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.startMonthShort{/lang}</a></th>
					<th class="columnDate columnStartDow{if $sortField == 'startDow'} active {@$sortOrder}{/if}" title="{lang}wcf.acp.cronjob.startDow{/lang}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=startDow&sortOrder={if $sortField == 'startDow' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.startDowShort{/lang}</a></th>
					<th class="columnText columnDescription">{lang}wcf.acp.cronjob.description{/lang}</th>
					<th class="columnDate columnNextExec{if $sortField == 'nextExec'} active {@$sortOrder}{/if}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=nextExec&sortOrder={if $sortField == 'nextExec' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.nextExec{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=cronjob}
						<tr class="jsCronjobRow">
							<td class="columnIcon">
								<span class="icon icon16 fa-play jsExecuteButton jsTooltip pointer" title="{lang}wcf.acp.cronjob.execute{/lang}" data-object-id="{@$cronjob->cronjobID}"></span>
								
								{if $cronjob->canBeDisabled()}
									<span class="icon icon16 fa-{if !$cronjob->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$cronjob->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$cronjob->cronjobID}"></span>
								{else}
									{if !$cronjob->isDisabled}
										<span class="icon icon16 fa-check-square-o disabled" title="{lang}wcf.global.button.disable{/lang}"></span>
									{else}
										<span class="icon icon16 fa-square-o disabled" title="{lang}wcf.global.button.enable{/lang}"></span>
									{/if}
								{/if}
								
								{if $cronjob->isEditable()}
									<a href="{link controller='CronjobEdit' id=$cronjob->cronjobID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
								{else}
									<span class="icon icon16 fa-pencil disabled" title="{lang}wcf.global.button.edit{/lang}"></span>
								{/if}
								{if $cronjob->isDeletable()}
									<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$cronjob->cronjobID}" data-confirm-message-html="{lang __encode}wcf.acp.cronjob.delete.sure{/lang}"></span>
								{else}
									<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
								{/if}
								
								{event name='rowButtons'}
							</td>
							<td class="columnID">{@$cronjob->cronjobID}</td>
							<td class="columnDate columnStartMinute">{$cronjob->startMinute|truncate:30}</td>
							<td class="columnDate columnStartHour">{$cronjob->startHour|truncate:30}</td>
							<td class="columnDate columnStartDom">{$cronjob->startDom|truncate:30}</td>
							<td class="columnDate columnStartMonth">{$cronjob->startMonth|truncate:30}</td>
							<td class="columnDate columnStartDow">{$cronjob->startDow|truncate:30}</td>
							<td class="columnText columnDescription" title="{$cronjob->description|language}">
								{if $cronjob->isEditable()}
									<a title="{lang}wcf.acp.cronjob.edit{/lang}" href="{link controller='CronjobEdit' id=$cronjob->cronjobID}{/link}">{$cronjob->description|language|truncate:50}</a>
								{else}
									{$cronjob->description|language|truncate:50}
								{/if}
							</td>
							<td class="columnDate columnNextExec">
								{if !$cronjob->isDisabled && $cronjob->nextExec != 1}
									{@$cronjob->nextExec|plainTime}
								{/if}
							</td>
							
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/hascontent}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	<nav class="contentFooterNavigation">
		<ul>
			<li><a href="{link controller='CronjobAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.cronjob.add{/lang}</span></a></li>
			
			{event name='contentFooterNavigation'}
		</ul>
	</nav>
</footer>

{include file='footer'}
