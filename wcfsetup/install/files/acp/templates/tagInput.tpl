{if $__wcf->session->getPermission('user.tag.canViewTag')}
	<dl class="jsOnly">
		<dt><label for="tagSearchInput{if $tagInputSuffix|isset}{@$tagInputSuffix}{/if}">{lang}wcf.tagging.tags{/lang}</label></dt>
		<dd>
			<input id="tagSearchInput{if $tagInputSuffix|isset}{@$tagInputSuffix}{/if}" type="text" value="" class="long">
			<small>{lang}wcf.tagging.tags.description{/lang}</small>
		</dd>
	</dl>
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/ItemList'], function(UiItemList) {
			UiItemList.init(
				'tagSearchInput{if $tagInputSuffix|isset}{@$tagInputSuffix}{/if}',
				[{if $tags|isset && $tags|count}{implode from=$tags item=tag}'{@$tag|encodeJS}'{/implode}{/if}],
				{
					ajax: {
						className: 'wcf\\data\\tag\\TagAction'
					},
					maxLength: {@TAGGING_MAX_TAG_LENGTH},
					submitFieldName: '{if $tagSubmitFieldName|isset}{@$tagSubmitFieldName}{else}tags[]{/if}'
				}
			);
		});
	</script>
{/if}
