{include file='header' pageTitle='wcf.acp.user.rank.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\user\\rank\\UserRankAction', '.jsUserRankRow');
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.rank.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="UserRankList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='UserRankAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.user.rank.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{hascontent}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.user.rank.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnRankID{if $sortField == 'rankID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserRankList'}pageNo={@$pageNo}&sortField=rankID&sortOrder={if $sortField == 'rankID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnRankTitle{if $sortField == 'rankTitle'} active {@$sortOrder}{/if}"><a href="{link controller='UserRankList'}pageNo={@$pageNo}&sortField=rankTitle&sortOrder={if $sortField == 'rankTitle' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.rank.title{/lang}</a></th>
					<th class="columnText columnRankImage{if $sortField == 'rankImage'} active {@$sortOrder}{/if}"><a href="{link controller='UserRankList'}pageNo={@$pageNo}&sortField=rankImage&sortOrder={if $sortField == 'rankImage' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.rank.image{/lang}</a></th>
					<th class="columnText columnGroupID{if $sortField == 'groupID'} active {@$sortOrder}{/if}"><a href="{link controller='UserRankList'}pageNo={@$pageNo}&sortField=groupID&sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.group{/lang}</a></th>
					<th class="columnText columnRequiredGender{if $sortField == 'requiredGender'} active {@$sortOrder}{/if}"><a href="{link controller='UserRankList'}pageNo={@$pageNo}&sortField=requiredGender&sortOrder={if $sortField == 'requiredGender' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.option.gender{/lang}</a></th>
					<th class="columnDigits columnRequiredPoints{if $sortField == 'requiredPoints'} active {@$sortOrder}{/if}"><a href="{link controller='UserRankList'}pageNo={@$pageNo}&sortField=requiredPoints&sortOrder={if $sortField == 'requiredPoints' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.rank.requiredPoints{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=userRank}
						<tr class="jsUserRankRow">
							<td class="columnIcon">
								<a href="{link controller='UserRankEdit' id=$userRank->rankID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$userRank->rankID}" data-confirm-message="{lang}wcf.acp.user.rank.delete.sure{/lang}"></span>
								
								{event name='rowButtons'}
							</td>
							<td class="columnID columnRankID">{@$userRank->rankID}</td>
							<td class="columnTitle columnRankTitle"><a href="{link controller='UserRankEdit' id=$userRank->rankID}{/link}" title="{lang}wcf.acp.user.rank.edit{/lang}" class="badge label{if $userRank->cssClassName} {$userRank->cssClassName}{/if}">{$userRank->rankTitle|language}</a></td>
							<td class="columnText columnRankImage">{if $userRank->rankImage}{@$userRank->getImage()}{/if}</td>
							<td class="columnText columnGroupID">{$userRank->groupName|language}</td>
							<td class="columnText columnRequiredGender">{if $userRank->requiredGender}{if $userRank->requiredGender == 1}{lang}wcf.user.gender.male{/lang}{else}{lang}wcf.user.gender.female{/lang}{/if}{/if}</td>
							<td class="columnDigits columnRequiredPoints">{#$userRank->requiredPoints}</td>
							
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
				<li><a href="{link controller='UserRankAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.user.rank.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/hascontent}

{include file='footer'}
