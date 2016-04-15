{include file='header' pageTitle='wcf.acp.menu.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				<li><a href="{link controller='MenuItemList' id=$menuID}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.item.list{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='MenuList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='MenuAdd'}{/link}{else}{link controller='MenuEdit' id=$menuID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'title'} class="formError"{/if}>
			<dt><label for="title">{lang}wcf.global.title{/lang}</label></dt>
			<dd>
				<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" autofocus="autofocus" class="long" />
				{if $errorField == 'title'}
					<small class="innerError">
						{if $errorType == 'title' || $errorType == 'multilingual'}
							{lang}wcf.global.form.error.{@$errorType}{/lang}
						{else}
							{lang}wcf.acp.menu.title.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.menu.title.description{/lang}</small>
				{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
			</dd>
		</dl>
		
		{if $action == 'add' || $menu->identifier != 'com.woltlab.wcf.MainMenu'}
			<dl{if $errorField == 'position'} class="formError"{/if}>
				<dt><label for="position">{lang}wcf.acp.box.position{/lang}</label></dt>
				<dd>
					<select name="position" id="position">
						{foreach from=$availablePositions item=availablePosition}
							<option value="{@$availablePosition}"{if $availablePosition == $position} selected="selected"{/if}>{lang}wcf.acp.box.position.{@$availablePosition}{/lang}</option>
						{/foreach}
					</select>
					
					{if $errorField == 'position'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.box.position.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl>
				<dt><label for="showOrder">{lang}wcf.acp.box.showOrder{/lang}</label></dt>
				<dd>
					<input type="number" id="showOrder" name="showOrder" value="{@$showOrder}" class="tiny" min="0" />
				</dd>
			</dl>
			
			<dl{if $errorField == 'cssClassName'} class="formError"{/if}>
				<dt><label for="cssClassName">{lang}wcf.acp.box.cssClassName{/lang}</label></dt>
				<dd>
					<input type="text" id="cssClassName" name="cssClassName" value="{$cssClassName}" class="long" />
					{if $errorField == 'cssClassName'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.box.cssClassName.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="showHeader" name="showHeader" value="1" {if $showHeader}checked="checked" {/if}/> {lang}wcf.acp.box.showHeader{/lang}</label>
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="visibleEverywhere" name="visibleEverywhere" value="1" {if $visibleEverywhere}checked="checked" {/if}/> {lang}wcf.acp.box.visibleEverywhere{/lang}</label>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="pageIDs">{lang}wcf.acp.box.pageIDs{/lang}</label></dt>
				<dd>
					<select name="pageIDs[]" id="pageIDs" multiple="multiple" size="20">
						{foreach from=$pageNodeList item=pageNode}
							<option value="{@$pageNode->getPage()->pageID}"{if $pageNode->getPage()->pageID|in_array:$pageIDs} selected="selected"{/if}>{if $pageNode->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($pageNode->getDepth() - 1)}{/if}{$pageNode->getPage()->name}</option>
						{/foreach}
					</select>
				</dd>
			</dl>
		{/if}
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
