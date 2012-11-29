{include file='header' pageTitle='wcf.acp.option.category.'|concat:$category->categoryName}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
		new WCF.ACP.Options();
		
		{if $optionName}
			var $option = $('#' + $.wcfEscapeID('{$optionName}'));
			new WCF.PeriodicalExecuter(function(pe) {
				pe.stop();
				
				var $scrollHandler = new WCF.Effect.Scroll();
				$scrollHandler.scrollTo($option, true);
				$option.focus();
			}, 200);
		{/if}
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.option.category.{$category->categoryName}{/lang}</h1>
		{hascontent}<h2>{content}{lang __optional=true}wcf.acp.option.category.{$category->categoryName}.description{/lang}{/content}</h2>{/hascontent}
	</hgroup>
</header>

{if $success|isset}
	<p class="success">{lang}wcf.global.form.edit.success{/lang}</p>
{/if}

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="{link controller='Option' id=$category->categoryID}{/link}">
	<div class="tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
		<nav class="tabMenu">
			<ul>
				{foreach from=$optionTree item=categoryLevel1}
					<li><a href="#{@$categoryLevel1[object]->categoryName}" title="{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}">{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</a></li>
				{/foreach}
			</ul>
		</nav>
		
		{foreach from=$optionTree item=categoryLevel1}
			<div id="{@$categoryLevel1[object]->categoryName}" class="container containerPadding shadow hidden tabMenuContent">
				{if $categoryLevel1[options]|count}
					<fieldset>
						<legend>{lang}wcf.acp.option.category.{$categoryLevel1[object]->categoryName}{/lang}</legend>
						{include file='optionFieldList' options=$categoryLevel1[options] langPrefix='wcf.acp.option.'}
					</fieldset>
				{/if}
				
				{if $categoryLevel1[categories]|count}
					{foreach from=$categoryLevel1[categories] item=categoryLevel2}
						<fieldset>
							<legend>{lang}wcf.acp.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</legend>
							{hascontent}<small>{content}{lang __optional=true}wcf.acp.option.category.{$categoryLevel2[object]->categoryName}.description{/lang}{/content}</small>{/hascontent}
							
							{include file='optionFieldList' options=$categoryLevel2[options] langPrefix='wcf.acp.option.'}
						</fieldset>
					{/foreach}
				{/if}
			</div>
		{/foreach}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" name="__submit" accesskey="s" />
	</div>
</form>

{include file='footer'}
