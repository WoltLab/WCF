WCF.Language.addObject({
	'wcf.message.quote.insertAllQuotes': '{lang}wcf.message.quote.insertAllQuotes{/lang}',
	'wcf.message.quote.insertSelectedQuotes': '{lang}wcf.message.quote.insertSelectedQuotes{/lang}',
	'wcf.message.quote.manageQuotes': '{lang}wcf.message.quote.manageQuotes{/lang}',
	'wcf.message.quote.quoteSelected': '{lang}wcf.message.quote.quoteSelected{/lang}',
	'wcf.message.quote.removeAllQuotes': '{lang}wcf.message.quote.removeAllQuotes{/lang}',
	'wcf.message.quote.removeSelectedQuotes': '{lang}wcf.message.quote.removeSelectedQuotes{/lang}',
	'wcf.message.quote.showQuotes': '{lang}wcf.message.quote.showQuotes{/lang}'
});

{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value=''}{/if}
{if !$supportPaste|isset}{assign var=supportPaste value=false}{/if}
var $quoteManager = new WCF.Message.Quote.Manager({@$__quoteCount}, '{$wysiwygSelector|encodeJS}', {if $supportPaste}true{else}false{/if}, [ {implode from=$__quoteRemove item=quoteID}'{$quoteID}'{/implode} ]);