{include file='header' pageTitle='wcf.acp.bbcode.mediaProvider.list'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.bbcode.mediaProvider.list{/lang}</h1>
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\bbcode\\media\\provider\\BBCodeMediaProviderAction', '.jsMediaProviderRow');
		});
		//]]>
	</script>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="BBCodeMediaProviderList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='BBCodeMediaProviderAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.bbcode.mediaProvider.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.bbcode.mediaProvider.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnMediaProviderID{if $sortField == 'providerID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='BBCodeMediaProviderList'}pageNo={@$pageNo}&sortField=providerID&sortOrder={if $sortField == 'providerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnMediaProviderTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='BBCodeMediaProviderList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.bbcode.mediaProvider.title{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item='mediaProvider'}
					<tr class="jsMediaProviderRow">
						<td class="columnIcon">
							<a href="{link controller='BBCodeMediaProviderEdit' object=$mediaProvider}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$mediaProvider->providerID}" data-confirm-message="{lang}wcf.acp.bbcode.mediaProvider.delete.sure{/lang}"></span>
							
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
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a href="{link controller='BBCodeMediaProviderAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.bbcode.mediaProvider.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<div class="container containerPadding">
		<div>
			<p class="info">{lang}wcf.global.noItems{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
