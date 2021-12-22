( function( $ ) {
    'use strict';

    $( document ).bind( 'gform_post_render', function( event, formId ) {
        const dataKey = `gravityFormsDateLimiter${formId}`;

        if ( dataKey in window ) {
            const localData = window[ dataKey ];

            gform.addFilter( 'gform_datepicker_options_pre_init', function( optionsObj, formId, fieldId ) {

                $.each( localData.fields, function( index, field ) {
                    if ( formId == field.formId && fieldId == field.fieldId ) {

                        const fieldData = field.dateLimiter;

                        // Minimum date
                        if ( fieldData.min_date ) {
                            optionsObj.minDate = new Date( fieldData.min_date );
                        }

                        // Maximum date
                        if ( fieldData.max_date ) {
                            optionsObj.maxDate = new Date( fieldData.max_date );
                        }

                        // Days of the week.
                        if ( fieldData.days_of_the_week ) {
                            const daysOfTheWeek = $.map( fieldData.days_of_the_week, num => parseInt( num ) );

                            optionsObj.beforeShowDay = function( date ) {
                                const day = date.getDay();
                                if ( daysOfTheWeek.includes( day ) ) {
                                    return [ true, '' ];
                                } else {
                                    return [ false, '' ];
                                }
                            };
                        }

                    }
                } );

                return optionsObj;
            } );
        }
    } );

} )( jQuery );
