{include file='header'}

{if $errorField == 'search'}
	<woltlab-core-notice type="error">{lang}wcf.user.search.error.noMatches{/lang}</woltlab-core-notice>
{else}
	{include file='shared_formError'}
{/if}

<form method="post" action="{link controller='UserSearch'}{/link}">
	<div class="section">
		<dl>
			<dt><label for="searchUsername">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<input type="text" id="searchUsername" name="username" value="{$username}" class="medium">
			</dd>
		</dl>
		
		{event name='generalFields'}
	</div>
	
	{if !$optionTree|empty}
		{foreach from=$optionTree[0][categories] item=category}
			<section class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$category[object]->categoryName}{/lang}</h2>
					{hascontent}<p class="sectionDescription">{content}{lang __optional=true}wcf.user.option.category.{@$category[object]->categoryName}.description{/lang}{/content}</p>{/hascontent}
				</header>
				{include file='userOptionFieldList' options=$category[options] langPrefix='wcf.user.option.' isSearchMode=true}
			</section>
		{/foreach}
	{/if}
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/User/Search/Input'], (UiUserSearchInput) => {
		new UiUserSearchInput(document.getElementById('searchUsername'));
	});
</script>

{include file='footer'}
