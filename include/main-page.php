<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

$success_message = '';
$post_error_message = '';

// ====================================================
// Include the file that contains all the info
// ====================================================
include('settings.php');

// getting the page url for the settings page
$plugin_page_url = esc_url( menu_page_url( 'yydev-redirection', false ) );

// ====================================================
// Inserting the content to the database if it was created
// ====================================================

if( isset($_POST['yydev_redirect_nonce_main']) ) {

    if( wp_verify_nonce($_POST['yydev_redirect_nonce_main'], 'yydev_redirect_action_main') ) {

        if( isset($_POST['submit_new_form'])  ) {
            // If someone create new content    
            
            if( empty($_POST['form_submit_name']) ) {
                // If the content name is empty echo a message
                $submit_name_error = "You have to choose a name for the redirect folder";

            } else { // if( !empty($_POST['form_submit_name']) ) {

                // If there is no error insert the info to the database
                $form_submit_name = sanitize_text_field($_POST['form_submit_name']);

                $form_slug_name = str_replace(" ", "_", strtolower(trim($form_submit_name)));
                $form_slug_name = sanitize_text_field($form_slug_name);

                // Checking if the content name already exists
                $form_submit_name_exists_check = $wpdb->query("SELECT slug FROM " . $yydev_redirect_table_name . " WHERE slug = '{$form_slug_name}' ");
                        
                if($form_submit_name_exists_check > 0 ) {
                    $submit_name_error = "The redirect name is already exists please choose different name";
                } else { // if($form_submit_name_exists_check > 0 ) {
                
                // If the content name not exists it will insert it into the database
                $wpdb->insert( $yydev_redirect_table_name,
                    array('name'=>$form_submit_name,
                    'slug'=>$form_slug_name,
                    ), array('%s', '%s') );

                    // Creating page link and redirect the user to the new url page where he can edit the content
                    $new_detabase_id = $wpdb->insert_id;
                    $new_page_link = $plugin_page_url . "&view=secondary&id=" . $new_detabase_id;
                    $success_message = "The redirect folder was successfully created to view it <a href='" . $new_page_link . "'>click here</a>";
                    // yydev_redirect_redirections_page($new_page_link);

                } // } else { // if($form_submit_name_exists_check > 0 ) {
            
            } // if( !empty($_POST['form_submit_name']) ) {
            
        } // if( isset($_POST['submit_new_form']) ) {

    } else { // if( wp_verify_nonce($_POST['yydev_redirect_nonce_main'], 'yydev_redirect_action_main') ) {
        $submit_name_error = "Form nonce was incorrect";
    } // } else { // if( wp_verify_nonce($_POST['yydev_redirect_nonce_main'], 'yydev_redirect_action_main') ) {

} // if( isset($_POST['yydev_redirect_nonce_main']) ) {

// ====================================================
// Removing the main Data if it was deleted
// ====================================================

if( isset($_POST['yydev_redirect_nonce_remove']) ) {

    if( wp_verify_nonce($_POST['yydev_redirect_nonce_remove'], 'yydev_redirect_action_remove') ) {

        $secondary_page_id = '';
        if( isset($_POST['remove_redirect_id']) ) {
            $secondary_page_id = intval($_POST['remove_redirect_id']);
        } // if( isset($_POST['remove_redirect_id']) ) {

        if( isset($secondary_page_id) && !empty($secondary_page_id) ) {

            $check_content_id = $wpdb->query("SELECT * FROM " . $yydev_redirect_table_name . " WHERE id = " . $secondary_page_id);

            if($check_content_id > 0) {
                // if the data id exists on the database it will be removed
                
                $wpdb->delete( $yydev_redirect_table_name, array('id'=>$secondary_page_id) ); // removing main database info
                $wpdb->delete( $yydev_secondary_table_name, array('secondary_id'=>$secondary_page_id) ); // removing all sub database info

                $success_message = "The redirect id #" . $secondary_page_id . " was removed successfully";

            } else { // if($check_content_id > 0) {
                
                $error_message = "The redirect id wasn't not found";
                $new_page_link = $plugin_page_url . "&error-message=" . urlencode($error_message);
                // yydev_redirect_redirections_page($new_page_link);
                
            } // } else { // if($check_content_id > 0) {
            
        } // if( isset($secondary_page_id) && !empty($secondary_page_id) ) {

    } else { // if( wp_verify_nonce($_POST['yydev_redirect_nonce_remove'], 'yydev_redirect_action_remove') ) {
        $post_error_message = "Form nonce was incorrect";
    } // } else { // if( wp_verify_nonce($_POST['yydev_redirect_nonce_remove'], 'yydev_redirect_action_remove') ) {

} // if( isset($_POST['yydev_redirect_nonce_remove']) ) {

