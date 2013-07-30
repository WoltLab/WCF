{include file='documentHeader'}

<head>
	<title>{lang}wcf.search.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='header'}

<header class="boxHeadline">
	<h1>{lang}wcf.search.title{/lang}</h1>
</header>

{include file='userNotice'}

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $errorMessage|isset}
	<p class="error">{@$errorMessage}</p>
{/if}

<form method="post" action="{link controller='Search'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.search.general{/lang}</legend>
			
			<dl{if $errorField == 'q'} class="formError"{/if}>
				<dt><label for="searchTerm">{lang}wcf.search.query{/lang}</label></dt>
				<dd>
					<input type="text" id="searchTerm" name="q" value="{$query}" class="long" maxlength="255" autofocus="autofocus" />
					{if $errorField == 'q'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.search.query.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					<label><input type="checkbox" name="subjectOnly" value="1"{if $subjectOnly == 1} checked="checked"{/if} /> {lang}wcf.search.subjectOnly{/lang}</label>
					{event name='queryOptions'}
					
					<p><small>{lang}wcf.search.query.description{/lang}</small></p>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="searchAuthor">{lang}wcf.search.author{/lang}</label></dt>
				<dd>
					<input type="text" id="searchAuthor" name="username" value="{$username}" class="long" maxlength="255" autocomplete="off" />
					<label><input type="checkbox" name="nameExactly" value="1"{if $nameExactly == 1} checked="checked"{/if} /> {lang}wcf.search.matchExactly{/lang}</label>
					{event name='authorOptions'}
				</dd>
			</dl>
			
			<dl>
				<dt><label for="startDate">{lang}wcf.search.period{/lang}</label></dt>
				<dd>
					<input type="date" id="startDate" name="startDate" value="{$startDate}" data-placeholder="{lang}wcf.date.period.start{/lang}" />
					<input type="date" id="endDate" name="endDate" value="{$endDate}" data-placeholder="{lang}wcf.date.period.end{/lang}" />
					{event name='periodOptions'}
				</dd>
			</dl>
			
			<dl>
				<dt><label for="sortField">{lang}wcf.search.sortBy{/lang}</label></dt>
				<dd>
					<select id="sortField" name="sortField">
						<option value="relevance"{if $sortField == 'relevance'} selected="selected"{/if}>{lang}wcf.search.sortBy.relevance{/lang}</option>
						<option value="subject"{if $sortField == 'subject'} selected="selected"{/if}>{lang}wcf.search.sortBy.subject{/lang}</option>
						<option value="time"{if $sortField == 'time'} selected="selected"{/if}>{lang}wcf.search.sortBy.time{/lang}</option>
						<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wcf.search.sortBy.username{/lang}</option>
					</select>
					
					<select name="sortOrder">
						<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
						<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
					</select>
					{event name='displayOptions'}
				</dd>
			</dl>
			
			{event name='generalFields'}
			
			<dl>
				<dt>{lang}wcf.search.type{/lang}</dt>
				<dd class="floated">
					{foreach from=$objectTypes key=objectTypeName item=objectType}
						{if $objectType->isAccessible()}
							<label><input id="{@'.'|str_replace:'_':$objectTypeName}" type="checkbox" name="types[]" value="{@$objectTypeName}"{if $objectTypeName|in_array:$selectedObjectTypes} checked="checked"{/if} /> {lang}wcf.search.type.{@$objectTypeName}{/lang}</label>
						{/if}
					{/foreach}
				</dd>
			</dl>
		</fieldset>
		
		{event name='fieldsets'}
		{if $useCaptcha}{include file='recaptcha'}{/if}
		
		{foreach from=$objectTypes key=objectTypeName item=objectType}
			{if $objectType->isAccessible() && $objectType->getFormTemplateName()}
				{assign var='__jsID' value='.'|str_replace:'_':$objectTypeName}
				<fieldset id="{@$__jsID}Form">
					<legend>{lang}wcf.search.type.{@$objectTypeName}{/lang}</legend>
					
					{include file=$objectType->getFormTemplateName() application=$objectType->getApplication()}
					
					<script data-relocate="true">
						//<![CDATA[
						$(function() {
							$('#{@$__jsID}').click(function() {
								if (this.checked) $('#{@$__jsID}Form').wcfFadeIn();
								else $('#{@$__jsID}Form').wcfFadeOut();
							});
							{if !$objectTypeName|in_array:$selectedObjectTypes}$('#{@$__jsID}Form').hide();{/if}
						});
						//]]>
					</script>
				</fieldset>
			{/if}
		{/foreach}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>

{include file='footer'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Search.User($('#searchAuthor'), function(data) {
			$('#searchAuthor').val(data.label);//.focus();
		});
	});
	//]]>
</script>

</body>
</html>