<?php
   /**
	* Script to Add/Update user error log to server,
	*
	* Getting the file ready
	* It accepts the parameter user_id,user_type(optional),user_os using GET, and log_file using POST(multipart)
	* Please note that this file only helps handle, determine and prevent any empty parameter
	* If a required parameter is empty, it'll return false
	* You should make sure all those are handled on your end
	* 
	* @return JSON true on success or Error status on failure.
	* @author Precious Omonzejele <omonze@peepsipi.com>, Pelumi Oyefeso
	*/

	header( "Content-Type: application/json" );
	require "../inc/_config.php";

	$user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : "";
	$user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
	$user_os = isset($_GET["user_os"]) ? trim( strtolower($_GET["user_os"]) ) : "";
    $log_file_name = isset($_FILES['log_file']['name']) ? $_FILES['log_file']['name'] : "" ;
	 //$n_send_mail = isset($_GET["dont_send_mail"]) ? true : false;

	if( !( $user_id && $user_os ) ){
		pekky_set_failure_state( 0, "empty field(s)" );
		exit( pekky_print_json_state() );//end the program
	}

	if( !$log_file_name ){
		pekky_set_failure_state( 0, "no log file to upload." );
		exit( pekky_print_json_state() );//end the program	
	}

	if( !strpos( $log_file_name, ".log" ) ){
        pekky_set_failure_state( 0, "Invalid log file format. File does not end with '.log'." );
        exit( pekky_print_json_state() );//end the program
    }

	// Some necessaries.
	$user_type_txt = user_type( $user_type );
	$document_root = $_SERVER['DOCUMENT_ROOT'] . "/";
    $logs_folder_name = "logs";

    // Check if the logs folder exists in the root directory otherwise, create the folder
    $logs_dir = $document_root.$logs_folder_name;
	if( !file_exists( $logs_dir ) ){
	    mkdir( $logs_dir );
    }

    // Check if the user type folders exist in the logs folder otherwise create them.
    $user_type_logs_dir = $logs_dir . "/" . $user_type_txt;

	if( !file_exists( $user_type_logs_dir ) ){
        mkdir( $user_type_logs_dir );
    }

    /**
     * Check if the user device operating systems folder exists inside the
     * user logs folder otherwise create them.
     */
    $os_user_type_logs_dir = $user_type_logs_dir . "/" . $user_os;
    if( !file_exists( $os_user_type_logs_dir ) ){
        mkdir( $os_user_type_logs_dir );
    }

	// OS specific user type folder.
	$os_specific_user_type_logs_dir = $os_user_type_logs_dir . "/" . $user_id;
    if( !file_exists( $os_specific_user_type_logs_dir ) ){
        mkdir( $os_specific_user_type_logs_dir );
    }

	// Finally,, the full path including the log name :)
    $os_specific_user_type_log_file = $os_specific_user_type_logs_dir . "/" . $log_file_name;

    if( move_uploaded_file( $_FILES['log_file']['tmp_name'], $android_specific_user_log_file_name ) ) {
        pekky_set_success_state();
        pekky_print_json_state();
    }
	else{
		$internal_err = file_upload_err_msg( $_FILES['log_file']['error'] );
        pekky_set_failure_state( -1, "Error in uploading file:" . $internal_err );
        pekky_print_json_state();
    }
