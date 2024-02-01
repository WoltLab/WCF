{include file='header' pageTitle='wcf.acp.tag.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.tag.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='TagList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.tag.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='TagAdd'}{/link}{else}{link controller='TagEdit' object=$tagObj}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'name'} class="formError"{/if}>
			<dt><label for="name">{lang}wcf.global.name{/lang}</label></dt>
			<dd>
				<input type="text" id="name" name="name" value="{$name}" required autofocus class="long">
				{if $errorField == 'name'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'duplicate'}
							{lang}wcf.acp.tag.error.name.duplicate{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{hascontent}
			<dl{if $errorField == 'languageID' || $action == 'edit'} class="{if $action == 'edit'}disabled{else}formError{/if}"{/if}>
				<dt><label for="languageID">{lang}wcf.acp.tag.languageID{/lang}</label></dt>
				<dd>
					<select id="languageID" name="languageID"{if $action == 'edit'} disabled{/if}>
						{content}
							{foreach from=$availableLanguages item=language}
								<option value="{$language->languageID}"{if $languageID == $language->languageID} selected{/if}>{$language->languageName} ({$language->languageCode})</option>
							{/foreach}
						{/content}
					</select>
					{if $errorField == 'languageID'}
						<small class="innerError">
							{lang}wcf.acp.tag.error.languageID.{$errorType}{/lang}
						</small>
					{/if}
				</dd>
			</dl>
		{/hascontent}
		
		{if !$tagObj|isset || $tagObj->synonymFor === null}
			<dl>
				<dt><label for="synonyms">{lang}wcf.acp.tag.synonyms{/lang}</label></dt>
				<dd>
					<input id="synonyms" type="text" value="" class="long">
				</dd>
			</dl>
			
			<script data-relocate="true">
				require(['WoltLabSuite/Core/Ui/ItemList'], function(UiItemList) {
					UiItemList.init(
						'synonyms',
						[{if !$synonyms|empty}{implode from=$synonyms item=synonym}'{$synonym|encodeJS}'{/implode}{/if}],
						{
							ajax: {
								className: 'wcf\\data\\tag\\TagAction'
							},
							maxLength: {@TAGGING_MAX_TAG_LENGTH},
							submitFieldName: 'tags[]'
						}
					);
				});
			</script>
		{elseif $tagObj|isset}
			<dl>
				<dt><label for="synonyms">{lang}wcf.acp.tag.synonymFor{/lang}</label></dt>
				<dd>
					<a href="{link controller='TagEdit' id=$tagObj->synonymFor}{/link}" class="badge tag">{$synonym->name}</a>
				</dd>
			</dl>
		{/if}
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
