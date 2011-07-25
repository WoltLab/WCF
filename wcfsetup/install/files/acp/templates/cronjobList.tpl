{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/cronjobsL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.cronjob.list{/lang}</h1>
		<h2>{lang}wcf.acp.cronjob.subtitle{/lang}</h2>
	</hgroup>
	
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\cronjob\\CronjobAction', $('.cronjobRow'));
			new WCF.Action.Toggle('wcf\\data\\cronjob\\CronjobAction', $('.cronjobRow'));
			new WCF.Action.SimpleProxy({
				action: 'execute',
				className: 'wcf\\data\\cronjob\\CronjobAction',
				elements: $('.cronjobRow .executeButton')
			}, {
				success: function(data, statusText, jqXHR) {
					$('.cronjobRow').each(function(index, row) {
						$button = $(row).find('.executeButton');
						
						if (WCF.inArray($($button).data('objectID'), data.objectIDs)) {
							// insert feedback here
							$(row).find('td.columnNextExec').html('...');
							$(row).wcfHighlight();
						}
					});
				}
			});
		});
		//]]>
	</script>
</header>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=CronjobList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:SID_ARG_2ND_NOT_ENCODED}
	
	{if $__wcf->session->getPermission('admin.system.cronjobs.canAddCronjob')}
		<nav class="largeButtons">
			<ul><li><a href="index.php?form=CronjobAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.cronjob.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobsAddM.png" alt="" /> <span>{lang}wcf.acp.cronjob.add{/lang}</span></a></li></ul>
		</nav>
	{/if}
</div>

