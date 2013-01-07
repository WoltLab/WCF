{include file='header' pageTitle='wcf.acp.cronjob.list'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.cronjob.list{/lang}</h1>
		<h2>{lang}wcf.acp.cronjob.subtitle{/lang}</h2>
	</hgroup>
</header>

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\cronjob\\CronjobAction', '.jsCronjobRow');
		new WCF.Action.Toggle('wcf\\data\\cronjob\\CronjobAction', '.jsCronjobRow');
		new WCF.Action.SimpleProxy({
			action: 'execute',
			className: 'wcf\\data\\cronjob\\CronjobAction',
			elements: $('.jsCronjobRow .jsExecuteButton')
		}, {
			success: function(data, statusText, jqXHR) {
				$('.jsCronjobRow').each(function(index, row) {
					var $button = $(row).find('.jsExecuteButton');
					
					if (WCF.inArray($($button).data('objectID'), data.objectIDs)) {
						// insert feedback here
						$(row).find('td.columnNextExec').html(data.returnValues[$($button).data('objectID')].formatted);
						$(row).wcfHighlight();
					}
				});
			}
		});
	});
	//]]>
</script>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="CronjobList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='CronjobAdd'}{/link}" title="{lang}wcf.acp.cronjob.add{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.cronjob.add{/lang}</span></a></li>
			
			{event name='largeButtonsTop'}
		</ul>
	</nav>
</div>

{hascontent}
	<div class="tabularBox tabularBoxTitle marginTop">
		<hgroup>
			<h1>{lang}wcf.acp.cronjob.list{/lang} <span class="badge badgeInverse" title="{lang}wcf.acp.cronjob.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnCronjobID{if $sortField == 'cronjobID'} active{/if}" colspan="2"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=cronjobID&sortOrder={if $sortField == 'cronjobID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'cronjobID'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnStartMinute{if $sortField == 'startMinute'} active{/if}" title="{lang}wcf.acp.cronjob.startMinute{/lang}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=startMinute&sortOrder={if $sortField == 'startMinute' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.startMinuteShort{/lang}{if $sortField == 'startMinute'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnStartHour{if $sortField == 'startHour'} active{/if}" title="{lang}wcf.acp.cronjob.startHour{/lang}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=startHour&sortOrder={if $sortField == 'startHour' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.startHourShort{/lang}{if $sortField == 'startHour'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnStartDom{if $sortField == 'startDom'} active{/if}" title="{lang}wcf.acp.cronjob.startDom{/lang}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=startDom&sortOrder={if $sortField == 'startDom' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.startDomShort{/lang}{if $sortField == 'startDom'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnStartMonth{if $sortField == 'startMonth'} active{/if}" title="{lang}wcf.acp.cronjob.startMonth{/lang}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=startMonth&sortOrder={if $sortField == 'startMonth' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.startMonthShort{/lang}{if $sortField == 'startMonth'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnStartDow{if $sortField == 'startDow'} active{/if}" title="{lang}wcf.acp.cronjob.startDow{/lang}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=startDow&sortOrder={if $sortField == 'startDow' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.startDowShort{/lang}{if $sortField == 'startDow'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText columnDescription{if $sortField == 'description'} active{/if}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=description&sortOrder={if $sortField == 'description' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.description{/lang}{if $sortField == 'description'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnNextExec{if $sortField == 'nextExec'} active{/if}"><a href="{link controller='CronjobList'}pageNo={@$pageNo}&sortField=nextExec&sortOrder={if $sortField == 'nextExec' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.nextExec{/lang}{if $sortField == 'nextExec'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=cronjob}
						<tr class="jsCronjobRow">
							<td class="columnIcon">
								<img src="{@$__wcf->getPath()}icon/play.svg" alt="" title="{lang}wcf.acp.cronjob.execute{/lang}" class="icon16 jsExecuteButton jsTooltip pointer" data-object-id="{@$cronjob->cronjobID}" />
								
								{if $cronjob->canBeDisabled()}
									<img src="{@$__wcf->getPath()}icon/{if !$cronjob->isDisabled}enabled{else}disabled{/if}.svg" alt="" title="{lang}wcf.global.button.{if !$cronjob->isDisabled}disable{else}enable{/if}{/lang}" class="icon16 jsToggleButton jsTooltip pointer" data-object-id="{@$cronjob->cronjobID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" />
								{else}
									{if !$cronjob->isDisabled}
										<img src="{@$__wcf->getPath()}icon/enabled.svg" alt="" title="{lang}wcf.global.button.disable{/lang}" class="icon16 disabled" />
									{else}
										<img src="{@$__wcf->getPath()}icon/disabled.svg" alt="" title="{lang}wcf.global.button.enable{/lang}" class="icon16 disabled" />
									{/if}
								{/if}
								
								{if $cronjob->isEditable()}
									<a href="{link controller='CronjobEdit' id=$cronjob->cronjobID}{/link}"><img src="{@$__wcf->getPath()}icon/edit.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="icon16 jsTooltip" /></a>
								{else}
									<img src="{@$__wcf->getPath()}icon/edit.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="icon16 disabled" />
								{/if}
								{if $cronjob->isDeletable()}
									<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 jsDeleteButton jsTooltip pointer" data-object-id="{@$cronjob->cronjobID}" data-confirm-message="{lang}wcf.acp.cronjob.delete.sure{/lang}" />
								{else}
									<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 disabled" />
								{/if}
								
								{event name='buttons'}
							</td>
							<td class="columnID"><p>{@$cronjob->cronjobID}</p></td>
							<td class="columnDate columnStartMinute"><p>{$cronjob->startMinute|truncate:30}</p></td>
							<td class="columnDate columnStartHour"><p>{$cronjob->startHour|truncate:30}</p></td>
							<td class="columnDate columnStartDom"><p>{$cronjob->startDom|truncate:30}</p></td>
							<td class="columnDate columnStartMonth"><p>{$cronjob->startMonth|truncate:30}</p></td>
							<td class="columnDate columnStartDow"><p>{$cronjob->startDow|truncate:30}</p></td>
							<td class="columnText columnDescription" title="{$cronjob->description|language}">
								{if $cronjob->isEditable()}
									<p><a title="{lang}wcf.acp.cronjob.edit{/lang}" href="{link controller='CronjobEdit' id=$cronjob->cronjobID}{/link}">{$cronjob->description|language|truncate:50}</a></p>
								{else}
									<p>{$cronjob->description|language|truncate:50}</p>
								{/if}
							</td>
							<td class="columnDate columnNextExec">
								{if !$cronjob->isDisabled && $cronjob->nextExec != 1}
									<p>{@$cronjob->nextExec|plainTime}</p>
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
	<p class="info">{lang}wcf.acp.cronjob.noneAvailable{/lang}</p>
{/hascontent}

<div class="contentNavigation">
	{@$pagesLinks}
	
	<nav>
		<ul>
			<li><a href="{link controller='CronjobAdd'}{/link}" title="{lang}wcf.acp.cronjob.add{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.cronjob.add{/lang}</span></a></li>
			
			{event name='largeButtonsBottom'}
		</ul>
	</nav>
</div>

{include file='footer'}
