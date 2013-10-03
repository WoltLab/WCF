{include file='header' pageTitle='wcf.acp.style.importStyle'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.style.importStyle{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.add{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='StyleList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.style.list{/lang}</span></a></li>
				
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='StyleImport'}{/link}" enctype="multipart/form-data">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.style.import.source{/lang}</legend>
			
			<dl{if $errorField == 'source'} class="formError"{/if}>
				<dt><label for="source">{lang}wcf.acp.style.import.source.upload{/lang}</label></dt>
				<dd>
					<input type="file" id="source" name="source" value="" />
					{if $errorField == 'source'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.style.import.source.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.style.import.source.upload.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='sourceFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}