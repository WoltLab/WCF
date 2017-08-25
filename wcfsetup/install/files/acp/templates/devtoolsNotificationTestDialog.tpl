{if $errors}
	<p class="error">{lang}wcf.acp.devtools.notificationTest.error.generation{/lang}</p>
{/if}

<div class="section notificationTestSection" id="notificationTestAllSection">
	{foreach from=$events item=event}
		<section class="section">
			<h2 class="sectionTitle">{$event[description]}</h2>
			
			{if $event[title]|isset}
				<dl>
					<dt>{lang}wcf.global.title{/lang}</dt>
					<dd>{@$event[title]}</dd>
				</dl>
			{else}
				<dl>
					<dt>{lang}wcf.acp.devtools.notificationTest.title.exception{/lang}</dt>
					<dd><pre>{$event[titleException]}</pre></dd>
				</dl>
			{/if}
			
			{if $event[message]|isset}
				<dl>
					<dt>{lang}wcf.acp.devtools.notificationTest.message{/lang}</dt>
					<dd>{@$event[message]}</dd>
				</dl>
			{else}
				<dl>
					<dt>{lang}wcf.acp.devtools.notificationTest.message.exception{/lang}</dt>
					<dd><pre>{$event[messageException]}</pre></dd>
				</dl>
			{/if}
			
			{if $event[link]|isset}
				<dl>
					<dt>{lang}wcf.acp.devtools.notificationTest.link{/lang}</dt>
					<dd><a href="{@$event[link]}">{@$event[link]}</a></dd>
				</dl>
			{else}
				<dl>
					<dt>{lang}wcf.acp.devtools.notificationTest.link.exception{/lang}</dt>
					<dd><pre>{$event[linkException]}</pre></dd>
				</dl>
			{/if}
			
			{if $hasEmailSupport}
				{if $event[instantEmail]|isset}
					<dl>
						<dt>{lang}wcf.acp.devtools.notificationTest.instantEmail{/lang}</dt>
						<dd><pre>{$event[instantEmail]}</pre></dd>
					</dl>
				{elseif $event[instantEmailException]|isset}
					<dl>
						<dt>{lang}wcf.acp.devtools.notificationTest.instantEmail.exception{/lang}</dt>
						<dd><pre>{$event[instantEmailException]}</pre></dd>
					</dl>
				{/if}
				
				{if $event[dailyEmail]|isset}
					<dl>
						<dt>{lang}wcf.acp.devtools.notificationTest.dailyEmail{/lang}</dt>
						<dd><pre>{$event[dailyEmail]}</pre></dd>
					</dl>
				{else}
					<dl>
						<dt>{lang}wcf.acp.devtools.notificationTest.dailyEmail.exception{/lang}</dt>
						<dd><pre>{$event[dailyEmailException]}</pre></dd>
					</dl>
				{/if}
			{/if}
		</section>
	{/foreach}
</div>

<section class="section notificationTestSection" id="notificationTestTitleSection" style="display: none;">
	<h2 class="sectionTitle">{lang}wcf.acp.devtools.notificationTest.titles{/lang}</h2>
	
	<dl>
		{foreach from=$events item=event}
			<dt>{$event[description]}</dt>
			<dd>{if $event[title]|isset}{@$event[title]}{else}<pre>{$event[titleException]}</pre>{/if}</dd>
		{/foreach}
	</dl>
</section>

<section class="section notificationTestSection" id="notificationTestMessageSection" style="display: none;">
	<h2 class="sectionTitle">{lang}wcf.acp.devtools.notificationTest.messages{/lang}</h2>
	
	<dl>
		{foreach from=$events item=event}
			<dt>{$event[description]}</dt>
			<dd>{if $event[message]|isset}{@$event[message]}{else}<pre>{$event[messageException]}</pre>{/if}</dd>
		{/foreach}
	</dl>
</section>

<section class="section notificationTestSection" id="notificationTestLinkSection" style="display: none;">
	<h2 class="sectionTitle">{lang}wcf.acp.devtools.notificationTest.links{/lang}</h2>
	
	<dl>
		{foreach from=$events item=event}
			<dt>{$event[description]}</dt>
			<dd>{if $event[link]|isset}<a href="{@$event[link]}">{@$event[link]}</a>{else}<pre>{$event[linkException]}</pre>{/if}</dd>
		{/foreach}
	</dl>
</section>

{if $hasEmailSupport}
	<section class="section notificationTestSection" id="notificationTestInstantEmailSection" style="display: none;">
		<h2 class="sectionTitle">{lang}wcf.acp.devtools.notificationTest.instantEmails{/lang}</h2>
		
		<dl>
			{foreach from=$events item=event}
				{if $event[instantEmail]|isset || $event[instantEmailException]|isset}
					<dt>{$event[description]}</dt>
					<dd><pre>{if $event[instantEmail]|isset}{$event[instantEmail]}{else}{$event[instantEmailException]}{/if}</pre></dd>
				{/if}
			{/foreach}
		</dl>
	</section>
	
	<section class="section notificationTestSection" id="notificationTestDailyEmailSection" style="display: none;">
		<h2 class="sectionTitle">{lang}wcf.acp.devtools.notificationTest.dailyEmails{/lang}</h2>
		
		<dl>
			{foreach from=$events item=event}
				<dt>{$event[description]}</dt>
				<dd><pre>{if $event[dailyEmail]|isset}{$event[dailyEmail]}{else}{$event[dailyEmailException]}{/if}</pre></dd>
			{/foreach}
		</dl>
	</section>
{/if}

<div class="formSubmit">
	<button class="small buttonPrimary" id="notificationTestAllSectionButton">{lang}wcf.acp.devtools.notificationTest.button.showAll{/lang}</button>
	<button class="small button" id="notificationTestTitleSectionButton">{lang}wcf.acp.devtools.notificationTest.titles{/lang}</button>
	<button class="small button" id="notificationTestMessageSectionButton">{lang}wcf.acp.devtools.notificationTest.messages{/lang}</button>
	<button class="small button" id="notificationTestLinkSectionButton">{lang}wcf.acp.devtools.notificationTest.links{/lang}</button>
	{if $hasEmailSupport}
		<button class="small button" id="notificationTestInstantEmailSectionButton">{lang}wcf.acp.devtools.notificationTest.instantEmails{/lang}</button>
		<button class="small button" id="notificationTestDailyEmailSectionButton">{lang}wcf.acp.devtools.notificationTest.dailyEmails{/lang}</button>
	{/if}
</div>