?>

<div class="wrap yydevelopment-redirecting-main yydevelopment-redirecting <?php if(is_rtl()) {echo "yydevelopment-redirecting-rtl yydevelopment-redirecting-rtl-main";} ?>">
    <h2>Advanced 301/302 Redirect</h2>

    <?php yydev_redirect_echo_message_if_exists(); ?>
    <?php yydev_redirect_link_echo_success_message_if_exists($success_message); ?>
    <?php 
        if(isset($post_error_message)) {
            yydev_redirect_echo_error_message_if_exists($post_error_message); 
        }
    ?>

    <br />    
    <div class="insert-new">
        
        <h5>Add Redirect</h5>
        <form class="insert-form" method="POST" action="">
            <label for="form_submit_name">New Redirect Name</label>
            <input type="text" id="form_submit_name" class="form_submit_name input-long direction-ltr" name="form_submit_name" value="" />

            <?php
                // creating nonce to make sure the form was submitted correctly from the right page
                wp_nonce_field( 'yydev_redirect_action_main', 'yydev_redirect_nonce_main' ); 
            ?>

            <input type="submit" name="submit_new_form" value="Submit Redirect Folder" />
            <?php 
                if(isset($submit_name_error)) {
                    yydev_redirect_show_error_message($submit_name_error, '1'); 
                } // if(isset($submit_name_error)) {
            ?>
        </form>
    
    </div><!--insert-new-->
            
    <div class="main-page-table">
    <table class="wp-list-table widefat fixed striped posts">
    <thead>
        <tr>
            <th style="width:80px;">ID</th>
            <th style="width:250px;">Redirect Name</th>
            <th style="width:120px;text-align:center;">No. of redirects</th>
            <th style="width:190px;">Action</th>
        </tr>
    </thead>
    
    <tbody id="the-list">
    
<?php
    
// ================================================
// Echoing all the data from the database 
// ================================================
    
    global $wpdb;
    $database_content_output = $wpdb->get_results("SELECT * FROM " . $yydev_redirect_table_name . " ORDER BY id DESC ");
    
    // Echo if nothing was found
    if(empty($database_content_output)) {
?>
    <tr class="no-items"><td class="colspanchange" colspan="6">No. redirects where found</td></tr>
<?php     
    } // if(empty($database_content_output)) {
    
    
    foreach($database_content_output as $database_output) {
            
?>
        <tr>
            <td><a href="<?php echo $plugin_page_url . "&view=secondary&id=" . $database_output->id; ?>"><?php echo $database_output->id; ?></a></td>
            <td><a href="<?php echo $plugin_page_url . "&view=secondary&id=" . $database_output->id; ?>"><?php echo $database_output->name; ?></a></td>
            <td style="text-align:center;"><?php echo $wpdb->query("SELECT * FROM " . $yydev_secondary_table_name . " WHERE secondary_id = " . $database_output->id ); ?></td>
            <td><a href="<?php echo $plugin_page_url . "&view=secondary&id=" . $database_output->id; ?>">Edit Redirect</a> &nbsp;&nbsp;&nbsp; / &nbsp;&nbsp;&nbsp;

            <form class="insert-form remove-data-form" method="POST" action="">
                <?php wp_nonce_field( 'yydev_redirect_action_remove', 'yydev_redirect_nonce_remove' ); ?>
                <input type="hidden" name="remove_redirect_id" value="<?php echo $database_output->id; ?>" />
                <input type="submit" class="remove-submit-button remove-form" name="submit_new_form" value="Delete Redirect Folder" />
            </form>
        </tr>
        
<?php
    } // foreach($database_content_output as $database_output) {
    
?>

    </tbody>
    
    <tfoot>
        <tr>
            <th style="width:80px;">ID</th>
            <th style="width:250px;">Redirect Name</th>
            <th style="width:120px;text-align:center;">No. of redirects</th>
            <th style="width:190px;">Action</th>
        </tr>
    </tfoot>
    
    </table>
    </div><!--main-page-table-->
        
<br />
<span id="footer-thankyou-code">This plugin was create by <a target="_blank" href="https://www.yydevelopment.com">YYDevelopment</a>. If you liked the plugin please give it a <a target="_blank" href="https://wordpress.org/plugins/advanced-301-and-302-redirect/#reviews">5 stars review</a>. 
If you want to help support this FREE plugin <a target="_blank" href="https://www.yydevelopment.com/coffee-break/?plugin=advanced-301-and-302-redirect">buy us a coffee</a>.</span>
</div><!--wrap-->