{if !$items}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.cronjob.noneAvailable{/lang}</p>
		</div>
	</div>
{else}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.cronjob.list.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnCronjobID{if $sortField == 'cronjobID'} active{/if}" colspan="2"><div><a href="index.php?page=CronjobList&amp;pageNo={@$pageNo}&amp;sortField=cronjobID&amp;sortOrder={if $sortField == 'cronjobID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.cronjobID{/lang}{if $sortField == 'cronjobID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStartMinute{if $sortField == 'startMinute'} active{/if}" title="{lang}wcf.acp.cronjob.startMinute{/lang}"><div><a href="index.php?page=CronjobList&amp;pageNo={@$pageNo}&amp;sortField=startMinute&amp;sortOrder={if $sortField == 'startMinute' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.startMinuteShort{/lang}{if $sortField == 'startMinute'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStartHour{if $sortField == 'startHour'} active{/if}" title="{lang}wcf.acp.cronjob.startHour{/lang}"><div><a href="index.php?page=CronjobList&amp;pageNo={@$pageNo}&amp;sortField=startHour&amp;sortOrder={if $sortField == 'startHour' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.startHourShort{/lang}{if $sortField == 'startHour'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStartDom{if $sortField == 'startDom'} active{/if}" title="{lang}wcf.acp.cronjob.startDom{/lang}"><div><a href="index.php?page=CronjobList&amp;pageNo={@$pageNo}&amp;sortField=startDom&amp;sortOrder={if $sortField == 'startDom' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.startDomShort{/lang}{if $sortField == 'startDom'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStartMonth{if $sortField == 'startMonth'} active{/if}" title="{lang}wcf.acp.cronjob.startMonth{/lang}"><div><a href="index.php?page=CronjobList&amp;pageNo={@$pageNo}&amp;sortField=startMonth&amp;sortOrder={if $sortField == 'startMonth' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.startMonthShort{/lang}{if $sortField == 'startMonth'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStartDow{if $sortField == 'startDow'} active{/if}" title="{lang}wcf.acp.cronjob.startDow{/lang}"><div><a href="index.php?page=CronjobList&amp;pageNo={@$pageNo}&amp;sortField=startDow&amp;sortOrder={if $sortField == 'startDow' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.startDowShort{/lang}{if $sortField == 'startDow'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnDescription{if $sortField == 'description'} active{/if}"><div><a href="index.php?page=CronjobList&amp;pageNo={@$pageNo}&amp;sortField=description&amp;sortOrder={if $sortField == 'description' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.description{/lang}{if $sortField == 'description'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnNextExec{if $sortField == 'nextExec'} active{/if}"><div><a href="index.php?page=CronjobList&amp;pageNo={@$pageNo}&amp;sortField=nextExec&amp;sortOrder={if $sortField == 'nextExec' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.nextExec{/lang}{if $sortField == 'nextExec'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalHeadColumns|isset}{@$additionalHeadColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$cronjobs item=cronjob}
				<tr class="cronjobRow {cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $__wcf->session->getPermission('admin.system.cronjobs.canEditCronjob')}
							<img src="{@RELATIVE_WCF_DIR}icon/cronjobExecuteS.png" alt="" class="executeButton" title="{lang}wcf.acp.cronjob.execute{/lang}" data-objectID="{@$cronjob->cronjobID}" />
						{/if}
						
						{if $cronjob->canBeDisabled()}
							<img src="{@RELATIVE_WCF_DIR}icon/{if $cronjob->active}enabled{else}disabled{/if}S.png" alt="" class="toggleButton" title="{lang}wcf.acp.cronjobs.{if $cronjob->active}disable{else}enable{/if}{/lang}" data-objectID="{@$cronjob->cronjobID}" data-disableMessage="{lang}wcf.acp.cronjob.disable{/lang}" data-enableMessage="{lang}wcf.acp.cronjob.enable{/lang}" />
						{else}
							{if $cronjob->active}
								<img src="{@RELATIVE_WCF_DIR}icon/enabledDisabledS.png" alt="" title="{lang}wcf.acp.cronjob.disable{/lang}" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/disabledDisabledS.png" alt="" title="{lang}wcf.acp.cronjob.enable{/lang}" />
							{/if}
						{/if}
						
						{if $cronjob->isEditable()}
							<a href="index.php?form=CronjobEdit&amp;cronjobID={@$cronjob->cronjobID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.cronjob.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.cronjob.edit.disabled{/lang}" />
						{/if}
						{if $cronjob->isDeletable()}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.cronjob.delete{/lang}" class="deleteButton" data-objectID="{@$cronjob->cronjobID}" data-confirmMessage="{lang}wcf.acp.cronjob.delete.sure{/lang}" />
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.cronjob.delete.disabled{/lang}" />
						{/if}
						{if $additionalButtons[$cronjob->cronjobID]|isset}{@$additionalButtons[$cronjob->cronjobID]}{/if}
					</td>
					<td class="columnID">{@$cronjob->cronjobID}</td>
					<td class="columnStartMinute">{$cronjob->startMinute|truncate:30:' ...'}</td>
					<td class="columnStartHour">{$cronjob->startHour|truncate:30:' ...'}</td>
					<td class="columnStartDom">{$cronjob->startDom|truncate:30:' ...'}</td>
					<td class="columnStartMonth">{$cronjob->startMonth|truncate:30:' ...'}</td>
					<td class="columnStartDow">{$cronjob->startDow|truncate:30:' ...'}</td>
					<td class="columnDescription columnText" title="{$cronjob->description}">
						{if $cronjob->editable}
							<a title="{lang}wcf.acp.cronjob.edit{/lang}" href="index.php?form=CronjobEdit&amp;cronjobID={@$cronjob->cronjobID}{@SID_ARG_2ND}">{$cronjob->description|truncate:50:" ..."}</a>
						{else}
							{$cronjob->description|truncate:50:' ...'}
						{/if}
					</td>
					<td class="columnNextExec columnDate">
						{if $cronjob->active && $cronjob->nextExec != 1}
							{@$cronjob->nextExec|plaintime}
						{/if}
					</td>
					
					{if $additionalColumns[$cronjob->cronjobID]|isset}{@$additionalColumns[$cronjob->cronjobID]}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $__wcf->session->getPermission('admin.system.cronjobs.canAddCronjob')}
			<nav class="largeButtons">
				<ul><li><a href="index.php?form=CronjobAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.cronjob.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobsAddM.png" alt="" /> <span>{lang}wcf.acp.cronjob.add{/lang}</span></a></li></ul>
			</nav>
		{/if}
	</div>
{/if}

{include file='footer'}
