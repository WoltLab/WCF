<div class="section">
	<dl>
		<dt><label for="categoryID">{lang}wcf.global.category{/lang}</label></dt>
		<dd>
			<select name="categoryID">
				<option value="0">{lang}wcf.global.noSelection{/lang}</option>
				
				{foreach from=$categoryList item=categoryItem}
					<option value="{$categoryItem->categoryID}">{$categoryItem->getTitle()}</option>
					
					{if $categoryItem->hasChildren()}
						{foreach from=$categoryItem item=subCategoryItem}
							<option value="{$subCategoryItem->categoryID}">&nbsp;&nbsp;&nbsp;&nbsp;{$subCategoryItem->getTitle()}</option>
							
							{if $subCategoryItem->hasChildren()}
								{foreach from=$subCategoryItem item=subSubCategoryItem}
									<option value="{$subSubCategoryItem->categoryID}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$subSubCategoryItem->getTitle()}</option>
								{/foreach}
							{/if}
						{/foreach}
					{/if}
				{/foreach}
			</select>
		</dd>
	</dl>
</div>
