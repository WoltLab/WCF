{include file='header' __disableAds=true}

{include file='formError'}

{if $errorMessage|isset}
	<p class="error" role="alert">{@$errorMessage}</p>
{/if}

<form method="post" action="{link controller='TagSearch'}{/link}">
	<div class="section tabMenuContainer staticTabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="{link controller='Search'}{/link}">{lang}wcf.search.type.keywords{/lang}</a></li>
				<li class="active"><a href="{link controller='TagSearch'}{/link}">{lang}wcf.search.type.tags{/lang}</a></li>
				
				{event name='tabMenuTabs'}
			</ul>
		</nav>
		
		<div class="tabMenuContent">
			<div class="section jsOnly">
				{include file='messageFormMultilingualism'}
				
				<dl>
					<dt><label for="tagSearchInput">{lang}wcf.tagging.tags{/lang}</label></dt>
					<dd>
						<input id="tagSearchInput" type="text" value="" class="long">
						{if $errorField === 'tags'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.tagging.tags.error.{$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<script data-relocate="true">
					require(['WoltLabSuite/Core/Language/Chooser', 'WoltLabSuite/Core/Ui/ItemList'], function(LanguageChooser, UiItemList) {
						UiItemList.init(
							'tagSearchInput',
							[{if !$tagNames|empty}{implode from=$tagNames item=tagName}'{@$tagName|encodeJS}'{/implode}{/if}],
							{
								ajax: {
									className: 'wcf\\data\\tag\\TagAction'
								},
								maxItems: {@SEARCH_MAX_COMBINED_TAGS},
								restricted: true,
								submitFieldName: 'tagNames[]'
							}
						);
						
						var languageId = {@$languageID};
						LanguageChooser.getChooser('languageID').callback = function (listItem) {
							var newLanguageId = parseInt(elData(listItem, 'language-id'), 10);
							if (newLanguageId !== languageId) {
								languageId = newLanguageId;
							}
						};
					});
				</script>
			</div>
			
			{if !$tags|empty}
				<section class="section">
					<h2 class="sectionTitle">{lang}wcf.search.type.tags.popular{/lang}</h2>
					
					{include file='tagCloudBox'}
				</section>
			{/if}
			
			{event name='sections'}
			
			{include file='captcha' supportsAsyncCaptcha=true}
			
			<div class="formSubmit">
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
				{@SECURITY_TOKEN_INPUT_TAG}
			</div>
		</div>
	</div>
</form>

{include file='footer' __disableAds=true}
