{capture assign='pageTitle'}{lang}wcf.user.security.multifactor.authentication{/lang}{/capture}

{include file='header' __isLogin=true}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.user.security.multifactor.authentication{/lang}</h1>
		<p class="contentHeaderDescription">{lang}wcf.user.security.multifactor.authentication.description{/lang}</p>
	</div>
</header>

{@$form->getHtml()}

<div class="authOtherOptionButtons" hidden>
	<div class="authOtherOptionButtons__separator">
		{lang}wcf.user.security.multifactor.otherOptions{/lang}
	</div>

	<ul class="authOtherOptionButtons__buttonList">
		{foreach from=$setups item='method'}
			{if $setup->getId() != $method->getId()}
				<li>
					<a
						href="{link controller='MultifactorAuthentication' object=$method}{/link}"
						class="button authOtherOptionButtons__button"
					>
						{if $method->getObjectType()->icon}
							{icon size=24 name=$method->getObjectType()->icon}
						{else}
							{icon size=24 name='lock'}
						{/if}
						<span>{lang}wcf.user.security.multifactor.{$method->getObjectType()->objectType}{/lang}</span>
					</a>
				</li>
			{/if}
		{/foreach}
	</ul>
</div>

<script data-eager="true">
	{
		const container = document.querySelector('.authOtherOptionButtons');
		document.getElementById('multifactorAuthentication').append(container);
		container.hidden = false;
	}
</script>

<script data-relocate="true">
	{
		const code = document.getElementById('code') ?? document.getElementById('onetimecode');
		if (code) {
			code.addEventListener('input', () => {
				if (code.value.length == code.maxLength) {
					code.form.submit();	
				}
			});
		}
	}
</script>

{include file='footer'}
