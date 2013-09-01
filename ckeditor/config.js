/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	config.entities = false; // Pour que les accents ne soient pas transformés en entités HTML (ce qui est inutile avec le codage utf-8 des pages)
	config.height = '300px'; // hauteur de la fenêtre d'édition (par défaut : 200px)

};
