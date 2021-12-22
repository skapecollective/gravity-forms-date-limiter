( function( $ ) {
	'use strict';

	const fieldGroups = [
		'min',
		'min_modifier',
		'max',
		'max_modifier',
		'days_of_the_week'
	];

	// Adding field groups to any fields with a of type "date"
	for ( let i = 0; i < fieldGroups.length; i++ ) {
		var fieldGroup = fieldGroups[ i ];
		fieldSettings.date += ', [data-date-limiter="' + fieldGroup + '"]';
	}

	$( document ).bind( 'gform_load_field_settings', function( event, field, form ) {

		const allGroups = () => {
			return $( '[data-date-limiter="' + fieldGroups.join( '"], [data-date-limiter="' ) + '"]' );;
		};

		/**
		 * @param {string|null} fieldName
		 * @return {boolean|mixed|mixed[]}
		 */
		const getValues = ( fieldName ) => {
			let values = field.gravityFormsDateLimiter || [];

			if ( fieldName ) {
				values = values.filter( value => value.name === fieldName );
				values = values.map( value => value.value );

				if ( values.length > 1 ) {
					return values;
				}

				return values[ 0 ] || null;
			}

			return null;
		}

		const storeValues = () => {
			SetFieldProperty( `gravityFormsDateLimiter`, allGroups().find( '[name]:input' ).serializeArray() );
		};

		// Conditional fields.
		$( document ).on( 'update', '[data-date-limiter-show-when]', function() {
			const $this = $( this );
			const query = $this.data( 'date-limiter-show-when' );
			const queryParts = query.split( /\s+/ );

			const target = queryParts[ 0 ];
			const operator = ( queryParts[ 1 ] || '' ).toUpperCase();
			var compare = queryParts[ 2 ];

			const $groups = allGroups();
			const $target = $groups.find( `[name="${target}"]:input` );
			const targetValue = $target.val();

			// Nullify compare value
			if ( compare && compare.toString().toLowerCase() === 'null' ) {
				compare === null;
			}

			// Empty string compare value
			if ( compare && compare.toString().toLowerCase() === "''" ) {
				compare === '';
			}

			let matched = false;

			switch ( operator ) {
				case '=':
					matched = targetValue === compare;
					break;
				case '!=':
					matched = targetValue !== compare;
					break;
				case '>':
					matched = targetValue > compare;
					break;
				case '<':
					matched = targetValue < compare;
					break;
				case '>=':
					matched = targetValue >= compare;
					break;
				case '<=':
					matched = targetValue <= compare;
					break;
				case 'ANY':
					matched = targetValue || !!targetValue;
					break;
				case 'NONE':
					matched = !targetValue;
					break;
			}

			if ( matched ) {
				$this.show();
			} else {
				$this.hide();
			}
		} );

		// Field value handling.
		for ( let i = 0; i < fieldGroups.length; i++ ) {
			const fieldGroup = fieldGroups[ i ];
			const $group = $( '[data-date-limiter="' + fieldGroup + '"]' );

			$group.find( '[name]:input' ).each( function() {
				const $field = $( this );
				const name = $field.attr( 'name' );
				const value = getValues( name );

				if ( value ) {
					$field.val( value ).trigger( 'input' );
				}

				$field.on( 'change input', event => {
					storeValues();
					$( '[data-date-limiter-show-when]' ).trigger( 'update' );
				} );
			} );
		}

		$( '[data-date-limiter-show-when]' ).trigger( 'update' );

	} );

} )( jQuery );
