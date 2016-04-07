{include file='header' pageTitle='wcf.acp.smiley.list'}

{if $objects|count}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\smiley\\SmileyAction', $('.smileyRow'));
			new WCF.Sortable.List('smileyList', 'wcf\\data\\smiley\\SmileyAction', {@$startIndex});
		});
		//]]>
	</script>
{/if}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.smiley.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='SmileyAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.smiley.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="SmileyList" object=$category link="pageNo=%d"}{/content}
	</div>
{/hascontent}

{if $smileyCount}
	<div class="section tabMenuContainer">
		<nav class="menu">{*todo*}
			<ul>
				{foreach from=$categories item=categoryLoop}
					<li{if (!$category && !$categoryLoop->categoryID) || ($category && $category->categoryID == $categoryLoop->categoryID)} class="ui-state-active"{/if}><a href="{if $categoryLoop->categoryID}{link controller='SmileyList' object=$categoryLoop}{/link}{else}{link controller='SmileyList'}{/link}{/if}">{$categoryLoop->title|language}</a></li>
				{/foreach}
			</ul>
		</nav>
		<section id="smileyList" class="sortableListContainer">
			{if $objects|count}
				<ol class="sortableList" data-object-id="0" start="{@($pageNo - 1) * $itemsPerPage + 1}">
					{foreach from=$objects item=smiley}
						<li class="sortableNode sortableNoNesting smileyRow" data-object-id="{@$smiley->smileyID}">
							<span class="sortableNodeLabel">
								<a href="{link controller='SmileyEdit' id=$smiley->smileyID}{/link}"><img src="{$smiley->getURL()}" alt=""/ > {lang}{$smiley->smileyTitle}{/lang}</a> <span class="badge">{$smiley->smileyCode}</span>{foreach from=$smiley->getAliases() item='alias'} <span class="badge">{$alias}</span>{/foreach}
								
								<span class="statusDisplay sortableButtonContainer">
									<a href="{link controller='SmileyEdit' id=$smiley->smileyID}{/link}"><span title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip icon icon16 fa-pencil" /></a>
									<span title="{lang}wcf.global.button.delete{/lang}" class="jsDeleteButton jsTooltip icon icon16 fa-times" data-object-id="{@$smiley->smileyID}" data-confirm-message="{lang}wcf.acp.smiley.delete.sure{/lang}" />
									
									{event name='itemButtons'}
								</span>
							</span>
							<ol class="sortableList" data-object-id="{@$smiley->smileyID}"></ol></li>
						</li>
					{/foreach}
				</ol>
				<div class="formSubmit">
					<button class="button" data-type="submit">{lang}wcf.global.button.submit{/lang}</button>
				</div>
			{else}
				<p class="info">{lang}wcf.global.noItems{/lang}</p>
			{/if}
		</section>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
