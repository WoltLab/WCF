{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value='text'}{/if}

{event name='wysiwyg'}

<script data-eager="true">
{
	let stylesheet = document.getElementById("ckeditor5-stylesheet");
	if (stylesheet === null) {
		stylesheet = document.createElement("link");
		stylesheet.rel = "stylesheet";
		stylesheet.type = "text/css";
		stylesheet.href = "{$__wcf->getPath()}style/ckeditor5.css";
		stylesheet.id = "ckeditor5-stylesheet";

		document.querySelector('link[rel="stylesheet"]').before(stylesheet);
	}
}
</script>
<script data-relocate="true">
	require([
		"WoltLabSuite/Core/Component/Ckeditor",
		"WoltLabSuite/Core/prism-meta",
		{@$__wcf->getBBCodeHandler()->getEditorLocalization()}
	], (
		{ setupCkeditor },
		PrismMeta
	) => {
		{jsphrase name='wcf.ckeditor.code.fileName'}
		{jsphrase name='wcf.ckeditor.code.lineNumber'}
		{jsphrase name='wcf.ckeditor.marker.error'}
		{jsphrase name='wcf.ckeditor.marker.info'}
		{jsphrase name='wcf.ckeditor.marker.success'}
		{jsphrase name='wcf.ckeditor.marker.warning'}
		{jsphrase name='wcf.ckeditor.quote'}
		{jsphrase name='wcf.ckeditor.quote.author'}
		{jsphrase name='wcf.ckeditor.quoteFrom'}
		{jsphrase name='wcf.editor.button.group.format'}
		{jsphrase name='wcf.editor.button.group.list'}
		{jsphrase name='wcf.editor.button.spoiler'}
		{jsphrase name='wcf.editor.restoreDraft'}
		{jsphrase name='wcf.editor.restoreDraft.preview'}
		{jsphrase name='wcf.editor.restoreDraft.restoreOrDiscard'}

		{include file='mediaJavaScript'}

		const element = document.getElementById('{$wysiwygSelector|encodeJS}');
		if (element === null) {
			throw new Error("Unable to find the source element '{$wysiwygSelector|encodeJS}' for the editor.")
		}

		let enableAttachments = element.dataset.disableAttachments !== "true";
		{if !$attachmentHandler|empty && !$attachmentHandler->canUpload()}
		enableAttachments = false;
		{/if}

		const features = {
			alignment: true,
			attachment: enableAttachments,
			autosave: element.dataset.autosave || "",
			code: true,
			codeBlock: true,
			fontColor: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('color')}true{else}false{/if},
			fontFamily: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('font')}true{else}false{/if},
			fontSize: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('size')}true{else}false{/if},
			heading: true,
			html: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('html')}true{else}false{/if},
			image: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('img')}true{else}false{/if},
			link: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('url')}true{else}false{/if},
			list: true,
			mark: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('mark')}true{else}false{/if},
			media: {if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}true{else}false{/if},
			mention: element.dataset.supportMention === "true",
			quoteBlock: true,
			strikethrough: true,
			submitOnEnter: false,
			subscript: true,
			superscript: true,
			spoiler: {if $__wcf->getBBCodeHandler()->isAvailableBBCode('spoiler')}true{else}false{/if},
			table: true,
			underline: true,
			undo: true,
		};

		const bbcodes = [
			{foreach from=$__wcf->getBBCodeHandler()->getButtonBBCodes(true) item=__bbcode}
				{
					icon: '{@$__bbcode->getIcon()|encodeJS}',
					name: '{@$__bbcode->bbcodeTag|encodeJS}',
					label: '{@$__bbcode->getButtonLabel()|encodeJS}',
				},
			{/foreach}
		];
		if (features.media) {
			bbcodes.push({
				icon: "file-circle-plus;false",
				name: "media",
				label: '{jslang}wcf.editor.button.media{/jslang}',
			});
		}
		{assign var=emojis value=$__wcf->getSmileyCache()->getEmojis()}
		const smileys = [
			{foreach from=$emojis key=__code item=__smiley}
			{
				code: '{@$__code|encodeJS}',
				html: '{@$__smiley->getHtml()|encodeJS}',
			},
			{/foreach}
		];

		const codeBlockLanguages = [
			{ language: "", label: '{jslang}wcf.editor.code.highlighter.detect{/jslang}' },
			{ language: "plain", label: '{jslang}wcf.editor.code.highlighter.plain{/jslang}' },
			{foreach from=$__wcf->getBBCodeHandler()->getCodeBlockLanguages() item=__codeBlockLanguage}
				{ language: '{@$__codeBlockLanguage|encodeJS}', label: PrismMeta.default['{@$__codeBlockLanguage|encodeJS}'].title },
			{/foreach}
		];

		void setupCkeditor(element, features, bbcodes, smileys, codeBlockLanguages, '{@$__wcf->getBBCodeHandler()->getCkeditorLicenseKey()|encodeJS}');
	});
</script>
