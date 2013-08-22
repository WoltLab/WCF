{include file='header' pageTitle='wcf.acp.user.option.setDefaults'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.option.setDefaults{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if !$optionTree|empty}
	<form method="post" action="{link controller='UserOptionSetDefaults'}{/link}">
		<div class="container containerPadding marginTop">
			<fieldset>
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="applyChangesToExistingUsers" value="1" {if $applyChangesToExistingUsers}checked="checked" {/if}/> {lang}wcf.acp.user.option.setDefaults.applyChangesToExistingUsers{/lang}</label>
						<small>{lang}wcf.acp.user.option.setDefaults.applyChangesToExistingUsers.description{/lang}</small>	
					</dd>
				</dl>
			</fieldset>
			
			{foreach from=$optionTree[0][categories] item=optionCategory}
				<fieldset>
					<legend>{lang}wcf.user.option.category.{@$optionCategory[object]->categoryName}{/lang}</legend>
					
					{include file='optionFieldList' options=$optionCategory[options] langPrefix='wcf.user.option.'}
					
					{if $optionCategory[categories]|count}
						{foreach from=$optionCategory[categories] item=optionCategory2}
							{include file='optionFieldList' options=$optionCategory2[options] langPrefix='wcf.user.option.'}
						{/foreach}
					{/if}
				</fieldset>
			{/foreach}
			
			{event name='fieldsets'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		</div>
	</form>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
