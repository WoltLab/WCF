{include file='header' pageTitle='wcf.acp.paidSubscription.'|concat:$action}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		$('#subscriptionLengthPermanent').change(function() {
			if ($('#subscriptionLengthPermanent').is(':checked')) {
				$('#subscriptionLengthDL, #isRecurringDL').hide();
			}
			else {
				$('#subscriptionLengthDL, #isRecurringDL').show();
			}
		});
		$('#subscriptionLengthPermanent').change();
	});
	//]]>
</script>

{include file='multipleLanguageInputJavascript' elementIdentifier='description' forceSelection=false}
{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='PaidSubscriptionList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.paidSubscription.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='PaidSubscriptionAdd'}{/link}{else}{link controller='PaidSubscriptionEdit' id=$subscriptionID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'title'} class="formError"{/if}>
			<dt><label for="title">{lang}wcf.global.title{/lang}</label></dt>
			<dd>
				<input type="text" id="title" name="title" value="{$i18nPlainValues['title']}" autofocus="autofocus" class="medium" />
				{if $errorField == 'title'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{else}
							{lang}wcf.acp.paidSubscription.title.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'description'} class="formError"{/if}>
			<dt><label for="description">{lang}wcf.global.description{/lang}</label></dt>
			<dd>
				<textarea id="description" name="description" cols="40" rows="10">{$i18nPlainValues[description]}</textarea>
				{if $errorField == 'description'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.paidSubscription.description.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt><label for="showOrder">{lang}wcf.acp.paidSubscription.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" id="showOrder" name="showOrder" value="{if $showOrder}{@$showOrder}{/if}" class="tiny" min="0" />
				<small>{lang}wcf.acp.paidSubscription.showOrder.description{/lang}</small>
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="isDisabled" value="1" {if $isDisabled}checked="checked" {/if}/> {lang}wcf.acp.paidSubscription.isDisabled{/lang}</label>
				<small>{lang}wcf.acp.paidSubscription.isDisabled.description{/lang}</small>
			</dd>
		</dl>
		
		{if $availableSubscriptions|count}
			<dl>
				<dt>{lang}wcf.acp.paidSubscription.excludedSubscriptions{/lang}</dt>
				<dd>
					{foreach from=$availableSubscriptions item=availableSubscription}
						<label><input type="checkbox" name="excludedSubscriptionIDs[]" value="{@$availableSubscription->subscriptionID}" {if $availableSubscription->subscriptionID|in_array:$excludedSubscriptionIDs}checked="checked" {/if}/> {$availableSubscription->title|language}</label>
					{/foreach}
					<small>{lang}wcf.acp.paidSubscription.excludedSubscriptions.description{/lang}</small>
				</dd>
			</dl>
		{/if}
		
		{event name='dataFields'}
	</div>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.paidSubscription.paymentOptions{/lang}</h2>
	
		<dl{if $errorField == 'cost'} class="formError"{/if}>
			<dt><label for="cost">{lang}wcf.acp.paidSubscription.cost{/lang}</label></dt>
			<dd>
				<input type="number" id="cost" name="cost" value="{$cost}" class="tiny" step="0.01" min="0" />
				<select name="currency" id="currency">
					{htmlOptions values=$availableCurrencies output=$availableCurrencies selected=$currency}
				</select>
				{if $errorField == 'cost'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.paidSubscription.cost.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" id="subscriptionLengthPermanent" name="subscriptionLengthPermanent" value="1" {if !$subscriptionLength}checked="checked" {/if}/> {lang}wcf.acp.paidSubscription.subscriptionLength.permanent{/lang}</label>
			</dd>
		</dl>
		
		<dl id="subscriptionLengthDL"{if $errorField == 'subscriptionLength'} class="formError"{/if}>
			<dt><label for="subscriptionLength">{lang}wcf.acp.paidSubscription.subscriptionLength{/lang}</label></dt>
			<dd>
				<input type="number" id="subscriptionLength" name="subscriptionLength" value="{@$subscriptionLength}" class="tiny" />
				<select name="subscriptionLengthUnit" id="subscriptionLengthUnit">
					<option value="D"{if $subscriptionLengthUnit == 'D'} selected="selected"{/if}>{lang}wcf.acp.paidSubscription.subscriptionLengthUnit.D{/lang}</option>
					<option value="M"{if $subscriptionLengthUnit == 'M'} selected="selected"{/if}>{lang}wcf.acp.paidSubscription.subscriptionLengthUnit.M{/lang}</option>
					<option value="Y"{if $subscriptionLengthUnit == 'Y'} selected="selected"{/if}>{lang}wcf.acp.paidSubscription.subscriptionLengthUnit.Y{/lang}</option>
				</select>
				{if $errorField == 'subscriptionLength'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.paidSubscription.subscriptionLength.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl id="isRecurringDL">
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="isRecurring" value="1" {if $isRecurring}checked="checked" {/if}/> {lang}wcf.acp.paidSubscription.isRecurring{/lang}</label>
				<small>{lang}wcf.acp.paidSubscription.isRecurring.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'groupIDs'} class="formError"{/if}>
			<dt><label>{lang}wcf.acp.paidSubscription.userGroups{/lang}</label></dt>
			<dd>
				{foreach from=$availableUserGroups item=userGroup}
					<label><input type="checkbox" name="groupIDs[]" value="{@$userGroup->groupID}" {if $userGroup->groupID|in_array:$groupIDs}checked="checked" {/if}/> {$userGroup->groupName|language}</label>
				{/foreach}
				{if $errorField == 'groupIDs'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.paidSubscription.userGroups.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.paidSubscription.userGroups.description{/lang}</small>
				
			</dd>
		</dl>
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}