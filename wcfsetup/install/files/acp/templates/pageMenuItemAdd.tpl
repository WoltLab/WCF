{include file='header' pageTitle='wcf.acp.pageMenu.'|concat:$action}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		var $menuPosition = $('#menuPosition');
		var $parentMenuItemContainer = $('#parentMenuItemContainer');
		var $isInternalLink = $('input[name=isInternalLink]').filter('[value=1]');
		var $menuItemControllerContainer = $('#menuItemControllerContainer');
		var $menuItemLinkContainer = $('#menuItemLinkContainer');
		var $menuItemParametersContainer = $('#menuItemParametersContainer');
		
		function handleMenuPosition() {
			if ($menuPosition.val() === 'header') {
				$parentMenuItemContainer.show();
			}
			else {
				$parentMenuItemContainer.hide();
			}
		}
		
		function handleIsInternalLink() {
			if ($isInternalLink.is(':checked')) {
				$menuItemControllerContainer.show();
				$menuItemParametersContainer.show();
				$menuItemLinkContainer.hide();
			}
			else {
				$menuItemControllerContainer.hide();
				$menuItemParametersContainer.hide();
				$menuItemLinkContainer.show();
			}
		}
		
		$menuPosition.change(handleMenuPosition);
		$('input[name=isInternalLink]').change(handleIsInternalLink);
		
		handleMenuPosition();
		handleIsInternalLink();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.pageMenu.{$action}{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PageMenuItemList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.pageMenu.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='PageMenuItemAdd'}{/link}{else}{link controller='PageMenuItemEdit' id=$menuItem->menuItemID}{/link}{/if}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.pageMenu.data{/lang}</legend>
			
			<dl{if $errorField == 'menuPosition'} class="formError"{/if}>
				<dt><label for="menuPosition">{lang}wcf.acp.pageMenu.menuPosition{/lang}</label></dt>
				<dd>
					<select name="menuPosition" id="menuPosition">
						<option value="header"{if $menuPosition == 'header'} selected="selected"{/if}>{lang}wcf.acp.pageMenu.menuPosition.header{/lang}</option>
						<option value="footer"{if $menuPosition == 'footer'} selected="selected"{/if}>{lang}wcf.acp.pageMenu.menuPosition.footer{/lang}</option>
					</select>
					{if $errorField == 'menuPosition'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.pageMenu.menuPosition.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl id="parentMenuItemContainer"{if $errorField == 'parentMenuItem'} class="formError"{/if}>
				<dt><label for="parentMenuItem">{lang}wcf.acp.pageMenu.parentMenuItem{/lang}</label></dt>
				<dd>
					<select name="parentMenuItem" id="parentMenuItem">
						<option value=""{if $parentMenuItem == ''} selected="selected"{/if}>{lang}wcf.global.noSelection{/lang}</option>
						{foreach from=$availableParentMenuItems item=availableParentMenuItem}
							<option value="{$availableParentMenuItem->menuItem}"{if $parentMenuItem == $availableParentMenuItem->menuItem} selected="selected"{/if}>{$availableParentMenuItem}</option>
						{/foreach}
					</select>
					{if $errorField == 'parentMenuItem'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.pageMenu.parentMenuItem.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'pageMenuItem'} class="formError"{/if}>
				<dt><label for="pageMenuItem">{lang}wcf.acp.pageMenu.pageMenuItem{/lang}</label></dt>
				<dd>
					<input type="text" name="pageMenuItem" id="pageMenuItem" value="{$i18nPlainValues['pageMenuItem']}" class="long" required="required" />
					{if $errorField == 'pageMenuItem'}
						<small class="innerError">
							{if $errorType == 'multilingual'}
								{lang}wcf.global.form.error.multilingual{/lang}
							{else}
								{lang}wcf.acp.pageMenu.pageMenuItem.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
					
					{include file='multipleLanguageInputJavascript' elementIdentifier='pageMenuItem' forceSelection=true}
				</dd>
			</dl>
			
			{event name='dataFields'}
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.pageMenu.link{/lang}</legend>
			
			<dl>
				<dt></dt>
				<dd class="floated">
					<label><input type="radio" name="isInternalLink" value="1"{if $isInternalLink} checked="checked"{/if} /> {lang}wcf.acp.pageMenu.link.internal{/lang}</label>
					<label><input type="radio" name="isInternalLink" value="0"{if !$isInternalLink} checked="checked"{/if} /> {lang}wcf.acp.pageMenu.link.external{/lang}</label>
				</dd>
			</dl>
			
			<dl id="menuItemControllerContainer"{if $errorField == 'menuItemController'} class="formError"{/if}>
				<dt><label for="menuItemController">{lang}wcf.acp.pageMenu.menuItemController{/lang}</label></dt>
				<dd>
					<input type="text" name="menuItemController" id="menuItemController" value="{$menuItemController}" class="long" />
					{if $errorField == 'menuItemController'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.pageMenu.menuItemController.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.pageMenu.menuItemController.description{/lang}</small>
				</dd>
			</dl>
			
			<dl id="menuItemParametersContainer"{if $errorField == 'menuItemParameters'} class="formError"{/if}>
				<dt><label for="menuItemParameters">{lang}wcf.acp.pageMenu.menuItemParameters{/lang}</label></dt>
				<dd>
					<input type="text" name="menuItemParameters" id="menuItemParameters" value="{$menuItemParameters}" class="long" />
					{if $errorField == 'menuItemParameters'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.pageMenu.menuItemParameters.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl id="menuItemLinkContainer"{if $errorField == 'menuItemLink'} class="formError"{/if}>
				<dt><label for="menuItemLink">{lang}wcf.acp.pageMenu.menuItemLink{/lang}</label></dt>
				<dd>
					<input type="text" name="menuItemLink" id="menuItemLink" value="{$menuItemLink}" class="long" />
					{if $errorField == 'menuItemLink'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.pageMenu.menuItemLink.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
					
					{include file='multipleLanguageInputJavascript' elementIdentifier='menuItemLink' forceSelection=false}
				</dd>
			</dl>
			
			{event name='linkFields'}
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.pageMenu.advanced{/lang}</legend>
			
			<dl>
				<dt><label for="showOrder">{lang}wcf.acp.pageMenu.showOrder{/lang}</label></dt>
				<dd>
					<input type="number" name="showOrder" id="showOrder" value="{@$showOrder}" class="tiny" min="0" />
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="isDisabled" id="isDisabled" value="1"{if $isDisabled} checked="checked"{/if} /> <span>{lang}wcf.acp.pageMenu.isDisabled{/lang}</span></label>
				</dd>
			</dl>
			
			{event name='advancedFields'}
		</fieldset>
		
		{event name='fields'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
