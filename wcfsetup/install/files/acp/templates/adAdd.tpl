{include file='header' pageTitle='wcf.acp.ad.'|concat:$action}

<script data-relocate="true">
	$(function() {
		new WCF.ACP.Ad.LocationHandler({
			{implode from=$variablesDescriptions key=objectType item=description}'{$objectType}': '{@$description|encodeJS}'{/implode}
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.ad.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='AdList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.ad.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form id="adForm" method="post" action="{if $action == 'add'}{link controller='AdAdd'}{/link}{else}{link controller='AdEdit' object=$adObject}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'adName'} class="formError"{/if}>
			<dt><label for="adName">{lang}wcf.global.name{/lang}</label></dt>
			<dd>
				<input type="text" id="adName" name="adName" value="{$adName}" required autofocus class="long">
				{if $errorField == 'adName'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.ad.adName.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'ad'} class="formError"{/if}>
			<dt><label for="ad">{lang}wcf.acp.ad.ad{/lang}</label></dt>
			<dd>
				<textarea id="ad" name="ad" cols="40" rows="10">{$ad}</textarea>
				<small>{lang}wcf.acp.ad.ad.description{/lang}</small>
				{if $errorField == 'ad'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.ad.ad.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="isDisabled" value="1"{if $isDisabled} checked{/if}> {lang}wcf.acp.ad.isDisabled{/lang}</label>
			</dd>
		</dl>
		
		<dl>
			<dt><label for="objectTypeID">{lang}wcf.acp.ad.location{/lang}</label></dt>
			<dd>
				<select name="objectTypeID" id="objectTypeID">
					<option value="0"{if !$objectTypeID} selected{/if}>{lang}wcf.global.noSelection{/lang}</option>
					{foreach from=$locations key='locationGroupLabel' item='locationGroup'}
						{assign var='__firstLocationID' value=$locationGroup|key}
						<optgroup label="{$locationGroupLabel}" data-category-name="{@$locationObjectTypes[$__firstLocationID]->categoryname}">
							{foreach from=$locationGroup key='locationID' item='location'}
								<option value="{$locationID}"{if $locationObjectTypes[$locationID]->page} data-page="{$locationObjectTypes[$locationID]->page}"{/if}{if $objectTypeID == $locationID} selected{/if}>{$location}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
				{if $errorField == 'objectTypeID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'noValidSelection'}
							{lang}wcf.global.form.error.noValidSelection{/lang}
						{else}
							{lang}wcf.acp.ad.location.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt><label for="showOrder">{lang}wcf.global.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" class="tiny" min="0">
				<small>{lang}wcf.acp.ad.showOrder.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.ad.conditions{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.ad.conditions.description{/lang}</p>
		</header>
		
		<section class="section" id="pageConditions">
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.acp.ad.conditions.page{/lang}</h2>
				<p class="sectionDescription">{lang}wcf.acp.ad.conditions.page.description{/lang}</p>
			</header>
			
			{foreach from=$groupedConditionObjectTypes['com.woltlab.wcf.page'] item='pageConditionObjectType'}
				{@$pageConditionObjectType->getProcessor()->getHtml()}
			{/foreach}
		</section>
		
		<section class="section" id="pointInTimeConditions">
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.acp.ad.conditions.pointInTime{/lang}</h2>
				<p class="sectionDescription">{lang}wcf.acp.ad.conditions.pointInTime.description{/lang}</p>
			</header>
				
			{foreach from=$groupedConditionObjectTypes['com.woltlab.wcf.pointInTime'] item='pointInTimeConditionObjectType'}
				{@$pointInTimeConditionObjectType->getProcessor()->getHtml()}
			{/foreach}
		</section>
		
		{event name='conditionTypeFieldsets'}
	</section>
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.ad.conditions.user{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.ad.conditions.user.description{/lang}</p>
		</header>

		{include file='shared_userConditions' groupedObjectTypes=$groupedConditionObjectTypes['com.woltlab.wcf.user']}
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
