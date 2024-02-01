{include file='header' pageTitle='wcf.acp.bbcode.mediaProvider.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.bbcode.mediaProvider.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='BBCodeMediaProviderList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.bbcode.mediaProvider.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='BBCodeMediaProviderAdd'}{/link}{else}{link controller='BBCodeMediaProviderEdit' object=$mediaProvider}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'title'} class="formError"{/if}>
			<dt><label for="title">{lang}wcf.acp.bbcode.mediaProvider.title{/lang}</label></dt>
			<dd>
				<input type="text" id="title" name="title" value="{$title}" required autofocus class="long">
				{if $errorField == 'title'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.bbcode.mediaProvider.title.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'regex'} class="formError"{/if}>
			<dt><label for="regex">{lang}wcf.acp.bbcode.mediaProvider.regex{/lang}</label></dt>
			<dd>
				<textarea id="regex" name="regex" cols="40" rows="5" required>{$regex}</textarea>
				{if $errorField == 'regex'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.bbcode.mediaProvider.regex.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.bbcode.mediaProvider.regex.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'html'} class="formError"{/if}>
			<dt><label for="html">{lang}wcf.acp.bbcode.mediaProvider.html{/lang}</label></dt>
			<dd>
				<textarea id="html" name="html" cols="40" rows="10">{$html}</textarea>
				{if $errorField == 'html'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.bbcode.mediaProvider.html.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.bbcode.mediaProvider.html.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'className'} class="formError"{/if}>
			<dt><label for="className">{lang}wcf.acp.bbcode.mediaProvider.className{/lang}</label></dt>
			<dd>
				<input type="text" id="className" name="className" value="{$className}" pattern="^\\?([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\)*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$" class="long">
				{if $errorField == 'className'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.bbcode.mediaProvider.className.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
