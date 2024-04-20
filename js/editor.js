/**
 * Files_URLShortcut
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Paul Rosenberg <dev@paulrosenberg.de>
 * @copyright Paul Rosenberg 2024
 */

var Files_URLShortcut = {

	/**
	 * Stores info on the file being edited
	 */
	file: {
		opened: false,
		dir: null,
		name: null,
		mime: null,
		size: null
	},


	/**
	 * Current files app context
	 */
	currentContext: null,

	/**
	 * Handles the FileAction click event
	 */
	_onOpenTrigger: function(filename, context) {
		this.currentContext = context;
		this.file.name = filename;
		this.file.dir = context.dir;
		this.fileInfoModel = context.fileList.getModelForFile(filename);
		this.openURL(
			OCA.Files_URLShortcut.file
		);
	},

	/**
	 * Setup on page load
	 */
	initialize: function() {
		this.registerFileActions();
	},

	/**
	 * Registers the file actions
	 */
	registerFileActions: function() {
		var mimes = [
				'application/octet-stream'
			];

		_self = this;

		$.each(mimes, function(key, value) {
			OCA.Files.fileActions.registerAction({
				name: 'Files_URLShortcut',
				displayName: t('files_urlshortcut', 'Open in new Tab'),
				mime: value,
				actionHandler: _.bind(_self._onOpenTrigger, _self),
				permissions: OC.PERMISSION_READ,
				icon: function () {
					return OC.imagePath('core', 'actions/external');
				}
			});
			OCA.Files.fileActions.setDefault(value, 'Files_URLShortcut');
		});

	},

	loadURL: function(dir, filename, success, failure) {
		var _self = this;
		$.get(
			OC.generateUrl('/apps/files_urlshortcut/ajax/loadurl'),
			{
				filename: filename,
				dir: dir,
				sharingToken: $('#sharingToken').val()
			}
		).done(function(data) {
			// Call success callback
			success(OCA.Files_URLShortcut.file, data.filecontents);
		}).fail(function(jqXHR) {
			failure(JSON.parse(jqXHR.responseText).message);
		});
	},

	openURL: function(file) {
		var _self = this;
		this.loadURL(
			file.dir,
			file.name,
			function(file, data) {
				OCA.Files_URLShortcut.file.opened = true;
				window.open(data, '_blank');
			}
		)
	}

};

/*Files_URLShortcut.NewFileMenuPlugin = {

	attach: function(menu) {
		var fileList = menu.fileList;

		// only attach to main file list, public view is not supported yet
		if (fileList.id !== 'files') {
			return;
		}

		// register the new menu entry
		menu.addMenuEntry({
			id: 'file',
			displayName: t('files_urlshortcut', 'URL Shortcut'),
			templateName: t('files_urlshortcut', 'New link shortcut.url'),
			iconClass: 'icon-filetype-text',
			fileType: 'file',
			actionHandler: function(name) {
				var dir = fileList.getCurrentDirectory();
				// first create the file
				fileList.createFile(name).then(function() {
					// once the file got successfully created,
					// open the editor
					Files_Texteditor._onEditorTrigger(
						name,
						{
							fileList: fileList,
							dir: dir
						}
					);
				});
			}
		});
	}
};*/

OCA.Files_URLShortcut = Files_URLShortcut;

//OC.Plugins.register('OCA.Files.NewFileMenu', Files_URLShortcut.NewFileMenuPlugin);

$(document).ready(function () {
	OCA.Files_URLShortcut.initialize();
});