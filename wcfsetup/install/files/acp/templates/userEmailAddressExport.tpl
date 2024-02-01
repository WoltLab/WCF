{include file='header' pageTitle='wcf.acp.user.exportEmailAddress'}

<script data-relocate="true">
	$(function() {
		new WCF.Option.Handler();
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.exportEmailAddress{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='shared_formError'}

<form method="post" action="{link controller='UserEmailAddressExport'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.exportEmailAddress.markedUsers{/lang}</h2>
		
		<div>
			{implode from=$users item=$user}<a href="{link controller='UserEdit' id=$user->userID}{/link}">{$user}</a>{/implode}
		</div>
		
		{event name='markedUserFields'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.exportEmailAddress.format{/lang}</h2>
		
		<dl>
			<dt><label>{lang}wcf.acp.user.exportEmailAddress.fileType{/lang}</label></dt>
			<dd>
				<label><input type="radio" name="fileType" value="csv" {if $fileType == 'csv'}checked {/if}class="jsEnablesOptions" data-disable-options="[ ]" data-enable-options="[ 'separatorDiv', 'textSeparatorDiv' ]"> {lang}wcf.acp.user.exportEmailAddress.fileType.csv{/lang}</label>
				<label><input type="radio" class="jsEnablesOptions" name="fileType" value="xml" {if $fileType == 'xml'}checked {/if}data-disable-options="[ 'separatorDiv', 'textSeparatorDiv' ]" data-enable-options="[ ]"> {lang}wcf.acp.user.exportEmailAddress.fileType.xml{/lang}</label>
			</dd>
		</dl>
		
		<dl id="separatorDiv">
			<dt><label for="separator">{lang}wcf.acp.user.exportEmailAddress.separator{/lang}</label></dt>
			<dd>
				<input type="text" id="separator" name="separator" value="{$separator}" class="medium">
			</dd>
		</dl>
		
		<dl id="textSeparatorDiv">
			<dt><label for="textSeparator">{lang}wcf.acp.user.exportEmailAddress.textSeparator{/lang}</label></dt>
			<dd>
				<input type="text" id="textSeparator" name="textSeparator" value="{$textSeparator}" class="medium">
			</dd>
		</dl>
		
		{event name='emailAddressFormatFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
