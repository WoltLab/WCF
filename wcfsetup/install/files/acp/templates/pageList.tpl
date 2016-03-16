{include file='header' pageTitle='wcf.acp.page.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\page\\PageAction', '.jsPageRow');
		new WCF.Action.Toggle('wcf\\data\\page\\PageAction', '.jsPageRow');
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.page.list{/lang}</h1>
</header>

<form method="post" action="{link controller='PageList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap">
			<dl class="col-xs-12 col-md-4">
				<dt><label for="name">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="name" name="name" value="{$name}" class="long" />
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt><label for="pageTitle">{lang}wcf.acp.page.title{/lang}</label></dt>
				<dd>
					<input type="text" id="pageTitle" name="title" value="{$title}" class="long" />
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt><label for="pageContent">{lang}wcf.acp.page.content{/lang}</label></dt>
				<dd>
					<input type="text" id="pageContent" name="content" value="{$content}" class="long" />
				</dd>
			</dl>
			
			{event name='filterFields'}
		</div>
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<div class="contentNavigation">
	{assign var='linkParameters' value=''}
	{if $name}{capture append=linkParameters}&name={@$name|rawurlencode}{/capture}{/if}
	{if $title}{capture append=linkParameters}&title={@$title|rawurlencode}{/capture}{/if}
	{if $content}{capture append=linkParameters}&content={@$content|rawurlencode}{/capture}{/if}
	{pages print=true assign=pagesLinks controller="PageList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
	
	<nav>
		<ul>
			<li><a href="{link controller='PageLanding'}{/link}" class="button"><span class="icon icon16 fa-home"></span> {lang}wcf.acp.page.landing{/lang}</a></li>
			<li><a href="{link controller='PageAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.page.add{/lang}</span></a></li>
			<li><a href="{link controller='PageAdd'}isMultilingual=1{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.page.addMultilingual{/lang}</span></a></li>
		
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnPageID{if $sortField == 'pageID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=pageID&sortOrder={if $sortField == 'pageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnName{if $sortField == 'name'} active {@$sortOrder}{/if}"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=name&sortOrder={if $sortField == 'name' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnURL">{lang}wcf.acp.page.url{/lang}</th>
					<th class="columnDate columnLastUpdateTime{if $sortField == 'lastUpdateTime'} active {@$sortOrder}{/if}"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=lastUpdateTime&sortOrder={if $sortField == 'lastUpdateTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.page.lastUpdateTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=page}
					<tr class="jsPageRow">
						<td class="columnIcon">
							{if $page->canDisable()}
								<span class="icon icon16 fa-{if !$page->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$page->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$page->pageID}"></span>
							{else}
								<span class="icon icon16 fa-{if !$page->isDisabled}check-{/if}square-o disabled" title="{lang}wcf.global.button.{if !$page->isDisabled}disable{else}enable{/if}{/lang}"></span>
							{/if}
							<a href="{link controller='PageEdit' id=$page->pageID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $page->canDelete()}
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$page->pageID}" data-confirm-message="{lang}wcf.acp.page.delete.confirmMessage{/lang}"></span>
							{else}
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							{if !$page->requireObjectID}
								<a href="{$page->getLink()}" title="{lang}wcf.acp.page.button.viewPage{/lang}" class="jsTooltip"><span class="icon icon16 fa-search"></span></a>
							{else}
								<span class="icon icon16 fa-search disabled" title="{lang}wcf.acp.page.button.viewPage{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnPageID">{@$page->pageID}</td>
						<td class="columnTitle columnName">{if $page->isLandingPage}<span class="icon icon16 fa-home jsTooltip" title="{lang}wcf.acp.page.isLandingPage{/lang}"></span> {/if}<a href="{link controller='PageEdit' id=$page->pageID}{/link}">{$page->name}</a></td>
						<td class="columnText columnURL">
							<span class="badge label">{$page->getApplication()->getAbbreviation()}</span>
							{$page->getDisplayLink()}
						</td>
						<td class="columnDate columnLastUpdateTime">{@$page->lastUpdateTime|time}</td>
						
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
				<li><a href="{link controller='PageLanding'}{/link}" class="button"><span class="icon icon16 fa-home"></span> {lang}wcf.acp.page.landing{/lang}</a></li>
				<li><a href="{link controller='PageAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.page.add{/lang}</span></a></li>
				<li><a href="{link controller='PageAdd'}isMultilingual=1{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.page.addMultilingual{/lang}</span></a></li>
		
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{/if}

{include file='footer'}
