{foreach from=$optionTree item=categoryLevel1}
	{foreach from=$categoryLevel1[categories] item=categoryLevel2}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</h2>
			
			{if $categoryLevel2[object]->categoryName == 'profile.personal' && MODULE_USER_RANK && $__wcf->session->getPermission('user.profile.canEditUserTitle')}
				<dl>
					<dt><label for="__userTitle">{lang}wcf.user.userTitle{/lang}</label></dt>
					<dd>
						<input type="text" id="__userTitle" name="values[__userTitle]" value="{$__userTitle}" class="long" maxlength="{@USER_TITLE_MAX_LENGTH}">
						{if $errorType[__userTitle]|isset}
							<small class="innerError">
								{lang}wcf.user.userTitle.error.{@$errorType[__userTitle]}{/lang}
							</small>
						{/if}
						<small>{lang}wcf.user.userTitle.description{/lang}</small>
					</dd>
				</dl>
			{/if}
			
			{include file='userProfileOptionFieldList' options=$categoryLevel2[options] langPrefix='wcf.user.option.'}
		</section>
	{/foreach}
{/foreach}

<div class="formSubmit">
	<button class="buttonPrimary" accesskey="s" data-type="save">{lang}wcf.global.button.save{/lang}</button>
	<button data-type="restore">{lang}wcf.global.button.cancel{/lang}</button>
</div>

<script data-relocate="true">
	$(function() {
		new WCF.Option.Handler();
	});
</script>