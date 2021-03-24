{include file='header' pageTitle='wcf.acp.menu.link.devtools.missingLanguageItem.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.devtools.missingLanguageItem.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $items}
						<li><a href="#" id="clearExisingMissingLanguageItemLog" class="button"><span class="icon icon16 fa-times"></span> <span>{lang}wcf.acp.devtools.missingLanguageItem.clearExistingLog{/lang}</span></a></li>
						<li><a href="#" id="clearMissingLanguageItemLog" class="button"><span class="icon icon16 fa-times"></span> <span>{lang}wcf.acp.devtools.missingLanguageItem.clearLog{/lang}</span></a></li>
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks controller='DevtoolsMissingLanguageItemList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
		{/content}
	</div>
{/hascontent}

{if $items}
	<div id="missingLanguageItemTable" class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\devtools\missing\language\item\DevtoolsMissingLanguageItemAction">
			<thead>
				<tr>
					<th class="columnID{if $sortField === 'itemID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='DevtoolsMissingLanguageItemList'}sortField=itemID&sortOrder={if $sortField === 'itemID' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText{if $sortField === 'languageID'} active {@$sortOrder}{/if}"><a href="{link controller='DevtoolsMissingLanguageItemList'}sortField=languageID&sortOrder={if $sortField === 'languageID' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.devtools.missingLanguageItem.languageID{/lang}</a></th>
					<th class="columnText{if $sortField === 'languageItem'} active {@$sortOrder}{/if}"><a href="{link controller='DevtoolsMissingLanguageItemList'}sortField=languageItem&sortOrder={if $sortField === 'languageItem' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.devtools.missingLanguageItem.languageItem{/lang}</a></th>
					<th class="columnText{if $sortField === 'lastTime'} active {@$sortOrder}{/if}"><a href="{link controller='DevtoolsMissingLanguageItemList'}sortField=lastTime&sortOrder={if $sortField === 'lastTime' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.devtools.missingLanguageItem.lastTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=logEntry}
					<tr class="jsObjectRow jsObjectActionObject" data-object-id="{@$logEntry->getObjectID()}">
						<td class="columnIcon">
							{objectAction action="delete" confirmMessage='wcf.acp.devtools.missingLanguageItem.delete.confirmMessage'}
							<span class="icon icon16 fa-align-justify jsStackTraceButton jsTooltip pointer" title="{lang}wcf.acp.devtools.missingLanguageItem.showStackTrace{/lang}" data-stack-trace="{$logEntry->getStackTrace()}"></span>
						</td>
						<td class="columnID">{@$logEntry->getObjectID()}</td>
						<td class="columnText">{if $logEntry->getLanguage()}{$logEntry->getLanguage()}{else}{$logEntry->languageID}{/if}</td>
						<td class="columnText">{$logEntry->languageItem}</td>
						<td class="columnDate">{@$logEntry->lastTime|time}</td>
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
						{event name='contentFooterNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
	
	<script data-relocate="true">
		require(['Ajax', 'Language', 'WoltLabSuite/Core/Acp/Ui/Devtools/Missing/Language/Item/List'], function(Ajax, Language, UiDevtoolsMissingLanguageItemList) {
			Language.addObject({
				'wcf.acp.devtools.missingLanguageItem.stackTrace': '{jslang}wcf.acp.devtools.missingLanguageItem.stackTrace{/jslang}',
				'wcf.acp.devtools.missingLanguageItem.clearLog.confirmMessage': '{jslang}wcf.acp.devtools.missingLanguageItem.clearLog.confirmMessage{/jslang}',
				'wcf.acp.devtools.missingLanguageItem.clearExistingLog.confirmMessage': '{jslang}wcf.acp.devtools.missingLanguageItem.clearExistingLog.confirmMessage{/jslang}',
			});
			
			new UiDevtoolsMissingLanguageItemList.default();
		});
	</script>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
