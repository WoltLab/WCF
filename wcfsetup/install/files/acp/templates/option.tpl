{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
		new WCF.ACP.Options();
	});
	//]]>
</script>

<header class="wcf-container wcf-mainHeading">
	<img src="{@$__wcf->getPath()}icon/options1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.option.category.{$category->categoryName}{/lang}</h1>
		{hascontent}<h2>{content}{lang __optional=true}wcf.acp.option.category.{$category->categoryName}.description{/lang}{/content}</h2>{/hascontent}
	</hgroup>
</header>

{if $success|isset}
	<p class="wcf-success">{lang}wcf.global.form.edit.success{/lang}</p>
{/if}

{if $errorField}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="{link controller='Option' id=$category->categoryID}{/link}">
	<div class="wcf-tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
		<nav class="wcf-tabMenu">
			<ul>
				{foreach from=$optionTree item=categoryLevel1}
					<li><a href="#{@$categoryLevel1[object]->categoryName}" title="{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}">{*<span>*}{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}{*</span>*}</a></li>
				{/foreach}
			</ul>
		</nav>
		
		{foreach from=$optionTree item=categoryLevel1}
			<div id="{@$categoryLevel1[object]->categoryName}" class="wcf-border wcf-tabMenuContent hidden">
				<hgroup class="wcf-subHeading">
					<h1>{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</h1>
					<h2>{lang __optional=true}wcf.acp.option.category.{$categoryLevel1[object]->categoryName}.description{/lang}</h2>
				</hgroup>
				
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
	
	<div class="wcf-formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}
