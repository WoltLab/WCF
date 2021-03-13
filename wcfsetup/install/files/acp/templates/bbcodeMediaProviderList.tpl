{include file='header' pageTitle='wcf.acp.bbcode.mediaProvider.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\bbcode\\media\\provider\\BBCodeMediaProviderAction', '.jsMediaProviderRow');
		new WCF.Action.Toggle('wcf\\data\\bbcode\\media\\provider\\BBCodeMediaProviderAction', '.jsMediaProviderRow');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.bbcode.mediaProvider.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='BBCodeMediaProviderAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.bbcode.mediaProvider.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="BBCodeMediaProviderList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnMediaProviderID{if $sortField == 'providerID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='BBCodeMediaProviderList'}pageNo={@$pageNo}&sortField=providerID&sortOrder={if $sortField == 'providerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnMediaProviderTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='BBCodeMediaProviderList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.bbcode.mediaProvider.title{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item='mediaProvider'}
					<tr class="jsMediaProviderRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-{if !$mediaProvider->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$mediaProvider->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$mediaProvider->providerID}"></span>
							<a href="{link controller='BBCodeMediaProviderEdit' object=$mediaProvider}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$mediaProvider->providerID}" data-confirm-message-html="{lang __encode=true}wcf.acp.bbcode.mediaProvider.delete.sure{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$mediaProvider->providerID}</td>
						<td class="columnTitle columnMediaProviderTitle"><a href="{link controller='BBCodeMediaProviderEdit' object=$mediaProvider}{/link}">{$mediaProvider->title}</a></td>
						
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
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='BBCodeMediaProviderAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.bbcode.mediaProvider.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
