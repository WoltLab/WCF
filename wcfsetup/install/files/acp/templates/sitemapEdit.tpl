{include file='header' pageTitle='wcf.acp.sitemap.edit'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.sitemap.edit{/lang}</h1>
		<p class="contentHeaderDescription">{lang}wcf.acp.sitemap.objectType.{$objectType->objectType}{/lang}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='SitemapList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.maintenance.sitemap{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formError'}

{if $success|isset}
	<woltlab-core-notice type="success">{lang}wcf.global.success.edit{/lang}</woltlab-core-notice>
{/if}

<form method="post" action="{link controller='SitemapEdit'}objectType={$objectType->objectType}{/link}">
	<div class="section">
		<dl{if $errorField == 'changeFreq'} class="formError"{/if}>
			<dt><label for="changeFreq">{lang}wcf.acp.sitemap.changeFreq{/lang}</label></dt>
			<dd>
				<select id="changeFreq" name="changeFreq">
					{foreach from=$validChangeFreq item="value"}
						<option value="{$value}"{if $value == $changeFreq} selected="selected"{/if}>{lang}wcf.acp.sitemap.changeFreq.{$value}{/lang}</option>
					{/foreach}
				</select>
				{if $errorField == 'changeFreq'}
					<small class="innerError">
						{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'rebuildTime'} class="formError"{/if}>
			<dt><label for="rebuildTime">{lang}wcf.acp.sitemap.rebuildTime{/lang}</label></dt>
			<dd>
				<div class="inputAddon">
					<input type="number" id="rebuildTime" name="rebuildTime" min="0" value="{$rebuildTime}" class="short">
					<span class="inputSuffix">{lang}wcf.acp.option.suffix.seconds{/lang}</span>
				</div>
				<small>{lang}wcf.acp.sitemap.rebuildTime.description{/lang}</small>
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" id="isDisabled" name="isDisabled" value="1"{if $isDisabled} checked="checked"{/if} /> {lang}wcf.acp.sitemap.isDisabled{/lang}</label>
			</dd>
		</dl>
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
