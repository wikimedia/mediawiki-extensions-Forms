( function ( mw, $, undefined ) {
	mw.ext.forms.widget.Form = function( cfg ) {
		mw.ext.forms.widget.Form.parent.call( this, {} );

		this.definitionName = cfg.definitionName;
		if ( !this.definitionName ) {
			return;
		}

		this.action = cfg.action || 'view';
		// Values for form fields
		this.data = cfg.data || {};
		this._id = this.data._id || null;

		this.layout = new OO.ui.FieldsetLayout( {} );

		mw.ext.forms.mixin.FormLoadingAlert.call( this, false );
		mw.ext.forms.mixin.Message.call( this );
		mw.ext.forms.mixin.BookletLayoutParser.call( this );
		mw.ext.forms.mixin.IndexLayoutParser.call( this );

		this.getDefinition( this.definitionName )
			.done( function( definition ) {
				this.definition = definition;
				mw.ext.forms.mixin.DefinitionParser.call( this );
				this.invokeFormListeners( 'parseComplete' );
				if ( this.editMode ) {
					this.buttonsToShow = definition.buttons || [ 'submit', 'reset' ];
					mw.ext.forms.mixin.FloatableButtons.call( this );
					mw.ext.forms.mixin.EditSummary.call( this );
				}
				this.setLoadingVisibility( false );
				this.$element.append( this.layout.$element );
				this.invokeFormListeners( 'renderComplete' );
				if ( this.editMode ) {
					this.wireFormButtons();
				}
				this.invokeFormListeners( 'initComplete' );
			}.bind( this ) )
			.fail( function( error ) {
				this.setLoadingVisibility( false );
				this.errorMessage( error, false );
			}.bind( this ) );


		this.$element.addClass( 'mw-ext-forms-form' );
	};

	OO.inheritClass( mw.ext.forms.widget.Form, OO.ui.Widget );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.DefinitionParser );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.BookletLayoutParser );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.IndexLayoutParser );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.FormLoadingAlert );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.Message );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.FloatableButtons );
	OO.mixinClass( mw.ext.forms.widget.Form, mw.ext.forms.mixin.EditSummary );

	mw.ext.forms.widget.Form.static.tagName = 'div';

	mw.ext.forms.widget.Form.prototype.getDefinition = function( defName, defObject, dfd ) {
		defObject = defObject || {};
		dfd = dfd || $.Deferred();

		this.runApiCall( {
			action: 'forms-get-definitions',
			type: 'get-definition',
			name: defName
		} ).done( function ( response )  {
			if ( response.success === 0 ) {
				dfd.reject( response.error );
			}

			var definition;
			eval( "definition =" + response.result );
			if ( typeof definition !== 'object' ) {
				dfd.reject( mw.message( 'forms-error-invalid-form' ).text() );
			}
			if ( definition.hasOwnProperty( 'extends' ) ) {
				return this.getDefinition( definition.extends, definition, dfd );
			}
			definition = $.extend( definition, defObject );
			dfd.resolve( definition );
		}.bind( this ) ).fail( function( error ) {
			dfd.reject( mw.message( 'forms-api-generic-error' ).text() );
		} );

		return dfd.promise();
	};

	mw.ext.forms.widget.Form.prototype.runApiCall = function( data, postIt ) {
		var api = new mw.Api();
		if ( postIt ) {
			return api.postWithToken( 'edit', data );
		}
		return api.get( data );
	};

	mw.ext.forms.widget.Form.prototype.wireFormButtons = function() {
		this.submitButton.on( 'click', this.submitForm.bind( this ) );
		this.resetButton.on( 'click', this.resetForm.bind( this ) );
		this.cancelButton.on( 'click', this.cancelEdit.bind( this ) );
	};

	mw.ext.forms.widget.Form.prototype.submitForm = function() {
		this.validateForm()
			.done( function() {
				this.clearMessage();
				this.submitButton.setDisabled( true );
				this.invokeFormListeners( 'submit' )
					.done( function() {
						this.submitDataToTarget();
					}.bind( this ) )
					.fail( function( error ) {
						this.errorMessage( error, true ) ;
					}.bind( this ) );
			}.bind( this ) )
			.fail( function() {
				this.errorMessage( mw.message( 'forms-form-validation-failed' ).text(), true );
			}.bind( this ) );

	};

	mw.ext.forms.widget.Form.prototype.getData = function() {
		var dfd = $.Deferred(), data = {};

		var inputs = [];
		for ( var name in this.items.inputs ) {
			if ( !this.items.inputs.hasOwnProperty( name ) ) {
				continue;
			}
			var widget = this.items.inputs[name];
			inputs[name] = widget;
		}

		if ( this.sealAfterCreation === true ) {
			data._sealed = true;
		}

		this.getDataForItems( inputs, dfd, data );
		return dfd.promise();
	};

	mw.ext.forms.widget.Form.prototype.getDataForItems = function( items, dfd, data ) {
		if ( $.isEmptyObject( items ) ) {
			return dfd.resolve( data );
		}
		let name = Object.keys( items )[0];
		let widget = items[name];
		let value = widget.getValue();
		if ( typeof value.then === 'function' ) {
			value.done( function( val ) {
				delete( items[name] );
				data[name] = val;
				this.getDataForItems( items, dfd, data );
			}.bind( this ) );
		} else {
			delete( items[name] );
			data[name] = value;
			this.getDataForItems( items, dfd, data );
		}
	};

	mw.ext.forms.widget.Form.prototype.getSavedDataFor = function( item ) {
		if ( this.data.hasOwnProperty( item ) ) {
			return this.data[item];
		}
		return false;
	};

	mw.ext.forms.widget.Form.prototype.resetForm = function() {
		this.invokeFormListeners( 'reset' );
		for ( var name in this.items.inputs ) {
			if ( !this.items.inputs.hasOwnProperty( name ) ) {
				continue;
			}
			if ( this.data.hasOwnProperty( name ) ) {
				this.items.inputs[name].setValue( this.data[name] );
			} else {
				this.items.inputs[name].setValue();
			}
		}
	};

	mw.ext.forms.widget.Form.prototype.getAction = function() {
		return this.action;
	};


	mw.ext.forms.widget.Form.prototype.cancelEdit = function() {
		if ( this.action !== 'edit' ) {
			// For sanity
			return;
		}
		this.invokeFormListeners( 'cancel' );
	};

	// Single listener for now
	mw.ext.forms.widget.Form.prototype.invokeFormListeners = function( eType ) {
		var dfd = $.Deferred();
		if ( !( eType in this.formListeners ) ) {
			return dfd.resolve();
		}
		var func = this.formListeners[eType];
		if ( typeof func !== 'function' ) {
			return dfd.resolve();
		}
		func.call( this )
			.done( function( response ) {
				dfd.resolve( response )
			} )
			.fail( function( error ) {
				dfd.reject( error );
			} );
		return dfd.promise();
	};

	mw.ext.forms.widget.Form.prototype.validateForm = function() {
		var dfd = $.Deferred();
		var inputsToValidate = $.extend( {}, this.items.inputs );
		this.validateInternally( inputsToValidate, dfd );
		return dfd.promise();
	};

	mw.ext.forms.widget.Form.prototype.validateInternally = function( inputs, dfd ) {
		if ( $.isEmptyObject( inputs ) ) {
			return dfd.resolve();
		}
		var keys = Object.keys( inputs );
		var validatingName = keys[0];
		var validatingInput = inputs[validatingName];

		if ( typeof validatingInput.getValidity !== 'function' ) {
			// Some inputs do not support validation - skip over them
			delete( inputs[validatingName] );
			return this.validateInternally( inputs, dfd );
		}

		validatingInput.getValidity()
			.done( function() {
				delete( inputs[validatingName] );
				this.validateInternally( inputs, dfd );
			}.bind( this ) )
			.fail( function() {
				validatingInput.focus();
				validatingInput.setValidityFlag( false );
				dfd.reject( validatingName );
			} );
	};

	mw.ext.forms.widget.Form.prototype.submitDataToTarget = function() {
		if ( this.action === 'edit' && this._id !== null ) {
			this.target._id = this._id;
		}
		this.getData().done( function( data ) {
			var apiData = {
				action: 'forms-form-submit',
				form: this.definitionName,
				target: JSON.stringify( this.target ),
				data: JSON.stringify( data )
			};
			if ( this.enableEditSummary ) {
				apiData.summary = this.summaryInput.getValue();
			}
			this.runApiCall( apiData, true ).done( function( response ) {
				if ( response.success === 1 ) {
					this.successMessage( mw.message( 'forms-form-submit-success' ).text() );
					this.executeAfterAction( response.result, response.defaultAfterAction );
				} else {
					this.emit( 'submitFailed', response.error );
					this.errorMessage( response.error );
				}
				this.submitButton.setDisabled( false );
			}.bind( this ) )
				.fail( function( code, response ) {
					let error = mw.message( 'forms-api-generic-error' ).text();
					if ( response.hasOwnProperty( 'error' ) ) {
						error = response.error;
					}
					this.emit( 'submitFailed', error );
					this.errorMessage( error );
					this.submitButton.setDisabled( false );
				}.bind( this ) );
		}.bind( this ) );

	};

	mw.ext.forms.widget.Form.prototype.executeAfterAction = function( result, defaultAction ) {
		var action = defaultAction || {};
		var isCustom = false;
		if ( this.definition.target.hasOwnProperty( 'afterAction' ) &&
			this.definition.target.afterAction.hasOwnProperty( 'type' ) ) {
			action = this.definition.target.afterAction;
			isCustom = true;
		}
		if ( !action ) {
			return;
		}
		var type = action.type;

		if ( type === 'callback' ) {
			this.doExecuteAfterAction( action, result ).done( function( block ) {
				if ( isCustom && !block ) {
					this.doExecuteAfterAction( defaultAction, result, false );
				}
			}.bind( this ) );
		} else {
			this.doExecuteAfterAction( action, result );
		}
	};

	mw.ext.forms.widget.Form.prototype.doExecuteAfterAction = function( action, result ) {

		var type = action.type;
		switch( type ) {
			case 'redirect':
				if ( !action.hasOwnProperty( 'url' ) ) {
					return;
				}
				window.location = action.url;
				break;
			case 'callback':
				if ( !action.hasOwnProperty( 'callback' ) ) {
					return;
				}
				return action.callback.call( this, result );
		}
	};
} )( mediaWiki, jQuery );
