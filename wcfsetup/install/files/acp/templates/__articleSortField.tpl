<dl>
	<dt><label for="sortField">{lang}wcf.acp.article.category.sortField{/lang}</label></dt>
	<dd>
		<select name="sortField" id="sortField">
			{foreach from=$availableSortFields item=availableSortField}
				<option value="{$availableSortField}"{if $availableSortField === $sortField} selected{/if}>{lang}wcf.acp.article.category.sortField.{$availableSortField}{/lang}</option>
			{/foreach}
		</select>
		<select name="sortOrder" id="sortOrder">
			<option value="ASC"{if $sortOrder === 'ASC'} selected{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
			<option value="DESC"{if $sortOrder === 'DESC'} selected{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
		</select>
	</dd>
</dl>
