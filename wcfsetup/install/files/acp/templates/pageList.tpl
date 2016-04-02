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

<div class="contentNavigation">
	{assign var='linkParameters' value=''}
	{if $name}{capture append=linkParameters}&name={@$name|rawurlencode}{/capture}{/if}
	{if $title}{capture append=linkParameters}&title={@$title|rawurlencode}{/capture}{/if}
	{if $content}{capture append=linkParameters}&content={@$content|rawurlencode}{/capture}{/if}
	{if $packageID}{capture append=linkParameters}&packageID={@$packageID}{/capture}{/if}
	{if $pageType}{capture append=linkParameters}&pageType={@$pageType|rawurlencode}{/capture}{/if}
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

<form method="post" action="{link controller='PageList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap">
			<div class="row rowColGap col-xs-12 col-md-10">
				<dl class="col-xs-12 col-md-4 wide">
					<dt></dt>
					<dd>
						<input type="text" id="name" name="name" value="{$name}" placeholder="{lang}wcf.global.name{/lang}" class="long" />
					</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-4 wide">
					<dt></dt>
					<dd>
						<input type="text" id="pageTitle" name="title" value="{$title}" placeholder="{lang}wcf.acp.page.title{/lang}" class="long" />
					</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-4 wide">
					<dt></dt>
					<dd>
						<input type="text" id="pageContent" name="content" value="{$content}" placeholder="{lang}wcf.acp.page.content{/lang}" class="long" />
					</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-4 wide">
					<dt></dt>
					<dd>
						<select name="packageID" id="packageID">
							<option value="0">{lang}wcf.acp.page.packageID{/lang}</option>
							{foreach from=$availableApplications item=availableApplication}
								<option value="{@$availableApplication->packageID}"{if $availableApplication->packageID == $packageID} selected="selected"{/if}>{$availableApplication->getAbbreviation()}: {$availableApplication->domainName}{$availableApplication->domainPath}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				
				<dl class="col-xs-12 col-md-4 wide">
					<dt></dt>
					<dd>
						<select name="pageType" id="pageType">
							<option value="">{lang}wcf.acp.page.pageType{/lang}</option>
							<option value="static"{if $pageType == 'static'} selected="selected"{/if}>{lang}wcf.acp.page.pageType.static{/lang}</option>
							<option value="system"{if $pageType == 'system'} selected="selected"{/if}>{lang}wcf.acp.page.pageType.system{/lang}</option>
						</select>
					</dd>
				</dl>
				
				{event name='filterFields'}
			</div>
			
			<div class="col-xs-12 col-md-2">
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
				{@SECURITY_TOKEN_INPUT_TAG}
			</div>
		</div>
	</section>
</form>

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnPageID{if $sortField == 'pageID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=pageID&sortOrder={if $sortField == 'pageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnName{if $sortField == 'name'} active {@$sortOrder}{/if}"><a href="{link controller='PageList'}pageNo={@$pageNo}&sortField=name&sortOrder={if $sortField == 'name' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnURL" colspan="2">{lang}wcf.acp.page.url{/lang}</th>
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
						<td class="columnIcon"><span class="badge label">{$page->getApplication()->getAbbreviation()}</span></td>
						<td class="columnText columnURL">
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
