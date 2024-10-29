<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

// making sure the script won't run without host on wordpress cron
if( !defined( 'WP_CLI' ) && isset($_SERVER['HTTP_HOST']) ) {

// ==========================================================================================================
// In this page all the redirect function will start to work in order to redirect user to the correct page
// ==========================================================================================================

    // ------------------------------------------------
    // fix url with get paramaters that contain more than one ?
    // (used to fixed redirect with parameter and source with parameters than solids)
    // ------------------------------------------------

    function yydev_redirect_fix_wrong_url($url) {

        $count = substr_count($url, "?");

        if ($count > 1) {

            $last_question_mark_index = strrpos($url, "?");

            if ($last_question_mark_index !== false && $last_question_mark_index != strlen($url) - 1) {
              // Replace only the last occurrence of "?" with "&"
              $url = substr_replace($url, "&", $last_question_mark_index, 1);
            }

        } // if ($count > 1) {

        return $url;

    } // function yydev_redirect_fix_wrong_url($do_redirect) {

    // ------------------------------------------------
    // this function will allow us to do 301 redirections
    // $do_redirect is the path that we want to redirect the page to
    // ------------------------------------------------

    function yydev_redirect_redirection_redirect_301($do_redirect) {

        // fixed wrong url containing the letter ? twice
        $do_redirect = yydev_redirect_fix_wrong_url($do_redirect);

        header('Cache-Control: no-store, no-cache, must-revalidate'); // trying to stop the browser from saving the redirect
        header ('HTTP/1.1 301 Moved Permanently');
        header ('Location: ' . $do_redirect);
        exit();

    } // function yydev_redirect_redirection_redirect_301($do_redirect) {


    // ------------------------------------------------
    // this function will allow us to do 302 redirections
    // $do_redirect is the path that we want to redirect the page to
    // ------------------------------------------------

    function yydev_redirect_redirection_redirect_302($do_redirect) {

        // fixed wrong url containing the letter ? twice
        $do_redirect = yydev_redirect_fix_wrong_url($do_redirect);

        header('Cache-Control: no-store, no-cache, must-revalidate'); // trying to stop the browser from saving the redirect
        header ('HTTP/1.1 302 Found');
        header ('Location: ' . $do_redirect);
        exit();

    } // function yydev_redirect_redirection_redirect_302($do_redirect) {


    // ------------------------------------------------
    // getting the page protocol (http or https)
    // ------------------------------------------------

    function yydev_redirect_get_protocol() {
        // Set the base protocol to http
        $protocol = 'http';
        // check for https
        if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
            $protocol .= "s";
        } // if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
        
        return $protocol;

    } //function yydev_redirect_get_protocol() {


    // ------------------------------------------------
    // getting the full website link path
    // ------------------------------------------------

    $page_path_with_gets = $_SERVER['REQUEST_URI'];
    $page_path_without_get_parmaters = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    
    // cleaning the ' or " from the url
    $page_path_without_get_parmaters = str_replace( array("'", '"'), array(''. ''), $page_path_without_get_parmaters);

    // getting the $_GET[''] paramaters that on the page
    $get_parmeters_from_url = str_replace($page_path_without_get_parmaters, '', $page_path_with_gets);
    $get_parmeters_from_url = $get_parmeters_from_url;

    function yydev_redirect_get_address() {
        // return the full address
        global $page_path_with_gets;
        global $page_path_without_get_parmaters;
        return yydev_redirect_get_protocol() . '://' . $_SERVER['HTTP_HOST'] . $page_path_without_get_parmaters;
    } // function yydev_redirect_get_address() {

    
    // Getting the end of the url path (without the domain) so we can check
    // if to redirect the page to different page
    $domains_name = get_option('home');
    $url_user_request = str_ireplace($domains_name, '' , yydev_redirect_get_address()); // getting the url without the domain name
    $url_user_request = esc_url_raw($url_user_request);

    $url_user_request_strlower = strtolower($url_user_request); // makindg english caracters not capital

    $full_url_path = strtolower(yydev_redirect_get_address());

    // removing the first / from links in case the user forgot to add it.
    $url_without_slash = rtrim($url_user_request, '/');

    $urldecode_user_request = urldecode($url_user_request); // getting the url as regular caracters if it's hebrew
    $urldecode_user_request = str_replace( array("'", '"'), array(''. ''), $urldecode_user_request);

    $urldecode_user_request_without_start_slash = urldecode($url_without_slash); // getting the url as regular caracters if it's hebrew
    $urldecode_user_request_without_start_slash = str_replace( array("'", '"'), array(''. ''), $urldecode_user_request_without_start_slash);

    $url_with_end_slash = $urldecode_user_request . "/"; // making sure to take in account urls without / at the end
    $url_with_end_slash = str_replace( array("'", '"'), array(''. ''), $url_with_end_slash);


    // making sure to not block the wp-admin page
    if( !strstr($url_user_request, 'wp-login') && !strstr($url_user_request, 'https:')  ) { 

        // ------------------------------------------------------------------------
        // checking if the url exists in the data base in case of exact match 
        // redirect query and require to be redirect to different page
        // ------------------------------------------------------------------------

        // making sure it's not the home page
        if(!empty($url_user_request)) {
            
            // checking if there are database lines with the redirect similar to the corrent url
            $check_if_redirect_exists = $wpdb->get_results("SELECT * FROM " . $yydev_secondary_table_name . " WHERE request_url = '{$url_user_request}' || request_url = '{$url_user_request_strlower}' OR request_url = '{$full_url_path}' OR request_url = '{$url_without_slash}' OR request_url = '{$urldecode_user_request}' OR request_url = '{$urldecode_user_request_without_start_slash}' OR request_url = '{$url_with_end_slash}' ");

            // if there are url that require redirection 
            if( count($check_if_redirect_exists) > 0 ) {

                foreach($check_if_redirect_exists as $check_redirecting) {

                    $redirects_acmount = $check_redirecting->redirects_amount;
                    $new_redirects_acmount = $redirects_acmount + 1;

                    $current_request_url =  $check_redirecting->request_url;
                    $request_url = str_ireplace($domains_name, '' , $current_request_url); // getting the url without the domain name
                    $request_url = strtolower($current_request_url); // makindg english caracters not capital
                    $request_url_with_slash = $request_url;
                    $request_url = rtrim($current_request_url, '/'); // removing white space and the slash at the end of the link

                    $urldecode_request_url = urldecode($request_url); // getting the url as regular caracters if it's hebrew
                    $urldecode_request_url_with_slash = urldecode($request_url) . "/";

                    // ------------------------------------------------
                    // cancel the $_GET parmaters if the user blocked them
                    // ------------------------------------------------

                    if($check_redirecting->get_parameters == 1) {
                        $get_parmeters_from_url = '';
                    } // if($check_redirecting->get_parameters == 1) {

                    // ------------------------------------------------
                    // maksing sure the that $url_user_request is exactly the same as $request_url
                    // ------------------------------------------------

                    if( ($request_url === $url_user_request) || ($request_url === $url_without_slash) || ($request_url_with_slash === $full_url_path) || ($urldecode_user_request === $urldecode_request_url) || ($urldecode_user_request === $urldecode_request_url_with_slash) || ($urldecode_user_request_without_start_slash === $urldecode_request_url) || ($urldecode_user_request_without_start_slash === $urldecode_request_url_with_slash) ) {

                        $destination_url = $check_redirecting->destination_url;

                        // checking if the destination url is full url with http:// or https://
                        // and if not we will add the website domain to the destination url
                        if( !strstr($destination_url, "http://") && !strstr($destination_url, "https://") ) {
                            $destination_url = $domains_name . $destination_url;
                        } // if( !strstr($destination_url, "http://") && !strstr($destination_url, "https://") ) {

                        // ------------------------------------------------
                        // checking which redirection the user choose 301 or
                        // 302 and redirect the user to the correct path
                        // ------------------------------------------------

                        $redirect_type = $check_redirecting->redirect_type;

                        if($redirect_type === "301") {

                            // update the redirections amount
                            $wpdb->update( $yydev_secondary_table_name, array('redirects_amount'=>$new_redirects_acmount), array('id'=>$check_redirecting->id), array('%d') );
                            
                            yydev_redirect_redirection_redirect_301($destination_url . $get_parmeters_from_url);

                        } // if($redirect_type === "301") {

                        if($redirect_type === "302") {

                            // update the redirections amount
                            $wpdb->update( $yydev_secondary_table_name, array('redirects_amount'=>$new_redirects_acmount), array('id'=>$check_redirecting->id), array('%d') );

                            yydev_redirect_redirection_redirect_302($destination_url . $get_parmeters_from_url);

                        } // if($redirect_type === "302") {

                    } // if( ($request_url === $url_user_request) || ($request_url === $url_without_slash) || ($request_url_with_slash === $full_url_path) || ($urldecode_user_request === $urldecode_request_url) || ($urldecode_user_request === $urldecode_request_url_with_slash) || ($urldecode_user_request_without_start_slash === $urldecode_request_url) || ($urldecode_user_request_without_start_slash === $urldecode_request_url_with_slash) ) {

                } // foreach($check_if_redirect_exists as $check_redirecting) {

            } // if( count($check_if_redirect_exists) > 0 ) {

        } // if(!empty($url_user_request)) {

        // ------------------------------------------------------------------------
        // create a redirect for all the redirect that the user choose 
        // the redirect query will be "contain request"
        // ------------------------------------------------------------------------

        // making sure it's not the home page
        if(!empty($url_user_request)) {
            
            // checking if there are database lines with the redirect similar to the corrent url
            $check_if_redirect_exists = $wpdb->get_results("SELECT * FROM " . $yydev_secondary_table_name . " WHERE redirect_query = 'contain' ");

            // if there are url that require redirection 
            if( count($check_if_redirect_exists) > 0 ) {

                foreach($check_if_redirect_exists as $check_redirecting) {

                    $redirects_acmount = $check_redirecting->redirects_amount;
                    $new_redirects_acmount = $redirects_acmount + 1;

                    $current_request_url =  $check_redirecting->request_url;
                    $request_url = str_ireplace($domains_name, '' , $current_request_url); // getting the url without the domain name
                    $request_url = strtolower($current_request_url); // makindg english caracters not capital
                    $request_url_with_slash = $request_url;

                    // ********************************************
                    // removed this options because it ignored slased and caused bad redirects
                     // ********************************************
                    // making sure not to return empty $request_url by removing / when transfer full sites
                    // if( $current_request_url != "/") { 
                       // $request_url = rtrim($current_request_url, '/'); // removing white space and the slash at the end of the link
                    // } // if( $current_request_url != "/") { 

                    $urldecode_request_url = urldecode($request_url); // getting the url as regular caracters if it's hebrew
                    $urldecode_request_url_with_slash = urldecode($request_url) . "/";

                    // ------------------------------------------------
                    // maksing sure the that $url_user_request is contain the same content as $request_url
                    // ------------------------------------------------

                    if( strstr($url_user_request, $request_url) || strstr($url_without_slash, $request_url) || strstr($request_url_with_slash, $full_url_path) || strstr($urldecode_user_request, $urldecode_request_url) || strstr($urldecode_user_request, $urldecode_request_url_with_slash) ) {

                        $destination_url = $check_redirecting->destination_url;

                        // checking if the destination url is full url with http:// or https://
                        // and if not we will add the website domain to the destination url
                        if( !strstr($destination_url, "http://") && !strstr($destination_url, "https://") ) {
                            $destination_url = $domains_name . $destination_url;
                        } // if( !strstr($destination_url, "http://") && !strstr($destination_url, "https://") ) {

                        // ------------------------------------------------
                        // cancel the $_GET parmaters if the user blocked them
                        // ------------------------------------------------

                        if($check_redirecting->get_parameters == 1) {
                            $get_parmeters_from_url = '';
                        } // if($check_redirecting->get_parameters == 1) {

                        // ------------------------------------------------
                        // checking which redirection the user choose 301 or
                        // 302 and redirect the user to the correct path
                        // ------------------------------------------------

                        $redirect_type = $check_redirecting->redirect_type;

                        if($redirect_type === "301") {

                            // update the redirections amount
                            $wpdb->update( $yydev_secondary_table_name, array('redirects_amount'=>$new_redirects_acmount), array('id'=>$check_redirecting->id), array('%d') );
                            
                            yydev_redirect_redirection_redirect_301($destination_url . $get_parmeters_from_url);

                        } // if($redirect_type === "301") {

                        if($redirect_type === "302") {

                            // update the redirections amount
                            $wpdb->update( $yydev_secondary_table_name, array('redirects_amount'=>$new_redirects_acmount), array('id'=>$check_redirecting->id), array('%d') );

                            yydev_redirect_redirection_redirect_302($destination_url . $get_parmeters_from_url);

                        } // if($redirect_type === "302") {

                    } // if( strstr($url_user_request, $request_url) || strstr($url_without_slash, $request_url) || strstr($request_url_with_slash, $full_url_path) || strstr($urldecode_user_request, $urldecode_request_url) || strstr($urldecode_user_request, $urldecode_request_url_with_slash) ) {

                } // foreach($check_if_redirect_exists as $check_redirecting) {

            } // if( count($check_if_redirect_exists) > 0 ) {

        } // if(!empty($url_user_request)) {


    } // if( !strstr($url_user_request, 'wp-login') ) {

} // if( $_SERVER['HTTP_HOST'] ) {

?>