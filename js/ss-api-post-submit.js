jQuery( document ).ready( function ( $ ) {
    $( '.ss-api-form-submit-post' ).on( 'submit', function( e ) {
        e.preventDefault();

        var ss_post_title   = $( '#ss-input-post-title' ).val();
        var ss_post_excerpt = $( '#ss-input-post-excerpt' ).val();
        var ss_post_content = $( '#ss-input-post-excerpt' ).val();
 
        var ss_post_data = {
            title: ss_post_title,
            excerpt: ss_post_excerpt,
            content: ss_post_content,
            status: 'publish'
        };
 
        $.ajax( {
            method: "POST",
            url: ss_api_post_submit_action.root + 'wp/v2/posts',
            data: ss_post_data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', ss_api_post_submit_action.nonce );
            },
            success : function( response ) {
                alert( ss_api_post_submit_action.success );
            },
            fail : function( response ) {
                alert( ss_api_post_submit_action.failure );
            }
 
        } );
 
    });
 
} );