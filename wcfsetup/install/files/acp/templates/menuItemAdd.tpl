{include file='header' pageTitle='wcf.acp.menu.item.'|concat:$action}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		var $isInternalLink = $('input[name=isInternalLink]').filter('[value=1]');
		var $pageIDContainer = $('#pageIDContainer');
		var $externalURLContainer = $('#externalURLContainer');
		
		function handleIsInternalLink() {
			if ($isInternalLink.is(':checked')) {
				$pageIDContainer.show();
				$externalURLContainer.hide();
			}
			else {
				$pageIDContainer.hide();
				$externalURLContainer.show();
			}
		}
		
		$('input[name=isInternalLink]').change(handleIsInternalLink);
		handleIsInternalLink();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.menu.item.{$action}{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='MenuItemList' id=$menuID}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.item.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='MenuItemAdd'}{/link}{else}{link controller='MenuItemEdit' id=$itemID}{/link}{/if}">
	<section class="marginTop">
		<h1>{lang}wcf.global.form.data{/lang}</h1>
			
		<dl{if $errorField == 'parentItemID'} class="formError"{/if}>
			<dt><label for="parentItemID">{lang}wcf.acp.menu.item.parentItem{/lang}</label></dt>
			<dd>
				<select name="parentItemID" id="parentItemID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					
					{foreach from=$menuItemNodeList item=menuItemNode}
						<option value="{@$menuItemNode->getMenuItem()->itemID}"{if $menuItemNode->getMenuItem()->itemID == $parentItemID} selected="selected"{/if}>{if $menuItemNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($menuItemNode->getDepth() - 1)}{/if}{lang}{$menuItemNode->getMenuItem()->title}{/lang}</option>
					{/foreach}
				</select>
				{if $errorField == 'parentItemID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.menu.item.parentItemID.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'title'} class="formError"{/if}>
			<dt><label for="title">{lang}wcf.global.name{/lang}</label></dt>
			<dd>
				<input type="text" name="title" id="title" value="{$title}" class="long" required="required" />
				{if $errorField == 'title'}
					<small class="innerError">
						{if $errorType == 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{else}
							{lang}wcf.acp.menu.item.title.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
				
				{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
			</dd>
		</dl>
		
		{event name='dataFields'}
	</section>
	
	<section>
		<h1>{lang}wcf.acp.menu.item.link{/lang}</h1>
	
		<dl>
			<dt></dt>
			<dd class="floated">
				<label><input type="radio" name="isInternalLink" value="1"{if $isInternalLink} checked="checked"{/if} /> {lang}wcf.acp.menu.item.link.internal{/lang}</label>
				<label><input type="radio" name="isInternalLink" value="0"{if !$isInternalLink} checked="checked"{/if} /> {lang}wcf.acp.menu.item.link.external{/lang}</label>
			</dd>
		</dl>
		
		<dl id="pageIDContainer"{if $errorField == 'pageID'} class="formError"{/if}>
			<dt><label for="pageID">{lang}wcf.acp.page.parentPageID{/lang}</label></dt>
			<dd>
				<select name="pageID" id="pageID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					
					{foreach from=$pageNodeList item=pageNode}
						<option value="{@$pageNode->getPage()->pageID}"{if $pageNode->getPage()->pageID == $pageID} selected="selected"{/if}>{if $pageNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($pageNode->getDepth() - 1)}{/if}{$pageNode->getPage()->displayName}</option>
					{/foreach}
				</select>
				{if $errorField == 'pageID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.menu.item.pageID.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl id="externalURLContainer"{if $errorField == 'externalURL'} class="formError"{/if}>
			<dt><label for="externalURL">{lang}wcf.acp.menu.item.externalURL{/lang}</label></dt>
			<dd>
				<input type="text" name="externalURL" id="externalURL" value="{$externalURL}" class="long" />
				{if $errorField == 'externalURL'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.menu.item.externalURL.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
				
				{include file='multipleLanguageInputJavascript' elementIdentifier='externalURL' forceSelection=false}
			</dd>
		</dl>
		
		{event name='linkFields'}
	</section>
	
	<section>
		<h1>{lang}wcf.acp.menu.item.advanced{/lang}</h1>
		
		<dl>
			<dt><label for="showOrder">{lang}wcf.acp.menu.item.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" name="showOrder" id="showOrder" value="{@$showOrder}" class="tiny" min="0" />
			</dd>
		</dl>
		
		{if $action == 'add' || !$menuItem->isLandingPage}
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="isDisabled" id="isDisabled" value="1"{if $isDisabled} checked="checked"{/if} /> <span>{lang}wcf.acp.menu.item.isDisabled{/lang}</span></label>
				</dd>
			</dl>
		{/if}
		
		{event name='advancedFields'}
	</section>
		
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" />
		{if $action == 'add'}<input type="hidden" name="menuID" value="{@$menuID}" />{/if}
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
