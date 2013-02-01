{include file='header' pageTitle='wcf.acp.style.importStyle'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.style.importStyle{/lang}</h1>
	</hgroup>
</header>

{if $success|isset}
	<p class="success">{lang}wcf.global.form.add.success{/lang}</p>	
{/if}

{hascontent}
	<div class="contentNavigation">
		<nav>
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.style.canDeleteStyle') || $__wcf->session->getPermission('admin.style.canEditStyle')}
						<li><a href="{link controller='StyleList'}{/link}" title="{lang}wcf.acp.menu.link.style.list{/lang}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.style.list{/lang}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	</div>
{/hascontent}

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
	</div>
</form>

{include file='footer'}