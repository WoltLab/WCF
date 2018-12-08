{include file='header' pageTitle='wcf.acp.style.globalValues'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.style.globalValues{/lang}</h1>
		<p class="contentHeaderDescription">{lang}wcf.acp.style.globalValues.description{/lang}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='StyleList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.style.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success{/lang}</p>
{/if}

<form method="post" action="{link controller='StyleGlobalValues'}{/link}">
	<div class="section">
		<dl>
			<dt>{lang}wcf.acp.style.globalValues.input{/lang}</dt>
			<dd>
				<div dir="ltr">
					<textarea id="styles" rows="20" cols="40" name="styles" style="visibility: hidden">{$styles}</textarea>
				</div>
			</dd>
		</dl>
		{include file='codemirror' codemirrorMode='text/x-less' codemirrorSelector='#styles'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
