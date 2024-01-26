{include file='header' __disableAds=true}

{include file='shared_formError'}

{if $errorMessage|isset}
	<woltlab-core-notice type="error">{@$errorMessage}</woltlab-core-notice>
{/if}

<form method="post" action="{link controller='TagSearch'}{/link}">
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
			require(['WoltLabSuite/Core/Ui/ItemList'], function(UiItemList) {
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
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer' __disableAds=true}
