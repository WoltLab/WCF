{include file='header'}
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
		new WCF.ACP.Options();
	});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/optionL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.option.category.{$category->categoryName}{/lang}</h2>
		<p>{lang}wcf.acp.option.category.{$category->categoryName}.description{/lang}</p>
	</div>
</div>

{if $success|isset}
	<p class="success">{lang}wcf.acp.option.success{/lang}</p>
{/if}

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=Option&amp;categoryID={@$category->categoryID}">
	<div class="tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
		<ul class="tabMenu">
			{foreach from=$optionTree item=categoryLevel1}
				<li><a href="#{@$categoryLevel1[object]->categoryName}">{*<span>*}{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}{*</span>*}</a></li>
			{/foreach}
		</ul>
		
		{foreach from=$optionTree item=categoryLevel1}
			<div class="border tabMenuContent hidden" id="{@$categoryLevel1[object]->categoryName}">
				<div class="container-1">
					<h3 class="subHeadline">{lang}wcf.acp.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</h3>
					<p class="description">{lang}wcf.acp.option.category.{$categoryLevel1[object]->categoryName}.description{/lang}</p>
					
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
								<p class="description">{lang}wcf.acp.option.category.{$categoryLevel2[object]->categoryName}.description{/lang}</p>
								
								{include file='optionFieldList' options=$categoryLevel2[options] langPrefix='wcf.acp.option.'}
							</fieldset>
						{/foreach}
					{/if}
				</div>
			</div>
		{/foreach}
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		{@SID_INPUT_TAG}
 		<input type="hidden" id="activeTabMenuItem" name="activeTabMenuItem" value="{$activeTabMenuItem}" />
	</div>
</form>

{include file='footer'}