mw.ext.forms.form = mw.ext.forms.form || {};
mw.ext.forms.form.FormProperties = function( cfg ) {
	cfg = cfg || {};
	var sa = this;
	cfg.definition = {
		buttons: [],
		items:  [
			{
				name: 'formProperties',
				type: 'layout_booklet',
				outlined: true,
				framed: true,
				widget_listeners: {
					set: function() {
						sa.emit( 'layoutChange' );
					}
				},
				pages: [
					{
						name: 'appearance',
						label: mw.message( 'forms-label-appearance' ).text(),
						icon: 'article',
						items: [
							{
								type: 'text',
								name: 'title',
								label: mw.message( 'forms-form-editor-prop-title' ).text()
							},
							{
								type: 'checkbox',
								name: 'showTitle',
								label: mw.message( 'forms-form-editor-prop-showtitle' ).text()
							},
							{
								type: 'checkbox',
								name: 'showFormName',
								label: mw.message( 'forms-form-editor-prop-showFormName' ).text()
							},
							{
								type: 'checkbox',
								name: 'buttonsFloat',
								label: mw.message( 'forms-form-editor-prop-buttonsfloat' ).text()
							}
						]
					},
					{
						name: 'behaviour',
						label: mw.message( 'forms-label-behaviour' ).text(),
						icon: 'settings',
						items: [
							{
								type: 'category_multiselect',
								name: 'categories',
								label: mw.message( 'forms-form-editor-prop-categories' ).text()
							},
							{
								type: 'checkbox',
								name: 'sealAfterCreation',
								label: mw.message( 'forms-form-editor-prop-sealaftercreation' ).text()
							},
							{
								type: 'checkbox',
								name: 'enableProgressSave',
								label: mw.message( 'forms-form-editor-prop-enableprogresssave' ).text()
							},
							{
								type: 'checkbox',
								name: 'enableEditSummary',
								label: mw.message( 'forms-form-editor-prop-enableeditsummary' ).text()
							}
						]
					},
					{
						name: 'infrastructure',
						label: mw.message( 'forms-label-infrastructure' ).text(),
						icon: 'puzzle',
						items: [
							{
								type: 'tag',
								name: 'rlDependencies',
								widget_allowArbitrary: true,
								label: mw.message( 'forms-form-editor-prop-rl-deps' ).text(),
								help: mw.message( 'forms-form-editor-prop-rl-deps-help' ).text()
							},
							{
								type: 'checkbox',
								name: 'useFormRevs',
								label: mw.message( 'forms-form-editor-prop-use-form-revs' ).text(),
								help: mw.message( 'forms-form-editor-prop-use-form-revs-help' ).text()
							},
							{
								type: 'checkbox',
								name: 'includable',
								label: mw.message( 'forms-form-editor-prop-includable' ).text()
							},
							{
								type: 'dropdown',
								name: 'extends',
								label: mw.message( 'forms-form-editor-prop-extends' ).text(),
								options: [ { data: '', label: '-' } ].concat( mw.config.get( 'formsAvailableFormsForWidget')['abstract'] ),
								listeners: {
									change: function( val ) {
										if ( val ) {
											mw.ext.forms.editor.Extender.extendWith.call( this, val );
											this.getItem( 'abstract' ).setValue( false );
											this.getItem( 'partial' ).setValue( false );
										}
									}
								}
							},
							{
								type: 'checkbox',
								name: 'abstract',
								label: mw.message( 'forms-form-editor-prop-abstract' ).text(),
								listeners: {
									change: function( val ) {
										if ( val ) {
											this.getItem( 'partial' ).setValue( false );
											this.getItem( 'extends' ).setValue( '' );
										}
									}
								}
							},
							{
								type: 'checkbox',
								name: 'partial',
								label: mw.message( 'forms-form-editor-prop-partial' ).text(),
								listeners: {
									change: function( val ) {
										if ( val ) {
											this.getItem( 'abstract' ).setValue( false );
											this.getItem( 'extends' ).setValue( '' );
											this.getItem( 'includable' ).setValue( true );
										}
									}
								}
							}
						]
					},
					{
						name: 'target',
						label: mw.message( 'forms-label-target' ).text(),
						icon: 'articleRedirect',
						items: [
							{
								type: 'dropdown',
								name: 'target.type',
								label: mw.message( 'forms-form-editor-prop-targettype' ).text(),
								options: [],
								style: 'margin-bottom: 10px;',
								listeners: {
									change: function( val ) {
										this.selectedTarget = null;
										if ( !val ) {
											return;
										}
										var targetCB = mw.ext.forms.registry.Target.registry[val];
										if ( !targetCB ) {
											return;
										}

										var target = new targetCB( this );
										this.getItem( 'target.additional_layout' ).clearItems();
										var parsed = this.parseItems( target.getAdditionalFields(), this.getItem( 'target.additional_layout' ), true );
										target.setItems( parsed );
										this.selectedTarget = target;
									}
								}
							},
							{
								type: 'layout_fieldset',
								name: 'target.additional_layout',
								items: [],
								noLayout: true,
							},
							{
								name: 'show_target_afterAction',
								type: 'checkbox',
								label: mw.message( 'forms-form-editor-prop-targetafteraction' ).text(),
								listeners: {
									change: function( val ) {
										if ( val ) {
											return this.showItem( 'target.afterAction.type' );
										}
										this.hideItem( 'target.afterAction.type' );
										this.hideItem( 'target.afterAction.url' );
										this.hideItem( 'target.afterAction.callback' );
									}
								}
							},
							{
								type: 'dropdown',
								hidden: true,
								name: 'target.afterAction.type',
								label: mw.message( 'forms-form-editor-prop-targetafteractiontype' ).text(),
								options: [
									{
										data: '',
										label: mw.message( 'forms-form-editor-prop-targetafteractiontypenone' ).text()
									},
									{
										data: 'redirect',
										label: mw.message( 'forms-form-editor-prop-targetafteractiontyperedirect' ).text(),
									},
									{
										data: 'callback',
										label: mw.message( 'forms-form-editor-prop-targetafteractiontypecallback' ).text(),
									}
								],
								listeners: {
									change: function( val ) {
										if ( !val ) {
											this.hideItem( 'target.afterAction.url' );
											this.hideItem( 'target.afterAction.callback' );
										}
										if ( val === 'redirect' ) {
											this.showItem( 'target.afterAction.url' );
											this.hideItem( 'target.afterAction.callback' );
										}
										if ( val === 'callback' ) {
											this.hideItem( 'target.afterAction.url' );
											this.showItem( 'target.afterAction.callback' );
										}
									}
								}
							},{
								type: 'text',
								hidden: true,
								name: 'target.afterAction.url',
								label: mw.message( 'forms-form-editor-prop-targetafteractionurl' ).text()
							},{
								type: 'js_input',
								hidden: true,
								name: 'target.afterAction.callback',
								label: mw.message( 'forms-form-editor-prop-targetafteractioncallback' ).text()
							}
						]
					},
					{
						name: 'listeners_page',
						label: mw.message( 'mwstake-formengine-label-listeners' ).text(),
						icon: 'feedback',
						items: [
							{
								type: 'listeners_widget',
								required: true,
								noLayout: true,
								name: 'listeners',
								label: 'Listeners',
								events: [
									'parseComplete',
									'renderComplete',
									'initComplete',
									'beforeSubmitData',
									'progressSave',
									'submit',
									'reset',
									'cancel'
								]
							}
						]
					}
				]
			}
		]
	};
	mw.ext.forms.form.FormProperties.parent.call( this, cfg );
};

