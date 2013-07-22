{include file='header' pageTitle='wcf.acp.user.activityPoint.option'}

<script>
	//<![CDATA[
	$(function() {
		$('#updateEvents').click(function () {
			new WCF.ACP.Worker('events', 'wcf\\system\\worker\\UserActivityPointUpdateEventsWorker', '{lang}wcf.acp.user.activityPoint.updateEvents{/lang}');
		});
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.user.activityPoint.option{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a id="updateEvents" class="button"><span class="icon icon16 icon-repeat"></span> <span>{lang}wcf.acp.user.activityPoint.updateEvents{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='UserActivityPointOption'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.user.activityPoint.pointsPerObject{/lang}</legend>
			{foreach from=$objectTypes item='objectType'}
				<dl{if $errorField == $objectType->objectTypeID} class="formError"{/if}>
					<dt><label for="{@$objectType->objectType}">{lang}wcf.user.activityPoint.objectType.{$objectType->objectType}{/lang}</label></dt>
					<dd>
						<input type="number" id="{@$objectType->objectType}" name="points[{@$objectType->objectTypeID}]" value="{$points[$objectType->objectTypeID]}" required="required" min="0" class="tiny" />
						{if $errorField == $objectType->objectTypeID}
							<small class="innerError">
								{lang}wcf.acp.user.activityPoint.option.notValid{/lang}
							</small>
						{/if}
					</dd>
				</dl>
			{/foreach}
		</fieldset>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>

{include file='footer'}