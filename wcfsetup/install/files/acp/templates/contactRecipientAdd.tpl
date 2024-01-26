{include file='header' pageTitle='wcf.acp.contact.recipient.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.contact.recipient.{@$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='ContactSettings'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.contact.settings{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form method="post" action="{if $action === 'add'}{link controller='ContactRecipientAdd'}{/link}{else}{link controller='ContactRecipientEdit' id=$recipientID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField === 'name'} class="formError"{/if}>
			<dt><label for="name">{lang}wcf.acp.contact.recipient.name{/lang}</label></dt>
			<dd>
				<input type="text" id="name" name="name" value="{$i18nPlainValues['name']}" autofocus class="medium">
				{if $errorField === 'name'}
					<small class="innerError">
						{if $errorType === 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType === 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{else}
							{lang}wcf.acp.contact.recipient.name.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}

				{include file='shared_multipleLanguageInputJavascript' elementIdentifier='name' forceSelection=false}
			</dd>
		</dl>
		
		<dl{if $errorField === 'email'} class="formError"{/if}>
			<dt><label for="email">{lang}wcf.user.email{/lang}</label></dt>
			<dd>
				{if $action === 'edit' && $recipient->isAdministrator}
					<span>{$i18nPlainValues['email']}</span>
					<input type="hidden" name="email" value="{$i18nPlainValues['email']}">
				{else}
					<input type="text" id="email" name="email" value="{$i18nPlainValues['email']}" class="medium">
					{if $errorField === 'email'}
						<small class="innerError">
							{if $errorType === 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType === 'multilingual'}
								{lang}wcf.global.form.error.multilingual{/lang}
							{else}
								{lang}wcf.user.email.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}

					{include file='shared_multipleLanguageInputJavascript' elementIdentifier='email' forceSelection=false}
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt><label for="showOrder">{lang}wcf.global.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" class="tiny" min="0">
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="isDisabled" value="1"{if $isDisabled} checked{/if}> {lang}wcf.acp.contact.recipient.isDisabled{/lang}</label>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="action" value="{$action}">
		{csrfToken}
	</div>
</form>

{include file='footer'}
