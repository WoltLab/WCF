{include file='header' pageTitle='wcf.acp.box.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\box\\BoxAction', '.jsBoxRow');
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.box.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="BoxList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='BoxAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.box.add{/lang}</span></a></li>
			<li><a href="{link controller='BoxAdd'}isMultilingual=1{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.box.addMultilingual{/lang}</span></a></li>
		
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.box.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnPageID{if $sortField == 'boxID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='BoxList'}pageNo={@$pageNo}&sortField=boxID&sortOrder={if $sortField == 'boxID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnName{if $sortField == 'name'} active {@$sortOrder}{/if}"><a href="{link controller='BoxList'}pageNo={@$pageNo}&sortField=name&sortOrder={if $sortField == 'name' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnBoxType{if $sortField == 'boxType'} active {@$sortOrder}{/if}"><a href="{link controller='BoxList'}pageNo={@$pageNo}&sortField=boxType&sortOrder={if $sortField == 'boxType' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.box.boxType{/lang}</a></th>
					<th class="columnText columnPosition{if $sortField == 'position'} active {@$sortOrder}{/if}"><a href="{link controller='BoxList'}pageNo={@$pageNo}&sortField=position&sortOrder={if $sortField == 'position' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.box.position{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=box}
					<tr class="jsBoxRow">
						<td class="columnIcon">
							<a href="{link controller='BoxEdit' id=$box->boxID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $box->canDelete()}
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$box->boxID}" data-confirm-message="{lang}wcf.acp.box.delete.confirmMessage{/lang}"></span>
							{else}
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnBoxID">{@$box->boxID}</td>
						<td class="columnTitle columnName"><a href="{link controller='BoxEdit' id=$box->boxID}{/link}">{$box->name}</a></td>
						<td class="columnText columnBoxType">{$box->boxType}</td>
						<td class="columnText columnPosition">{$box->position}</td>
						
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
				<li><a href="{link controller='BoxAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.box.add{/lang}</span></a></li>
				<li><a href="{link controller='BoxAdd'}isMultilingual=1{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.box.addMultilingual{/lang}</span></a></li>
		
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{/if}

{include file='footer'}
