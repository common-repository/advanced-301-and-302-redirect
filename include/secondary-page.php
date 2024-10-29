<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

$success_message = '';
$post_error_message = '';

// the id number for this page
$secondary_page_id = intval($_GET['id']);

// ====================================================
// Include the file that contains all the info
// ====================================================
include('settings.php');

// getting the page url for the settings page
$plugin_page_url = esc_url( menu_page_url( 'yydev-redirection', false ) );

// ====================================================
// Redirect the user the to main page
// if the data was not found
// ====================================================

    if( isset($secondary_page_id) && !empty($secondary_page_id) && is_numeric($secondary_page_id) ) {
        
        global $wpdb;
        $check_for_real_data_id = $wpdb->query("SELECT id FROM " . $yydev_redirect_table_name . " WHERE id = " . $secondary_page_id);
        
        if($check_for_real_data_id == 0) {
            $post_error_message = "The redirect id you were looking for was not found";
        } // if($check_for_real_data_id < 1 ) {
    
    } else { // if( isset($secondary_page_id) && !empty($secondary_page_id) && is_numeric($secondary_page_id) ) {
        $post_error_message = "The redirect id you were looking for was not found";
    } // } else { // if( isset($secondary_page_id) && !empty($secondary_page_id) && is_numeric($secondary_page_id) ) {

// ====================================================
// Update the main database if it's changed
// ====================================================
    
if( isset($_POST['yydev_redirect_nonce_edit_main_db']) ) {

    if( wp_verify_nonce($_POST['yydev_redirect_nonce_edit_main_db'], 'yydev_redirect_action_edit_main_db') ) {

        $main_deta_id = intval($_POST['form_id']);

        if( !empty($main_deta_id) ) {

                // If there is no error insert the info to the database
                $redirections_name = sanitize_text_field($_POST['redirections_name']);

                $redirections_slug = str_replace(" ", "_", strtolower(trim($redirections_name)));
                $redirections_slug = sanitize_text_field($redirections_slug);

                // Checking if the main database id exists
                $check_database_exists = $wpdb->query("SELECT id FROM " . $yydev_redirect_table_name . " where id = " . $main_deta_id);

                if($check_database_exists == 0 ) {
                    $post_error_message = "The redirect id was not found";
                } else { // if($check_database_exists < 1 ) {
                
                // If the main database id exists it will update it
                $wpdb->update( $yydev_redirect_table_name,
                    array('name'=>$redirections_name,
                    'slug'=>$redirections_slug,
                    ), array('id'=>$main_deta_id), array('%s', '%s') );

                    // Creating page link and redirect the user the current page with the new data
                    $new_detabase_id = $wpdb->insert_id;
                    $success_message = "The redirect was updated successfully";;

                } // } else { // if($check_database_exists < 1 ) {
                
        } // if( !empty($main_deta_id) ) {

    } else { // if( wp_verify_nonce($_POST['yydev_redirect_nonce_edit_main_db'], 'yydev_redirect_action_edit_main_db') ) {
        $post_error_message = "Form nonce was incorrect";
    } // } else { // if( wp_verify_nonce($_POST['yydev_redirect_nonce_edit_main_db'], 'yydev_redirect_action_edit_main_db') ) {

} // if( isset($_POST['yydev_redirect_nonce_edit_main_db']) ) {

// ====================================================
// Removing secondary form data if it was deleted
// ====================================================
    
