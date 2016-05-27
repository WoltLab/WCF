{include file='header' pageTitle='wcf.acp.user.option.setDefaults'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.option.setDefaults{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

{if !$optionTree|empty}
	<form method="post" action="{link controller='UserOptionSetDefaults'}{/link}">
		<div class="section">
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="applyChangesToExistingUsers" value="1" {if $applyChangesToExistingUsers}checked="checked" {/if}/> {lang}wcf.acp.user.option.setDefaults.applyChangesToExistingUsers{/lang}</label>
					<small>{lang}wcf.acp.user.option.setDefaults.applyChangesToExistingUsers.description{/lang}</small>	
				</dd>
			</dl>
		</div>
		
		{foreach from=$optionTree[0][categories] item=optionCategory}
			<section class="section">
				<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$optionCategory[object]->categoryName}{/lang}</h2>
				
				{include file='optionFieldList' options=$optionCategory[options] langPrefix='wcf.user.option.'}
				
				{if $optionCategory[categories]|count}
					{foreach from=$optionCategory[categories] item=optionCategory2}
						{include file='optionFieldList' options=$optionCategory2[options] langPrefix='wcf.user.option.'}
					{/foreach}
				{/if}
			</section>
		{/foreach}
		
		{event name='sections'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
