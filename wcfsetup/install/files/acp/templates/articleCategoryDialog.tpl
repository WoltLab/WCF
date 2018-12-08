<div class="section">
	<dl>
		<dt>{lang}wcf.acp.article.category{/lang}</dt>
		<dd>
			<select name="categoryID" id="categoryID">
				<option value="0">{lang}wcf.global.noSelection{/lang}</option>
				
				{foreach from=$categoryNodeList item=category}
					<option value="{@$category->categoryID}">{if $category->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($category->getDepth() - 1)}{/if}{$category->getTitle()}</option>
				{/foreach}
			</select>
		</dd>
	</dl>
</div>

<div class="formSubmit">
	<button data-type="submit">{lang}wcf.global.button.submit{/lang}</button>
</div>
