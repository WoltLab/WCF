<dl class="jsOnly">
	<dt><label for="tagSearchInput{if $tagInputSuffix|isset}{@$tagInputSuffix}{/if}">{lang}wcf.tagging.tags{/lang}</label></dt>
	<dd>
		<div id="tagList{if $tagInputSuffix|isset}{@$tagInputSuffix}{/if}" class="editableItemList"></div>
		<input id="tagSearchInput{if $tagInputSuffix|isset}{@$tagInputSuffix}{/if}" type="text" value="" class="long" />
		<small>{lang}wcf.tagging.tags.description{/lang}</small>
	</dd>
</dl>
<script src="{@$__wcf->getPath()}js/WCF.Tagging{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
<script>
	//<![CDATA[
	$(function() {
		var $tagList = new WCF.Tagging.TagList('#tagList{if $tagInputSuffix|isset}{@$tagInputSuffix}{/if}', '#tagSearchInput{if $tagInputSuffix|isset}{@$tagInputSuffix}{/if}', {@TAGGING_MAX_TAG_LENGTH});
		
		{if $tags|isset && $tags|count}
			$tagList.load([ {implode from=$tags item=tag}'{$tag|encodeJS}'{/implode} ]);
		{/if}
	});
	//]]>
</script>