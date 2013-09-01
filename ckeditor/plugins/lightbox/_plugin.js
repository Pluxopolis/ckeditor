
/**************************************
    Webutler V2.2 - www.webutler.de
    Copyright (c) 2008 - 2012
    Autor: Sven Zinke
    Free for any use
    Lizenz: GPL
**************************************/


( function()
{
    CKEDITOR.plugins.add( 'lightbox', { lang : [CKEDITOR.lang.detect(CKEDITOR.config.language)] } );
    
    CKEDITOR.on( 'dialogDefinition', function( ev )
    {
    	var dialogName = ev.data.name;
    	var dialogDefinition = ev.data.definition;
    	var editor = ev.editor;
    	
    	if ( dialogName == 'image' )
    	{
        	function getSelectedLink( editor )
        	{
        		try
        		{
        			var selection = editor.getSelection();
        			if ( selection.getType() == CKEDITOR.SELECTION_ELEMENT )
        			{
        				var selectedElement = selection.getSelectedElement();
        				if ( selectedElement.is( 'a' ) )
        					return selectedElement;
        			}
        
        			var range = selection.getRanges( true )[ 0 ];
        			range.shrink( CKEDITOR.SHRINK_TEXT );
        			var root = range.getCommonAncestor();
        			return root.getAscendant( 'a', true );
        		}
        		catch( e ) { return null; }
        	}
        	
        	function updateRelfield()
        	{
                var d = CKEDITOR.dialog.getCurrent();
                
                var ZoomField = d.getContentElement( 'info', 'BoxZoom' );
                    NextField = d.getContentElement( 'info', 'BoxNext' ),
                    NumField = d.getContentElement( 'info', 'BoxNum' ),
                    RelField = d.getContentElement( 'info', 'BoxRel' );
                var RelValue = '';
                    
                if(ZoomField.getValue() == true) {
                    RelValue = 'lightbox';
                    if(NextField.getValue() == true && NumField.getValue() != '') {
                        RelValue = RelValue + NumField.getValue();
                    }
                    RelField.setValue(RelValue);
                }
                else {
                    RelField.setValue('');
                }
        	}
    
            var infoTab = dialogDefinition.getContents('info');
            
            infoTab.add(
            {
    			type : 'vbox',
    			padding : 0,
    			children :
    			[
                    {
            			type : 'html',
            			html : editor.lang.lightbox.title
                    },
                    {
            			type : 'hbox',
                        style : 'margin:0px;width:100px;vertical-align: middle;',
            			children :
                        [
                            {
                    			type : 'html',
                    			html : editor.lang.lightbox.imgzoom,
                                style : 'display:block;margin-top:3px;'
                            },
                            {
                    			type : 'checkbox',
                    			id : 'BoxZoom',
                    			label : ' ',
                    			'default' : '',
                                style : 'margin-right:5px;padding:0px',
                    			//onClick : function ()
                    			onChange : function ()
                    			{
                                    var d = CKEDITOR.dialog.getCurrent();
                                    
                                    var UrlField = d.getContentElement( 'info', 'txtUrl' ),
                                        NextField = d.getContentElement( 'info', 'BoxNext' ),
                                        NumField = d.getContentElement( 'info', 'BoxNum' ),
                                        LinkField = d.getContentElement( 'Link', 'txtUrl' ),
                                        orgLnkUrl = UrlField.getValue(),
                                        boxLnkUrl;
                                   
                                    if(orgLnkUrl != '') {
                                        var fileName = orgLnkUrl.substr( orgLnkUrl.lastIndexOf('/') + 1 );
                                        var filePath = orgLnkUrl.substring(0, orgLnkUrl.lastIndexOf('/'));
                                       // PLUXML boxLnkUrl = filePath + '/.box/' + fileName;
									    boxLnkUrl = filePath + '/' + fileName.replace('.tb.', '.');
                                        //dialogDefinition.removeContents( 'Link' );
                                    }
                                    
                                    if(UrlField.getValue() == '')
                                        this.setValue( false, 'change' );
                                    
                                    if(this.getValue() == false) {
                                        if(NextField.getValue() == true)
                                            NextField.setValue( false, 'change' );
                                        NumField.setValue( '' );
                                        LinkField.setValue( '' );
                                    }
                                    else {
                                        LinkField.setValue( boxLnkUrl );
                                        //dialogDefinition.removeContents( 'Link' );
                                    }
                                    
                                    updateRelfield();
                    			},
                    			onShow : function ()
                    			{
                                    var d = CKEDITOR.dialog.getCurrent();
                                    
                                    var UrlField = d.getContentElement( 'info', 'txtUrl' ),
                                        LinkField = d.getContentElement( 'Link', 'txtUrl' ),
                                        orgUrlField = UrlField.getValue();
                                        orgLinkField = LinkField.getValue();
                                    
                                    if(orgUrlField != '') {
                                        var fileName = orgUrlField.substr( orgUrlField.lastIndexOf('/') + 1 );
                                        var filePath = orgUrlField.substring(0, orgUrlField.lastIndexOf('/'));
                                        // PLUXML var boxLnkUrl = filePath + '/.box/' + fileName;
										boxLnkUrl = filePath + '/' + fileName.replace('.tb.', '.');
                                    }
                                    if(orgLinkField == boxLnkUrl) {
                                        this.setValue( true, 'change' );
                                        //dialogDefinition.removeContents( 'Link' );
                                    }
                                    else {
                                        this.setValue( false, 'change' );
                                    }
                    			}
                            },
                            {
                    			type : 'html',
                    			html : editor.lang.lightbox.browse,
                                style : 'display:block;margin-top:3px;'
                            },
                            {
                    			type : 'checkbox',
                    			id : 'BoxNext',
                    			label : ' ',
                    			'default' : '',
                                style : 'margin-right:5px;padding:0px',
                    			onClick : function ()
                    			{
                                    var d = CKEDITOR.dialog.getCurrent();
                                    var ZoomField = d.getContentElement( 'info', 'BoxZoom' );
                                    var NumField = d.getContentElement( 'info', 'BoxNum' );
                                    
                                    if(ZoomField.getValue() == false && this.getValue() == true)
                                        this.setValue( false, 'change' );
                                    
                                    if(this.getValue() == false)
                                        NumField.setValue( '' );
                                    else
                                        NumField.setValue( '1' );
                                    
                                    updateRelfield();
                    			}
                            },
                            {
                    			type : 'html',
                    			html : editor.lang.lightbox.boxnum + ':',
                                style : 'display:block;margin-top:3px;'
                            },
                            {
                    			type : 'text',
                    			id : 'BoxNum',
                    			label : ' ',
                    			'default' : '',
                                style : 'width:25px',
            					validate : CKEDITOR.dialog.validate.integer(editor.lang.lightbox.mustint),
            					onKeyUp : function()
            					{
                                    var d = CKEDITOR.dialog.getCurrent();
                                    var ZoomField = d.getContentElement( 'info', 'BoxZoom' );
                                    var NextField = d.getContentElement( 'info', 'BoxNext' );
                                    
                                    if(ZoomField.getValue() == false || NextField.getValue() == false)
                                        this.setValue( '' );
                                    
                                    if(ZoomField.getValue() != false && NextField.getValue() != false && this.getValue() <= 0)
                                        this.setValue('1');
                                    
                                    updateRelfield();
            					}
                            },
                            {
                    			type : 'text',
                    			id : 'BoxRel',
                    			label : '',
                    			'default' : '',
                    			'style' : 'display: none',
                    			onHide : function ()
                    			{
                                    this.setValue( '' );
    							},
    							setup : function()
    							{
                                    var link = getSelectedLink( editor );
                                    if(link) {
        								this.setValue( link.getAttribute( 'rel' ) || '' );
        								
                                        var d = CKEDITOR.dialog.getCurrent();
                                        
                                        var LinkField = d.getContentElement( 'Link', 'txtUrl' ),
                                            orgLinkField = LinkField.getValue(),
                                            ZoomField = d.getContentElement( 'info', 'BoxZoom' ),
                                            NextField = d.getContentElement( 'info', 'BoxNext' ),
                                            NumField = d.getContentElement( 'info', 'BoxNum' );
                        				
                                        if(orgLinkField != '' && this.getValue() != '') {
                                            var RelAttr = this.getValue();
                                            relLight = RelAttr.substring(0, 8);
                                            if(relLight == 'lightbox') {
                                                ZoomField.setValue( true, 'change' );
                                                relNr = RelAttr.substr(8);
                                                if(relNr != '') {
                                                    NextField.setValue( true, 'change' );
                                                    NumField.setValue( relNr );
                                                }
                                            }
                                        }
                                    }
                    			},
    							//commit : function( element )
    							commit : function() //ie
                    			//onOk : function () //ff
    							{
    								if ( this.getValue() != '' ) {
    									this.linkElement = this.getDialog().linkElement;
                                        this.linkElement.setAttribute( 'rel', this.getValue() );
    								}
    							}
                            }
                        ]
                    },
        		]
            }, 'txtAlt' );
    	}
    });
})();


