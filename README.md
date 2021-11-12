# Forms

_HINT: THIS IS JUST A PROOF OF CONCEPT! IT IS NOT PRODUCTION READY!_

## Installation
Execute

    composer require mediawiki/forms dev-REL1_31
within MediaWiki root or add `mediawiki/forms` to the
`composer.json` file of your project

## Activation
Add

    wfLoadExtension( 'Forms' );
to your `LocalSettings.php` or the appropriate `settings.d/` file.

## Usage

Create a page `Someform.form` somewhere in the wiki with content like this

    {
        title: "Request vacation",
        target: {
            type: 'email', //May also be 'templates-on-wikipage', which is default and takes other parameters
            receivers: [ 'serverside-configured-receiver-1', 'serverside-configured-receiver-2' ]
        },
        items: [
            {
                type: 'panel',
                html: 'This is the vacation registration from. Please fill in the required information and submit it.'
            },
            {
                type: 'currentuser' //Hidden by default
            },
            {
                type: 'fieldset',
                name: 'dates', //When writing templates to a wiki page, this could be the target template name
                layout: 'hbox',
                items: [
                    {
                        type: 'datepicker',
                        name: 'from',
                        fielLabel: 'Begin'
                    },
                    {
                        type: 'datepicker',
                        name: 'end',
                        fielLabel: 'End',
                        //Even better would be a dedicated field type "daterangepicker" that incorporates two 'datepicker' and wires them up automatically
                        getStart: function( form ) {
                            return form.getField( 'from' ).getValue();
                        }
                    }
                ]
            },
            {
                type: 'textarea',
                name: 'notes',
                fieldLabel: 'Notes',
                labelAlign: 'top'
            }
        ]
    }

## TODOS
- Make sure only trustworthy users can edit a `*.form` page, as it contains JavaScript!