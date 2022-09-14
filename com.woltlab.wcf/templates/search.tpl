{include file='header' __disableAds=true}

<form id="extendedSearchForm" method="post" action="{link controller='Search'}{if $extended}extended=1{/if}{/link}">
	<div class="section">
		<div class="searchBar">
			<input id="searchQuery" class="searchQuery long" type="text" name="q" value="" maxlength="255" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" autocomplete="off" autofocus>
			<select id="searchType" class="searchType" name="type" aria-label="{lang}wcf.search.type{/lang}">
				<option value="">{lang}wcf.search.type.everywhere{/lang}</option>
				{foreach from=$objectTypes key=objectTypeName item=objectType}
					{if $objectType->isAccessible()}
						<option value="{@$objectTypeName}">{lang}wcf.search.type.{@$objectTypeName}{/lang}</option>
					{/if}
				{/foreach}
			</select>
			<button type="submit" class="searchButton button buttonPrimary">{lang}wcf.global.button.search{/lang}</button>
		</div>

		<details class="searchFiltersContainer"{if $extended} open{/if}>
			<summary class="searchShowMoreFiltersButton">{lang}wcf.search.button.showMoreFilters{/lang}</summary>
		
			<div class="searchFilters defaultSearchFilters">
				<dl>
					<dt><label>{lang}wcf.search.searchIn{/lang}</label></dt>
					<dd>
						<label><input type="radio" name="subjectOnly" value="" checked> {lang}wcf.search.searchIn.subjectAndMessage{/lang}</label>
						<label><input type="radio" name="subjectOnly" value="1"> {lang}wcf.search.searchIn.subjectOnly{/lang}</label>
						{* deprecated *}{event name='queryOptions'}
					</dd>
				</dl>

				<dl>
					<dt><label for="sortField">{lang}wcf.search.sortBy{/lang}</label></dt>
					<dd>
						<select id="sortField" name="sortField">
							<option value="relevance"{if $sortField == 'relevance'} selected{/if}>{lang}wcf.search.sortBy.relevance{/lang}</option>
							<option value="subject"{if $sortField == 'subject'} selected{/if}>{lang}wcf.global.subject{/lang}</option>
							<option value="time"{if $sortField == 'time'} selected{/if}>{lang}wcf.search.sortBy.time{/lang}</option>
							<option value="username"{if $sortField == 'username'} selected{/if}>{lang}wcf.search.sortBy.username{/lang}</option>
						</select>
						
						<select name="sortOrder">
							<option value="ASC"{if $sortOrder == 'ASC'} selected{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
							<option value="DESC"{if $sortOrder == 'DESC'} selected{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
						</select>
						{* deprecated *}{event name='displayOptions'}
					</dd>
				</dl>
				
				<dl>
					<dt><label for="searchAuthor">{lang}wcf.search.author{/lang}</label></dt>
					<dd>
						<input type="text" id="searchAuthor" name="usernames" value="" class="medium" autocomplete="off">
						{* deprecated *}{event name='authorOptions'}
					</dd>
				</dl>
				
				<dl>
					<dt><label for="startDate">{lang}wcf.search.period{/lang}</label></dt>
					<dd>
						<input type="date" id="startDate" name="startDate" value="" data-placeholder="{lang}wcf.date.period.start{/lang}">
						<input type="date" id="endDate" name="endDate" value="" data-placeholder="{lang}wcf.date.period.end{/lang}">
						{* deprecated *}{event name='periodOptions'}
					</dd>
				</dl>
				
				{* deprecated *}{event name='generalFields'}
				{event name='searchFilters'}
			</div>

			<div class="searchFiltersTitle" aria-hidden="true" hidden></div>

			{foreach from=$objectTypes key=objectTypeName item=objectType}
				{if $objectType->isAccessible() && $objectType->getFormTemplateName()}
					<div class="searchFilters objectTypeSearchFilters" data-object-type="{$objectTypeName}" hidden>
						{include file=$objectType->getFormTemplateName() application=$objectType->getApplication()}
					</div>
				{/if}
			{/foreach}

			<button type="submit" class="searchButton button buttonPrimary">{lang}wcf.global.button.search{/lang}</button>
		</details>
	</div>
</form>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/ItemList/User'], function(UiItemListUser) {
		UiItemListUser.init('searchAuthor', {
			maxItems: 5
		});
	});
	require(['WoltLabSuite/Core/Ui/Search/Extended'], ({ UiSearchExtended }) => {
		new UiSearchExtended();
	});
</script>

{include file='footer' __disableAds=true}
