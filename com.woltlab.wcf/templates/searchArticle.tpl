<dl>
	<dt><label for="articleCategoryID">{lang}wcf.article.search.categories{/lang}</label></dt>
	<dd>
		<select name="articleCategoryID" id="articleCategoryID">
			<option value="">{lang}wcf.global.language.noSelection{/lang}</option>
			{foreach from=$articleCategoryList item=category}
				<option value="{@$category->categoryID}">{if $category->getDepth() > 1}{@'&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:-1+$category->getDepth()}{/if}{$category->getTitle()}</option>
			{/foreach}
		</select>
	</dd>
</dl>

{event name='fields'}
