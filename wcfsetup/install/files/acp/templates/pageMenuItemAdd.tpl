{include file='header' pageTitle='wcf.acp.pageMenu.'|concat:$action}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		var $isDisabled = $('#isDisabled');
		var $isLandingPageContainer = $('#isLandingPageContainer');
		var $menuPosition = $('#menuPosition');
		var $parentMenuItemContainer = $('#parentMenuItemContainer');
		
		function handleMenuPosition() {
			if ($menuPosition.val() === 'header') {
				$parentMenuItemContainer.show();
				
				if (!$isDisabled.is(':checked')) {
					$isLandingPageContainer.show();
				}
			}
			else {
				$parentMenuItemContainer.hide();
				$isLandingPageContainer.hide();
			}
		}
		
		function handleIsDisabled() {
			if ($isDisabled.is(':checked')) {
				$isLandingPageContainer.hide();
			}
			else {
				$isLandingPageContainer.show();
			}
		}
		
		$isDisabled.change(handleIsDisabled);
		$menuPosition.change(handleMenuPosition);
		
		handleIsDisabled();
		handleMenuPosition();
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.pageMenu.{$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.form.{$action}.success{/lang}</p>	
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PageMenuItemList'}{/link}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/list.svg" alt="" /> <span>{lang}wcf.acp.pageMenu.list{/lang}</span></a></li>
		</ul>
	</nav>
</div>

<div class="container containerPadding marginTop">
	<form method="post" action="{if $action == 'add'}{link controller='PageMenuItemAdd'}{/link}{else}{link controller='PageMenuItemEdit' id=$menuItem->menuItemID}{/link}{/if}">
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
						<option value=""{if $parentMenuItem == ''} selected="selected"{/if}></option>
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
					<input type="text" name="pageMenuItem" id="pageMenuItem" value="{$pageMenuItem}" class="long" required="required" />
					{if $errorField == 'pageMenuItem'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.pageMenu.pageMenuItem.error.{$errorType}{/lang}
							{/if}
						</small>
					{/if}
					
					{include file='multipleLanguageInputJavascript' elementIdentifier='pageMenuItem' forceSelection=true}
				</dd>
			</dl>
			
			<dl{if $errorField == 'menuItemLink'} class="formError"{/if}>
				<dt><label for="menuItemLink">{lang}wcf.acp.pageMenu.menuItemLink{/lang}</label></dt>
				<dd>
					<input type="text" name="menuItemLink" id="menuItemLink" value="{$menuItemLink}" class="long" required="required" />
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
			
			<dl>
				<dd>
					<label><input type="checkbox" name="newWindow" id="newWindow" value="1"{if $newWindow} checked="checked"{/if} /> <span>{lang}wcf.acp.pageMenu.newWindow{/lang}</span></label>
				</dd>
			</dl>
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.pageMenu.advanced{/lang}</legend>
			
			<dl>
				<dt><label for="showOrder">{lang}wcf.acp.pageMenu.showOrder{/lang}</label></dt>
				<dd>
					<input type="number" name="showOrder" id="showOrder" value="{@$showOrder}" class="long" min="0" />
				</dd>
			</dl>
			
			<dl>
				<dd>
					<label><input type="checkbox" name="isDisabled" id="isDisabled" value="1"{if $isDisabled} checked="checked"{/if} /> <span>{lang}wcf.acp.pageMenu.isDisabled{/lang}</span></label>
				</dd>
			</dl>
			
			<dl id="isLandingPageContainer">
				<dd>
					<label><input type="checkbox" name="isLandingPage" id="isLandingPage" value="1"{if $isLandingPage} checked="checked"{/if} /> <span>{lang}wcf.acp.pageMenu.isLandingPage{/lang}</span></label>
					<small>{lang}wcf.acp.pageMenu.isLandingPage.description{/lang}</small>
				</dd>
			</dl>
		</fieldset>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" />
		</div>
	</form>
</div>

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='PageMenuItemList'}{/link}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/list.svg" alt="" /> <span>{lang}wcf.acp.pageMenu.list{/lang}</span></a></li>
		</ul>
	</nav>
</div>

{include file='footer'}
