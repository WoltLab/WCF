{if $box->position == 'sidebarLeft' || $box->position == 'sidebarRight'}
<p>{lang}wcf.user.login.register.teaser{/lang}</p>

<div class="formSubmit"><a href="{link controller='Register'}{/link}" class="button buttonPrimary">{lang}wcf.user.login.register.registerNow{/lang}</a></div>
{elseif $box->position == 'contentTop' || $box->position == 'contentBottom'}
<div class="info">
	<p>{lang}wcf.user.login.register.teaser{/lang}</p>

	<div class="formSubmit">
		<a href="{link controller='Register'}{/link}" class="button buttonPrimary">{lang}wcf.user.login.register.registerNow{/lang}</a>
		<a href="{link controller='Login'}{/link}" class="button buttonPrimary">{lang}wcf.user.button.login{/lang}</a>
	</div>
</div>
{/if}
