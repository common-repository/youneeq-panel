/**
 * Convert date inputs to use jQuery UI datepicker
 * if the browser does not support the date input type.
 *
 * @param {string|HTMLElement} selector jQuery selector to search for, or HTML element.
 * @param {HTMLElement}        context  HTML element in which to apply selector.
 */
function yq_date_fix( selector = 'input[type="date"]', context = null ) {
    if ( !Modernizr || !Modernizr.inputtypes || !Modernizr.inputtypes.date ) {
        ( context ? jQuery( selector, context ) : jQuery( selector ) )
        .attr( 'type', 'text' )
        .attr( 'pattern', yq_date_fix_l10n.regex_iso_8601 )
        .attr( 'placeholder', yq_date_fix_l10n.placeholder )
        .datepicker({
            'dateFormat': jQuery.datepicker.ISO_8601,
            'firstDay': parseInt( yq_date_fix_l10n.first_day ),
            'dayNames': yq_date_fix_l10n.day_names,
            'dayNamesMin': yq_date_fix_l10n.day_names_min,
            'dayNamesShort': yq_date_fix_l10n.day_names_short,
            'monthNames': yq_date_fix_l10n.month_names,
            'monthNamesShort': yq_date_fix_l10n.month_names_short
        });
    }
}
