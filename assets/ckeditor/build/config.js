/**
 * @license Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Disable plugins that have no external plugin.js or load conflicting jQuery
    config.removePlugins = 'Bootstrap-Mediaembed,bootstrapTable,bootstrapTabs,ckawesome,OpenStreet,pastecode,ajax,pastetools,pastefromgdocs,pastefromlibreoffice,pastefromword,exportpdf';
};
