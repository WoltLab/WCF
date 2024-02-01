{include file='header' pageTitle='wcf.acp.option.category.'|concat:$category->categoryName}

{event name='javascriptInclude'}

<script data-relocate="true">
	$(function() {
		new WCF.Option.Handler();
	});

	document.addEventListener("DOMContentLoaded", () => {
		const searchParams = new URLSearchParams(document.location.search);
		const optionName = searchParams.get("optionName");
		if (optionName) {
			window.setTimeout(() => {
				const element = document.querySelector(`#${ optionName }, label[for="${ optionName }"]`);
				if (!element) {
					return;
				}

				const dl = element.closest("dl");
				if (!dl) {
					return;
				}

				const elementTop = Math.trunc(element.getBoundingClientRect().top);
				window.scrollTo(0, elementTop - 50);

				try {
					element.focus();
				} catch {}
				
				window.setTimeout(() => {
					const label = dl.querySelector("dt label");
					label.classList.add("hightlightOptionLabel");
					label.addEventListener("transitionEnd",() => label.classList.remove("hightlightOptionLabel"), { once: true });
				}, 500);
			}, 1_000);
		}
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
	<woltlab-core-notice type="success">{lang}wcf.global.success.edit{/lang}</woltlab-core-notice>
{/if}

{include file='shared_formError'}

<form method="post" action="{link controller='Option' id=$category->categoryID}{/link}" enctype="multipart/form-data">
	{*
		fake fields are a workaround for chrome autofill picking the wrong fields
		taken from http://stackoverflow.com/a/15917221
	*}
	<div style="display: none;">
		<input type="text" name="fakeusernameremembered">
		<input type="password" name="fakepasswordremembered">
	</div>
	
	<div class="section tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
		<nav class="tabMenu">
			<ul>
				{foreach from=$optionTree item=categoryLevel1}
					<li><a href="#category_{$categoryLevel1[object]->categoryName|rawurlencode}" title="{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}">{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</a></li>
				{/foreach}
			</ul>
		</nav>
		
		{foreach from=$optionTree item=categoryLevel1}
			<div id="category_{@$categoryLevel1[object]->categoryName}" class="hidden tabMenuContent">
				{if $categoryLevel1[options]|count}
					<div class="section">
						{if $categoryLevel1[object]->categoryName === 'module.development'}
							<woltlab-core-notice type="warning">{lang}wcf.acp.option.category.module.development.notice{/lang}</woltlab-core-notice>
						{/if}
						
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
		{csrfToken}
	</div>
</form>

{include file='__optionEmailSmtpTest'}
{include file='__optionRewriteTest'}
{include file='__optionRewriteRules'}

{include file='footer'}
