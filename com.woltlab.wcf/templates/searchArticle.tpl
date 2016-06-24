<dl>
	<dt><label>{lang}wcf.article.search.categories{/lang}</label></dt>
	<dd>
		<ul class="scrollableCheckboxList">
			{foreach from=$articleCategoryList item=category}
				<li{if $category->getDepth() > 1} style="padding-left: {$category->getDepth()*20-20}px"{/if}>
					<label><input type="checkbox" name="articleCategoryIDs[]" value="{@$category->categoryID}"{if $category->categoryID|in_array:$articleCategoryIDs} checked{/if}> {$category->getTitle()}</label>
				</li>
			{/foreach}
		</ul>
	</dd>
</dl>

{event name='fields'}
