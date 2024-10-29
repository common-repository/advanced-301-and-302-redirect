<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); // Require to use dbDelta
    include('settings.php'); // Load the files to get the databse info
    
    if( $wpdb->get_var("SHOW TABLES LIKE '{$yydev_redirect_table_name}' ") != $yydev_redirect_table_name ) {
        // The table we want to create doesn't exists
       
        $sql = "CREATE TABLE " . $yydev_redirect_table_name . "( 
        id INTEGER(11) UNSIGNED AUTO_INCREMENT,
        name VARCHAR (500),
        slug VARCHAR (500),
        PRIMARY KEY (id) 
        ) $charset_collate;";
        
        dbDelta($sql);
        
       
    }  // if( $wpdb->get_var("SHOW TABLES LIKE '{$yydev_redirect_table_name}' ") != $yydev_redirect_table_name ) {
     


    // Creating the secondary database table
    
    if( $wpdb->get_var("SHOW TABLES LIKE '{$yydev_secondary_table_name}' ") != $yydev_secondary_table_name ) {
        // The table we want to create doesn't exists
       
        $sql = "CREATE TABLE " . $yydev_secondary_table_name . "( 
        id INTEGER(11) UNSIGNED AUTO_INCREMENT,
        secondary_id INTEGER (11),
        position FLOAT,
        request_url TEXT,
        destination_url TEXT,
        redirect_type INTEGER,
        advertising_platform TEXT,
        redirects_amount INTEGER NOT NULL,
        strtotime INTEGER (10),
        redirect_query TEXT,
        get_parameters TINYINT(1),
        PRIMARY KEY (id) 
        ) $charset_collate;";
        
        dbDelta($sql);
        
       
    }  // if( $wpdb->get_var("SHOW TABLES LIKE '{$yydev_secondary_table_name}' ") != $yydev_secondary_table_name ) {


// if the plugin change version and require to add database fields
if( isset($yydev_redirect_database_update ) ) {

    // ============================================================
    // Dealing with the plugin database updates for new versions
    // ============================================================

    // creating an array with all the columns from the database
    $existing_columns = $wpdb->get_col("DESC {$yydev_secondary_table_name}", 0);

    
    if($existing_columns) {

            // -------------------------------------------------------------
            // update the database for plugin version 1.1
            // -------------------------------------------------------------

            $new_db_column = 'strtotime';
            if( !in_array($new_db_column, $existing_columns) ) {
                // create the date column on the database
                $wpdb->query("ALTER TABLE $yydev_secondary_table_name ADD $new_db_column INTEGER (10) NOT NULL");
            } // if( in_array($new_db_column, $existing_columns) ) {

            // -------------------------------------------------------------
            // update the database for plugin version 1.2
            // -------------------------------------------------------------

            $new_db_column = 'redirect_query';
            if( !in_array($new_db_column, $existing_columns) ) {
                // create the date column on the database
                $wpdb->query("ALTER TABLE $yydev_secondary_table_name ADD $new_db_column TEXT NOT NULL");
            } // if( in_array($new_db_column, $existing_columns) ) {

            // -------------------------------------------------------------
            // update the database for plugin version 1.5
            // -------------------------------------------------------------

            $new_db_column = 'get_parameters';
            if( !in_array($new_db_column, $existing_columns) ) {
                // create the date column on the database
                $wpdb->query("ALTER TABLE $yydev_secondary_table_name ADD $new_db_column TINYINT(1) NOT NULL");
            } // if( in_array($new_db_column, $existing_columns) ) {

    } // if($existing_columns) {

} // if( isset($yydev_redirect_database_update ) ) {