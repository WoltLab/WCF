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
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID{if $sortField === 'itemID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='DevtoolsMissingLanguageItemList'}sortField=itemID&sortOrder={if $sortField === 'itemID' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText{if $sortField === 'languageID'} active {@$sortOrder}{/if}"><a href="{link controller='DevtoolsMissingLanguageItemList'}sortField=languageID&sortOrder={if $sortField === 'languageID' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.devtools.missingLanguageItem.languageID{/lang}</a></th>
					<th class="columnText{if $sortField === 'languageItem'} active {@$sortOrder}{/if}"><a href="{link controller='DevtoolsMissingLanguageItemList'}sortField=languageItem&sortOrder={if $sortField === 'languageItem' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.devtools.missingLanguageItem.languageItem{/lang}</a></th>
					<th class="columnText{if $sortField === 'lastTime'} active {@$sortOrder}{/if}"><a href="{link controller='DevtoolsMissingLanguageItemList'}sortField=lastTime&sortOrder={if $sortField === 'lastTime' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.devtools.missingLanguageItem.lastTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=logEntry}
					<tr class="jsObjectRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$logEntry->getObjectID()}" data-confirm-message-html="{lang __encode=true}wcf.acp.devtools.missingLanguageItem.delete.confirmMessage{/lang}"></span>
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
		require(['Ajax', 'Ui/Confirmation', 'Ui/Dialog'], function(Ajax, UiConfirmation, UiDialog) {
			new WCF.Action.Delete('wcf\\data\\devtools\\missing\\language\\item\\DevtoolsMissingLanguageItemAction', '.jsObjectRow');
			
			elBySelAll('.jsStackTraceButton', undefined, function(button) {
				button.addEventListener('click', function(event) {
					var dialog = UiDialog.openStatic(
						'logEntryStackTrace',
						elData(event.currentTarget, 'stack-trace'),
						{
							title: '{lang}wcf.acp.devtools.missingLanguageItem.stackTrace{/lang}',
						}
					);
					
					elBySel('.jsOutputFormatToggle', dialog.dialog).addEventListener('click', function(event) {
						var pre = event.currentTarget.nextElementSibling;
						if (pre.style.whiteSpace) {
							pre.style.whiteSpace = '';
						}
						else {
							pre.style.whiteSpace = 'pre-wrap';
						}
					});
				});
			});
			
			elById('clearMissingLanguageItemLog').addEventListener('click', function() {
				UiConfirmation.show({
					'confirm': function() {
						Ajax.apiOnce({
							data: {
								actionName: 'clearLog',
								className: 'wcf\\data\\devtools\\missing\\language\\item\\DevtoolsMissingLanguageItemAction',
							},
							success: function() {
								window.location.reload();
							}
						});
					},
					'message': '{lang}wcf.acp.devtools.missingLanguageItem.clearLog.confirmMessage{/lang}',
				});
			});
		});
	</script>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