OO.inheritClass( mw.ext.forms.form.FormProperties, mw.ext.forms.standalone.Form );

mw.ext.forms.form.FormProperties.prototype.onParseComplete = function( form, items ) {
	var targetPicker = form.getItem( 'target.type' ),
		options = [];
	for( var name in mw.ext.forms.registry.Target.registry ) {
		if ( !mw.ext.forms.registry.Target.registry.hasOwnProperty( name ) ) {
			continue;
		}
		var target = new mw.ext.forms.registry.Target.registry[name]();
		options.push( {
			data: target.getName(),
			label: target.getDisplayName()
		} );
	}
	targetPicker.setOptions( options );
	if ( form.data.hasOwnProperty( 'target' ) ) {
		targetPicker.setValue( form.data.target.type );
		if ( form.selectedTarget !== null ) {
			form.selectedTarget.setValue( form.data.target );
		}
	} else {
		targetPicker.setValue( 'database' );
	}

	var sa = this;
	form.getItem( 'show_target_afterAction' ).connect( this, {
		change: function( val ) {
			sa.emit( 'layoutChange' );
		}
	} );
	form.getItem( 'target.afterAction.type' ).connect( this, {
		change: function( val ) {
			sa.emit( 'layoutChange' );
		}
	} );
	form.getItem( 'listeners' ).connect( this, {
		change: function( val ) {
			sa.emit( 'layoutChange' );
		}
	} );
};

mw.ext.forms.form.FormProperties.prototype.onBeforeSubmitData = function( form, data ) {
	if ( this.selectedTarget !== null ) {
		data.target = form.selectedTarget.getValue();
	}
	if ( !data.show_target_afterAction ) {
		delete( data.target.afterAction );
	}
	return mw.ext.forms.form.FormProperties.parent.prototype.onBeforeSubmitData.call( this, form, data );
};