if( isset($_GET['remove-secondary-form']) && isset($_GET['secondary_id']) && !empty( intval($_GET['secondary_id']) ) ) {
    
    $secondary_redirect_id = intval($_GET['secondary_id']);
    $check_secondary_data_id = $wpdb->query("SELECT id FROM " . $yydev_secondary_table_name . " WHERE id = " . $secondary_redirect_id );
    
    if($check_secondary_data_id > 0) {
        // if the secondary data id exists on the database it will be removed
        
        $wpdb->delete( $yydev_secondary_table_name, array('id'=>$secondary_redirect_id) );

        $success_message = "The redirect was removed successfully";
        $new_page_link = $plugin_page_url . "&view=secondary&id=" . $secondary_page_id . "&message=" . urlencode($success_message);
        wp_redirect($new_page_link);

    } else { // if($check_secondary_data_id > 0) {
        $post_error_message = "The redirect id was not found and not deleted";
    } // } else { // if($check_secondary_data_id > 0) {
    
}  // if( isset($_GET['remove-secondary-form']) && isset($_GET['secondary_id']) && !empty( intval($_GET['secondary_id']) ) ) {

// ====================================================
// Add new secondary deta to the database
// ====================================================

if( isset($_POST['yydev_redirect_nonce_secondary_deta']) ) {

    if( wp_verify_nonce($_POST['yydev_redirect_nonce_secondary_deta'], 'yydev_redirect_action_secondary_deta') ) {

        if( isset($_POST['secondary_page_id_post']) && !empty($_POST['secondary_page_id_post'])  ) {

            $secondary_page_id_post = intval($_POST['secondary_page_id_post']);

            // Getting new position number
            $new_position = 1;
            $checking_first_position = $wpdb->get_results("SELECT * FROM " . $yydev_secondary_table_name . " WHERE secondary_id = " . $secondary_page_id_post . " ORDER BY position ASC limit 1");
            foreach($checking_first_position as $wordpress_position) {
                $new_position = $wordpress_position->position / 2;
            } // foreach($checking_first_position as $wordpress_position) {

            // incase there are a lot of data and the new position is 0
            // we will reorder all the options
            if( $new_position == 0 ) {

                $checking_redirect_positions = $wpdb->get_results("SELECT * FROM " . $yydev_secondary_table_name . " WHERE secondary_id = " . $secondary_page_id_post . " ORDER BY position ASC");
                $redirect_postion = 1;

                foreach($checking_redirect_positions as $wordpress_positions) {
                    $this_redirect_id = $wordpress_positions->id;
                    $wpdb->update( $yydev_secondary_table_name,array('position'=>$redirect_postion), array('id'=>$this_redirect_id), array('%f') );
                    $redirect_postion++;
                } // foreach($checking_first_position as $wordpress_position) {

                $new_position = 0.5;

            } // if( $new_position == 0 ) {
                
            // If there is no error insert the info to the database
            $main_deta_id = intval($secondary_page_id_post);
            $redirect_type = intval($_POST['redirect_type']);
            $advertising_platform = sanitize_text_field($_POST['advertising_platform']);
            $new_data_position = floatval($new_position);
            $request_url = esc_url_raw( $_POST['request_url'] );
            $destination_url = esc_url_raw( $_POST['destination_url'] );
            $strtotime = sanitize_text_field( strtotime("now") );
            $redirect_query = sanitize_text_field($_POST['redirect_query']);
            $get_parameters = intval($_POST['get_parameters']);
            
            // Checking if the data id exists
            $check_database_exists = $wpdb->query("SELECT id FROM " . $yydev_redirect_table_name . " where id = " . $main_deta_id);
                    
            if($check_database_exists == 0 ) {
                $post_error_message = "The redirect id was not found";
                $redirect_error = 1;
            } // if($check_database_exists == 0 ) {
            
            if(empty($request_url) || empty($destination_url) ) {
                $post_error_message = "You mush fill both the Request URL and the Destination URL.";
                $redirect_error = 1;
            } // if(empty($request_url) || empty($destination_url) ) {

            $request_url_strtolower = strtolower($request_url);
            // trying to help user insert the correct URL
            if( !strstr($request_url_strtolower, "http://")  && !strstr($request_url_strtolower, "https://") && ( substr($request_url_strtolower, 0, 1) != '/' ) ) {
                $post_error_message = "You must fill a correct path in Request URL. For example: http://www.website.com or /page/ &nbsp; <a href='#' class='yydevelopment-redirecting-go-back-bth' ONCLICK='history.go(-1)'>Go Back</a>";
                $redirect_error = 1;
            } // if( !strstr($request_url_strtolower, "http://")  && !strstr($request_url_strtolower, "https://") && ( substr($request_url_strtolower, 0, 1) != '/' ) ) {

            $destination_url_strtolower = strtolower($destination_url);
            // trying to help user insert the correct URL
            if( !strstr($destination_url_strtolower, "http://")  && !strstr($destination_url_strtolower, "https://") && ( substr($destination_url_strtolower, 0, 1) != '/' ) ) {
                $post_error_message = "You must fill a correct path in Destination URL. For example: http://www.website.com or /page/ &nbsp; <a href='#' class='yydevelopment-redirecting-go-back-bth' ONCLICK='history.go(-1)'>Go Back</a>";
                $redirect_error = 1;
            } // if( !strstr($destination_url_strtolower, "http://")  && !strstr($destination_url_strtolower, "https://") && ( substr($destination_url_strtolower, 0, 1) != '/' ) ) {

            // making sure the request url doesn't exists so it won't redirect the page twice
            $checking_if_request_already_exists = $wpdb->query("SELECT * FROM " . $yydev_secondary_table_name . " where request_url = '{$request_url}'");

            if($checking_if_request_already_exists > 0 ) {
                $post_error_message = "The request url is already exists please edit it instead of creating new one.";
                $redirect_error = 1;
            } // if($checking_if_request_already_exists == 0 ) {

            $request_url = esc_url_raw($request_url);
            $destination_url = esc_url_raw($destination_url);

            if( !isset($redirect_error) ) {

                // If the secondary data id exists it will add the new data
                $wpdb->insert( $yydev_secondary_table_name,
                    array('secondary_id'=>$main_deta_id,
                    'request_url'=>$request_url,
                    'destination_url'=>$destination_url,
                    'redirect_type'=>$redirect_type,
                    'advertising_platform'=>$advertising_platform,
                    'position'=>$new_data_position,
                    'strtotime'=>$strtotime,
                    'redirect_query'=>$redirect_query,
                    'get_parameters'=>$get_parameters,
                    ), array('%d', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%d') );
                
                    // Creating page link and redirect the user the current page with the new data
                    $new_detabase_id = $wpdb->insert_id;
                    $success_message = "The new redirect was inserted successfully";

            } // if( !isset($redirect_error) ) {
                
        } // if( isset($_POST['secondary_page_id_post']) && !empty($_POST['secondary_page_id_post'])  ) {

    } else { // if( wp_verify_nonce($_POST['yydev_redirect_nonce_secondary_deta'], 'yydev_redirect_action_secondary_deta') ) {
        $post_error_message = "Form nonce was incorrect";
    } // } else { // if( wp_verify_nonce($_POST['yydev_redirect_nonce_secondary_deta'], 'yydev_redirect_action_secondary_deta') ) {

} // if( isset($_POST['yydev_redirect_nonce_secondary_deta']) ) {

// ====================================================
// Update the secondary data if they are changed
// ====================================================

if( isset($_POST['yydev_redirect_nonce_update_secondary_deta']) ) {

    if( wp_verify_nonce($_POST['yydev_redirect_nonce_update_secondary_deta'], 'yydev_redirect_action_update_secondary_deta') ) {

            // -----------------------------------------------------
            // making sure the user inserted the correct url
            // -----------------------------------------------------
            $error_request_url = "";
            $error_destination_url = "";

            foreach( $_POST['form_id'] as $checking_for_correct_url) {

                $request_url = esc_url_raw( strtolower($_POST['request_url'][$checking_for_correct_url]) );
                $destination_url = esc_url_raw( strtolower($_POST['destination_url'][$checking_for_correct_url]) );

                // trying to help user insert the correct URL
                
                $request_url_strtolower = strtolower($request_url);
                if( !strstr($request_url_strtolower, "http://")  && !strstr($request_url_strtolower, "https://") && ( substr($request_url_strtolower, 0, 1) != '/' ) ) {
                    $error_request_url .= "<b>" . $request_url . "</b>, ";
                } // if( !strstr($request_url_strtolower, "http://")  && !strstr($request_url_strtolower, "https://") && ( substr($request_url_strtolower, 0, 1) != '/' ) ) {

                // trying to help user insert the correct URL
                $destination_url_strtolower = strtolower($destination_url);
                if( !strstr($destination_url_strtolower, "http://")  && !strstr($destination_url_strtolower, "https://") && ( substr($destination_url_strtolower, 0, 1) != '/' ) ) {
                    $error_destination_url .= "<b>" . $destination_url . "</b>, ";
                } // if( !strstr($destination_url_strtolower, "http://")  && !strstr($destination_url_strtolower, "https://") && ( substr($destination_url_strtolower, 0, 1) != '/' ) ) {
                    
            } // foreach( $_POST['form_id'] as $checking_for_correct_url) {

            if(!empty($error_destination_url) || !empty($error_request_url)) {

                $post_error_message = "You must fill a correct URL in all request & destination paths. For example: http://www.website.com or /page/ &nbsp; <a href='#' class='yydevelopment-redirecting-go-back-bth' ONCLICK='history.go(-1)'>Go Back</a>";
                $redirect_error = 1;

                if( !empty($error_destination_url) ) {
                    $post_error_message .= "<br /> Destination URL Errors: " . $error_destination_url;
                    $redirect_error = 1;
                } // if( !empty($error_destination_url) ) {

                if( !empty($error_request_url) ) {
                    $post_error_message .= "<br /> Request URL Errors: " . $error_request_url;
                    $redirect_error = 1;
                } // if( !empty($error_request_url) ) {

            } // if(!empty($error_destination_url) || !empty($error_request_url)) {

            // -----------------------------------------------------
            // update the info in the database
            // -----------------------------------------------------
            if( !isset($redirect_error) ) {

                foreach( $_POST['form_id'] as $this_secondary_data_id) {

                    $main_deta_id = intval( $_POST['form_id'][$this_secondary_data_id] );

                    if( !empty($main_deta_id) ) { 

                        // If there is no error insert the info to the database
                        $redirect_type = intval($_POST['redirect_type'][$this_secondary_data_id]);
                        $redirects_amount = intval($_POST['redirects_amount'][$this_secondary_data_id]);
                        $advertising_platform = sanitize_text_field($_POST['advertising_platform'][$this_secondary_data_id]);
                        $position = floatval($_POST['position'][$this_secondary_data_id]);
                        $request_url = esc_url_raw(strtolower($_POST['request_url'][$this_secondary_data_id]));
                        $destination_url = esc_url_raw($_POST['destination_url'][$this_secondary_data_id]);
                        $redirect_query = sanitize_text_field($_POST['redirect_query'][$this_secondary_data_id]);
                        $get_parameters = intval($_POST['get_parameters'][$this_secondary_data_id]);

                        // Checking data id exists
                        $check_database_exists = $wpdb->query("SELECT id FROM " . $yydev_secondary_table_name . " where id = " . $main_deta_id);

                        if($check_database_exists == 0 ) {
                            $post_error_message = "The redirect id was not found";
                        } else { // if($check_database_exists < 1 ) {
                        
                            // If the database id exists it will add the new secondary data
                            $wpdb->update( $yydev_secondary_table_name,
                            array('request_url'=>$request_url,
                            'destination_url'=>$destination_url,
                            'redirect_type'=>$redirect_type,
                            'advertising_platform'=>$advertising_platform,
                            'position'=>$position,
                            'redirects_amount'=>$redirects_amount,
                            'redirect_query'=>$redirect_query,
                            'get_parameters'=>$get_parameters,
                            ), array('id'=>$main_deta_id), array('%s', '%s', '%s', '%s', '%f', '%d', '%s', '%d') );
                        
                            $success_message = "The redirect settings were updated successfully";

                        } // } else { // if($check_database_exists < 1 ) {

                    } // if( !empty($main_deta_id) ) { 

                } // foreach( $_POST['form_id'] as $this_secondary_data_id) {
            
            } // if( !isset($redirect_error) ) {
    
    } else { // if( wp_verify_nonce($_POST['yydev_redirect_nonce_update_secondary_deta'], 'yydev_redirect_action_update_secondary_deta') ) {
        $post_error_message = "Form nonce was incorrect";
    } // } else { // if( wp_verify_nonce($_POST['yydev_redirect_nonce_update_secondary_deta'], 'yydev_redirect_action_update_secondary_deta') ) {

} // if( isset($_POST['yydev_redirect_nonce_update_secondary_deta']) ) {
     
?>


<div class="wrap yydevelopment-redirecting <?php if(is_rtl()) {echo "yydevelopment-redirecting-rtl";} ?>">
    <h2 class="isplay-inline">Edit 301/302 Redirects <a class="go-back-button" href="<?php echo $plugin_page_url; ?>">Go Back</a></h2>
    
    <?php yydev_redirect_echo_message_if_exists(); ?>
    <?php yydev_redirect_echo_success_message_if_exists($success_message); ?>
    <?php yydev_redirect_echo_error_message_if_exists($post_error_message); ?>
    
    <div class="insert-new">
        
<?php

    $check_secondary_deta_id = $wpdb->get_row("SELECT * FROM " . $yydev_redirect_table_name . " WHERE id = " . $secondary_page_id );

?>
                
        <h4>Edit Redirect Settings</h4>
                
        <form class="edit-main-database" method="POST" action="">
           
            <span>Redirect ID: <?php echo $check_secondary_deta_id->id; ?></span>
            <br />
           
            <label for="redirections_name">Redirection Name:</label>
            <input type="text" id="redirections_name" class="redirections_name input-long" name="redirections_name" value="<?php echo yydev_redirect_html_output($check_secondary_deta_id->name); ?>" />

            <input type="hidden" name="form_id" class="form_id" value="<?php echo yydev_redirect_html_output($secondary_page_id); ?>" />
            
            <br /><br />

            <?php wp_nonce_field( 'yydev_redirect_action_edit_main_db', 'yydev_redirect_nonce_edit_main_db' ); ?>

            <input type="submit" class="edit-main-database img_url_button" name="edit-main-database" value="Edit Redirect Settings" />
        </form>
    

    <form class="insert-form" method="POST" action="">
        
        <br /><br />
        <h2>Add Redirect</h2> 

                <div class="add_new_url_block">
                    <label for="request_url">Request URL: </label>
                    <input type="text" id="request_url" class="input-very-long" name="request_url" value="" /> 
                    <small>Example: /go/text/themes/</small>
                </div><!--add_new_url_block-->

                <div class="add_new_url_block">
                    <label for="destination_url">Destination URL: </label>
                    <input type="text" id="destination_url" class="input-very-long" name="destination_url" value="" /> 
                    <small>Example: /go/text/themes/, Example2: http://www.website.com,</small>
                </div><!--add_new_url_block-->

                <div class="add_new_url_block">
                    <label for="redirect_type">Redirection Type: </label>
                    <select name="redirect_type" id="redirect_type">
                    <option value="301">301 Permanent Redirect</option>
                    <option value="302">302 Temporary Redirect</option>
                    </select>
                </div><!--add_new_url_block-->

                <br />
                <strong><small><u>Advanced Redirect Options (Optional)</u></small></strong>
                <br /><br />

                <div class="advertising_platform">
                    <label for="advertising_platform">Affiliate Platform: <small>(optional)</small> </label>
                    <input type="text" id="advertising_platform" class="input-340" name="advertising_platform" value="" /> 
                    <small>Examples: CJ, Impact, ClickBank, Amazon</small>
                </div><!--add_new_url_block-->

                <div class="add_new_url_block">
                    <label for="get_parameters">Pass URL GET Parameters: </label>
                    <select name="get_parameters" id="get_parameters">
                    <option value="0">Allow</option>
                    <option value="1">Block</option>
                    </select>
                    <small>Allow to pass data with $_GET index.php?<strong>data=1</strong> to the redirect url. You can block it when redirecting to external sites or when it cause problems.</small>
                </div><!--add_new_url_block-->

                <div class="add_new_url_block">
                    <label for="redirect_query">Redirection Query: </label>
                    <select name="redirect_query" id="redirect_query">
                    <option value="exact">Exact Match</option>
                    <option value="contain">Contain Request</option>
                    </select>
                    <small>Don't change it if you don't know what it means, incorrect use can cause problems to your site.</small>
                </div><!--add_new_url_block-->

                <?php
                    // creating nonce to make sure the form was submitted correctly from the right page
                    wp_nonce_field( 'yydev_redirect_action_secondary_deta', 'yydev_redirect_nonce_secondary_deta' ); 
                ?>

                <br />
                
                <input type="hidden" name="secondary_page_id_post" value="<?php echo $secondary_page_id; ?>" />
                <input type="submit" name="submit-new-secondary-deta" class="button-cursor" value="Submit Redirect" />

    </form>

    
    <br /><br /><br />
    <h2>Edit Redirects</h2>     
    

    <div class="main-page-table">
    <form method="POST" action="">

        <table class="wp-list-table widefat fixed striped posts boxes-table redirect-table-th">
        <thead>
            <tr>
                <th style="width:25px;">ID</th>
                <th style="width:280px;">Request URL</th>
                <th style="width:280px;">Destination URL</th>
                <th style="width:155px;">Redirect Type</th>
                <th style="width:60px;">Redirects Amount</th>
                <th style="width:125px;">Affiliate Platform</th>
                <th style="width:115px;">Redirect Query</th>
                <th style="width:70px;">URL GET Parameters</th>
                <th style="width:60px;">Position</th>
                <th style="width:60px;">Remove</th>
            </tr>
        </thead>
        
        <tbody id="the-list">
        
    <?php
        
    // ================================================
    // Echoing all the secondary data from the database 
    // ================================================
        
        $main_secondary_data_info = $wpdb->get_results("SELECT * FROM " . $yydev_secondary_table_name . " WHERE secondary_id = " . $secondary_page_id . " ORDER BY position ASC");
        
        // Echo there is no data found 
        if(empty($main_secondary_data_info)) {
    ?>
        <tr class="no-items"><td class="colspanchange" colspan="9">No. redirects found</td></tr>
    <?php     
        } // if(empty($main_secondary_data_info)) {
        
        
        $position_number = 1;
        foreach($main_secondary_data_info as $secondary_data_info) {

        $secondery_data_info_id = $secondary_data_info->id;;

    ?>
                
            <tr>
                
                <td><?php echo $position_number; ?></td>

                <td><input type="text" id="request_url" class="input-340 direction-ltr;" name="request_url[<?php echo $secondery_data_info_id; ?>]" value="<?php echo yydev_redirect_html_output($secondary_data_info->request_url); ?>" /></td>

                <td><input type="text" id="destination_url" class="input-340 direction-ltr;" name="destination_url[<?php echo $secondery_data_info_id; ?>]" value="<?php echo yydev_redirect_html_output($secondary_data_info->destination_url); ?>" /></td>

                <td>
                    <select name="redirect_type[<?php echo $secondery_data_info_id; ?>]">
                    <option value="301" <?php if( $secondary_data_info->redirect_type == "301") {echo "selected";} ?> >301 Permanent Redirect</option>
                        <option value="302" <?php if ($secondary_data_info->redirect_type == "302") {echo "selected";} ?> >302 Temporary Redirect</option>
                    </select>
                </td>

                <td><input type="text" id="redirects_amount" class="text_color shorter_input" name="redirects_amount[<?php echo $secondery_data_info_id; ?>]" value="<?php echo yydev_redirect_html_output($secondary_data_info->redirects_amount); ?>" /></td>

                <td><input type="text" id="advertising_platform" name="advertising_platform[<?php echo $secondery_data_info_id; ?>]" value="<?php echo yydev_redirect_html_output($secondary_data_info->advertising_platform); ?>" /></td>

                <td>
                    <select name="redirect_query[<?php echo $secondery_data_info_id; ?>]">

                        <option value="exact" <?php if( $secondary_data_info->redirect_query == "exact") {echo "selected";} ?> >Exact Match</option>
                        <option value="contain" <?php if ($secondary_data_info->redirect_query == "contain") {echo "selected";} ?> >Contain Request</option>
                    </select>
                </td>

                <td>
                    <select name="get_parameters[<?php echo $secondery_data_info_id; ?>]">
                        <option value="0" <?php if( $secondary_data_info->get_parameters == 0) {echo "selected";} ?> >Allow</option>
                        <option value="1" <?php if ($secondary_data_info->get_parameters == 1) {echo "selected";} ?> >Block</option>
                    </select>
                </td>

                <td><input type="text" id="position_<?php echo $secondery_data_info_id; ?>" class="text_color shorter_input" name="position[<?php echo $secondery_data_info_id; ?>]" value="<?php echo yydev_redirect_html_output($position_number); ?>" /></td>

                <td style="vertical-align:middle;">
                    <a class="remove-secondary-form-image remove-form" href="<?php echo $plugin_page_url . "&view=secondary&remove-secondary-form=1&id=" . $secondary_page_id . "&secondary_id=" . intval($secondary_data_info->id); ; ?>">
                        <img  src="<?php echo plugins_url( 'images/delete.png', dirname(__FILE__) ); ?>" alt="" />
                    </a>
                </td>

                <input type="hidden" name="form_id[<?php echo $secondery_data_info_id; ?>]" value="<?php echo intval($secondary_data_info->id); ?>" />

            </tr>
                

    <?php
        $position_number++;
        } // foreach($main_secondary_data_info as $secondary_data_info) {
        
    ?>

        </tbody>
        
        <tfoot>
            <tr>
                <th style="width:25px;">ID</th>
                <th style="width:280px;">Request URL</th>
                <th style="width:280px;">Destination URL</th>
                <th style="width:155px;">Redirect Type</th>
                <th style="width:60px;">Redirects Amount</th>
                <th style="width:125px;">Affiliate Platform</th>
                <th style="width:115px;">Redirect Query</th>
                <th style="width:70px;">URL GET Parameters</th>
                <th style="width:60px;">Position</th>
                <th style="width:60px;">Remove</th>
            </tr>
        </tfoot>
        
        </table>
        </div><!--main-page-table-->

    <?php
        // creating nonce to make sure the form was submitted correctly from the right page
        wp_nonce_field( 'yydev_redirect_action_update_secondary_deta', 'yydev_redirect_nonce_update_secondary_deta' ); 
    ?>

    <input type="submit" class="update-button" name="update-secondary-deta" value="Update Redirects">

</form>

<br />
<span id="footer-thankyou-code">This plugin was create by <a target="_blank" href="https://www.yydevelopment.com">YYDevelopment</a>. If you liked the plugin please give it a <a target="_blank" href="https://wordpress.org/plugins/advanced-301-and-302-redirect/#reviews">5 stars review</a>.
If you want to help support this FREE plugin <a target="_blank" href="https://www.yydevelopment.com/coffee-break/?plugin=advanced-301-and-302-redirect">buy us a coffee</a>.
</span>
</div><!--wrap-->
