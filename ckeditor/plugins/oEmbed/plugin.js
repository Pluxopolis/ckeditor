/*
* oEmbed Plugin plugin
* Copyright (c) Ingo Herbote
* Licensed under the MIT license
* jQuery Embed Plugin Embeds: http://code.google.com/p/jquery-oembed/ (MIT License)
* Plugin for: http://ckeditor.com/license (GPL/LGPL/MPL: http://ckeditor.com/license)
*/

(function () {

	CKEDITOR.plugins.add('oEmbed', {

		requires: ['dialog'],
		lang: ['fr', 'en', 'de'],
		init: function (editor) {
			editor.addCommand('oEmbed', new CKEDITOR.dialogCommand('oEmbed'));
			editor.ui.addButton('oEmbed', {
				label: editor.lang.oEmbed.button,
				command: 'oEmbed',
				icon: this.path + 'images/icon.png'
			});

			CKEDITOR.dialog.add('oEmbed', this.path + 'dialogs/oEmbedDialog.js');

			// Load jquery?
			if (typeof(jQuery) == 'undefined') {
				CKEDITOR.scriptLoader.load(this.path + 'jquery.min.js');
			}
			CKEDITOR.scriptLoader.load(CKEDITOR.getUrl(CKEDITOR.plugins.getPath('oEmbed') + 'dialogs/jquery.oembed.js'));
		}
	})
})();