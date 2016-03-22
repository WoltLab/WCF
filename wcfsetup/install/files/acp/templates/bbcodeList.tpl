{include file='header' pageTitle='wcf.acp.bbcode.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\bbcode\\BBCodeAction', '.jsBBCodeRow');
		new WCF.Action.Toggle('wcf\\data\\bbcode\\BBCodeAction', $('.jsBBCodeRow'));
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.bbcode.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="BBCodeList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='BBCodeAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.bbcode.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnBBCodeID{if $sortField == 'bbcodeID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='BBCodeList'}pageNo={@$pageNo}&sortField=bbcodeID&sortOrder={if $sortField == 'bbcodeID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnBBCode{if $sortField == 'bbcodeTag'} active {@$sortOrder}{/if}"><a href="{link controller='BBCodeList'}pageNo={@$pageNo}&sortField=bbcodeTag&sortOrder={if $sortField == 'bbcodeTag' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.bbcode.bbcodeTag{/lang}</a></th>
					<th class="columnText columnClassName{if $sortField == 'className'} active {@$sortOrder}{/if}"><a href="{link controller='BBCodeList'}pageNo={@$pageNo}&sortField=className&sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.bbcode.className{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=bbcode}
					<tr class="jsBBCodeRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-{if !$bbcode->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $bbcode->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$bbcode->bbcodeID}"></span>
							<a href="{link controller='BBCodeEdit' object=$bbcode}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $bbcode->canDelete()}
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$bbcode->bbcodeID}" data-confirm-message="{lang}wcf.acp.bbcode.delete.sure{/lang}"></span>
							{else}
								<span class="icon icon16 fa-times disabled"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$bbcode->bbcodeID}</td>
						<td class="columnTitle columnBBCode"><a href="{link controller='BBCodeEdit' object=$bbcode}{/link}">[{$bbcode->bbcodeTag}]</a></td>
						<td class="columnText columnClassName">{$bbcode->className}</td>
						
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
				<li><a href="{link controller='BBCodeAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.bbcode.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
