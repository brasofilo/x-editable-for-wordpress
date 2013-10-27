/* 
 * @plugin X-Editable in User Edit/Profile
 */

jQuery( document ).ready( function( $ ) 
{ 
    $.fn.editable.defaults.mode = 'popup';     
    
    //make username editable
    $('#my_username').editable( {
        params: function(params) {
            params.action = 'x_editable_profile';
            params.security = xep_ajax.ajaxnonce;
            params.user_id = xep_ajax.user_id;
            return params;
        },
        url: xep_ajax.ajaxurl,
        success: function(response) {
            if( !response.success )
            {
                if( !response.data ) // failed nonce
                    console.log( 'Ajax ERROR: wrong referrer' );
                else // wp_send_json_error
                    console.log( response.data.error );
            }
            else // wp_send_json_success
                console.dir( response.data );
        }
    });
    
    //make status editable
    $('#my_status').editable({
        source: [
            {value: 1, text: 'status 1'},
            {value: 2, text: 'status 2'},
            {value: 3, text: 'status 3'}
        ],
        params: function(params) {
            params.action = 'x_editable_profile';
            params.security = xep_ajax.ajaxnonce;
            params.user_id = xep_ajax.user_id;
            return params;
        },
        url: xep_ajax.ajaxurl,
        success: function(response) {
            if( !response.success )
            {
                if( !response.data ) // failed nonce
                    console.log( 'Ajax ERROR: wrong referrer' );
                else // wp_send_json_error
                    console.log( response.data.error );
            }
            else // wp_send_json_success
                console.dir( response.data );
        }
    });
});
