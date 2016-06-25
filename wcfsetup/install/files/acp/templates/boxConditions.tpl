{if !$errorField|isset}{assign var=errorField value=''}{/if}

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.box.settings{/lang}</h2>
	
	{if $defaultLimit !== null}
		<dl{if $errorField === 'limit'} class="formError"{/if}>
			<dt>{lang}wcf.acp.box.settings.limit{/lang}</dt>
			<dd>
				<input type="number" name="limit" id="limit" value="{$limit}" min="{$minimumLimit}"{if $maximumLimit !== null} max="{$maximumLimit}"{/if} class="tiny">
				{if $errorField === 'limit'}
					<small class="innerError">
						{if $errorType === 'lessThan'}
							{lang lessThan=$maximumLimit+1}wcf.global.form.error.lessThan{/lang}
						{elseif $errorType === 'greaterThan'}
							{lang greaterThan=$minimumLimit-1}wcf.global.form.error.greaterThan{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
	{/if}
	
	{if !$validSortFields|empty}
		<dl{if $errorField === 'sorting'} class="formError"{/if}>
			<dt>{lang}wcf.global.sorting{/lang}</dt>
			<dd>
				<select name="sortField" id="sortField">
					{foreach from=$validSortFields item=validSortField}
						<option value="{$validSortField}"{if $validSortField == $sortField} selected{/if}>{lang}{$sortFieldLanguageItemPrefix}.{$validSortField}{/lang}</option>
					{/foreach}
				</select>
				
				<select name="sortOrder" id="sortOrder">
					<option value="ASC"{if $sortOrder == 'ASC'} selected{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
					<option value="DESC"{if $sortOrder == 'DESC'} selected{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
				</select>
				
				{if $errorField === 'sorting'}
					<small class="innerError">
						{lang}wcf.global.sorting.error.{$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
	{/if}
	
	{event name='fields'}
	
	{foreach from=$conditionObjectTypes item=conditionObjectType}
		{@$conditionObjectType->getProcessor()->getHtml()}
	{/foreach}
</section>
