{include file='header' __disableAds=true}

<form id="extendedSearchForm" method="post" action="{link controller='Search'}{if $extended}extended=1{/if}{/link}">
	<div class="section">
		<div class="searchBar">
			<input id="searchQuery" class="searchQuery long" type="text" name="q" value="" maxlength="255" placeholder="{lang}wcf.global.search.enterSearchTerm{/lang}" autocomplete="off" autofocus>
			<select id="searchType" class="searchType" name="type" aria-label="wcf.search.type">
				<option value="">{lang}wcf.search.type.everywhere{/lang}</option>
				{foreach from=$objectTypes key=objectTypeName item=objectType}
					{if $objectType->isAccessible()}
						<option value="{@$objectTypeName}">{lang}wcf.search.type.{@$objectTypeName}{/lang}</option>
					{/if}
				{/foreach}
			</select>
			<button class="searchButton button buttonPrimary">{lang}wcf.global.search{/lang}</button>
		</div>

		<details class="searchFiltersContainer"{if $extended} open{/if}>
			<summary class="searchShowMoreFiltersButton">{lang}wcf.search.button.showMoreFilters{/lang}</summary>
		
			<div class="searchFilters defaultSearchFilters">
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="subjectOnly" value="1"> {lang}wcf.search.subjectOnly{/lang}</label>
						{* deprecated *}{event name='queryOptions'}
					</dd>
				</dl>
				
				<dl>
					<dt><label for="searchAuthor">{lang}wcf.search.author{/lang}</label></dt>
					<dd>
						<input type="text" id="searchAuthor" name="username" value="" class="medium" maxlength="255" autocomplete="off">
						<label><input type="checkbox" name="nameExactly" value="1"> {lang}wcf.search.matchExactly{/lang}</label>
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
				
				{* deprecated *}{event name='generalFields'}
				{event name='searchFilters'}
			</div>

			{foreach from=$objectTypes key=objectTypeName item=objectType}
				{if $objectType->isAccessible() && $objectType->getFormTemplateName()}
					<div class="searchFilters objectTypeSearchFilters" data-object-type="{$objectTypeName}" hidden>
						{include file=$objectType->getFormTemplateName() application=$objectType->getApplication()}
					</div>
				{/if}
			{/foreach}

			<button class="searchButton button buttonPrimary">{lang}wcf.global.search{/lang}</button>
		</details>
	</div>
</form>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/User/Search/Input'], (UiUserSearchInput) => {
		new UiUserSearchInput(document.getElementById('searchAuthor'));
	});
	require(['WoltLabSuite/Core/Ui/Search/Extended'], ({ UiSearchExtended }) => {
		new UiSearchExtended();
	});
</script>

{include file='footer' __disableAds=true}
