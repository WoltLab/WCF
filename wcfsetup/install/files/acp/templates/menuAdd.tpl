{include file='header' pageTitle='wcf.acp.menu.'|concat:$action}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.menu.{$action}{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='MenuList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.list{/lang}</span></a></li>
				
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='MenuAdd'}{/link}{else}{link controller='MenuEdit' id=$menuID}{/link}{/if}">
	<section class="marginTop">
		<h1>{lang}wcf.global.form.data{/lang}</h1>
			
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
		
		{event name='dataFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
