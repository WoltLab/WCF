{include file='header' pageTitle='wcf.acp.devtools.project.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.devtools.project.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='DevtoolsProjectList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.devtools.project.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<p class="info">{lang}wcf.acp.devtools.project.introduction{/lang}</p>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='DevtoolsProjectAdd'}{/link}{else}{link controller='DevtoolsProjectEdit' id=$objectID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'name'} class="formError"{/if}>
			<dt><label for="name">{lang}wcf.acp.devtools.project.name{/lang}</label></dt>
			<dd>
				<input type="text" id="name" name="name" value="{$name}" required autofocus class="long">
				{if $errorField == 'name'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.devtools.project.name.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'path'} class="formError"{/if}>
			<dt><label for="path">{lang}wcf.acp.devtools.project.path{/lang}</label></dt>
			<dd>
				<input type="text" id="path" name="path" value="{$path}" class="long">
				{if $errorField == 'path'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.{@$errorType}{/lang}
						{else}
							{lang}wcf.acp.devtools.project.path.error.{@$errorType}{/lang}
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
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
