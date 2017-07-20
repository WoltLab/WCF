{include file='header' pageTitle='wcf.acp.option.category.'|concat:$category->categoryName}

{event name='javascriptInclude'}

<script data-relocate="true">
	$(function() {
		new WCF.Option.Handler();
		
		{if $optionName}
			setTimeout(function() {
				var option = elById('{$optionName}');
				var div = elCreate('div');
				div.id = 'wcfOptionAnchor';
				div.style.setProperty('position', 'absolute', '');
				div.style.setProperty('top', (option.closest('dl').offsetTop - 60) + 'px', '');
				document.body.appendChild(div);
				div.scrollIntoView({ behavior: 'smooth' });
				
				option.focus();
			}, 200);
		{/if}
	});
	
	{event name='javascriptInit'}
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.option.category.{$category->categoryName}{/lang}</h1>
		{hascontent}<p class="contentHeaderDescription">{content}{lang __optional=true}wcf.acp.option.category.{$category->categoryName}.description{/lang}{/content}</p>{/hascontent}
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

{include file='formError'}

<form method="post" action="{link controller='Option' id=$category->categoryID}{/link}" enctype="multipart/form-data">
	{*
		fake fields are a workaround for chrome autofill picking the wrong fields
		taken from http://stackoverflow.com/a/15917221
	*}
	<input style="display:none" type="text" name="fakeusernameremembered">
	<input style="display:none" type="password" name="fakepasswordremembered">
	
	<div class="section tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
		<nav class="tabMenu">
			<ul>
				{foreach from=$optionTree item=categoryLevel1}
					{capture assign=__categoryName}category_{$categoryLevel1[object]->categoryName}{/capture}
					<li><a href="{@$__wcf->getAnchor($__categoryName)}" title="{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}">{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</a></li>
				{/foreach}
			</ul>
		</nav>
		
		{foreach from=$optionTree item=categoryLevel1}
			<div id="category_{@$categoryLevel1[object]->categoryName}" class="hidden tabMenuContent">
				{if $categoryLevel1[options]|count}
					<div class="section">
						{if $categoryLevel1[object]->categoryName === 'module.development'}<p class="warning">{lang}wcf.acp.option.category.module.development.notice{/lang}</p>{/if}
						
						{include file='optionFieldList' options=$categoryLevel1[options] langPrefix='wcf.acp.option.'}
					</div>
				{/if}
				
				{if $categoryLevel1[categories]|count}
					{foreach from=$categoryLevel1[categories] item=categoryLevel2}
						<section class="section">
							<header class="sectionHeader">
								<h2 class="sectionTitle">{lang}wcf.acp.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</h2>
								{hascontent}<p class="sectionDescription">{content}{lang __optional=true}wcf.acp.option.category.{$categoryLevel2[object]->categoryName}.description{/lang}{/content}</p>{/hascontent}
							</header>
							
							{include file='optionFieldList' options=$categoryLevel2[options] langPrefix='wcf.acp.option.'}
						</section>
					{/foreach}
				{/if}
			</div>
		{/foreach}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" name="__submit" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='__optionEmailSmtpTest'}
{include file='__optionRewriteTest'}

{include file='footer'}
