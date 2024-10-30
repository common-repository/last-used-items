<?php
/**
* Plugin Name: Last Used Items
* Description: This Plugin lets you view your last used WordPress items.
* Version: 2.15   
* Author: Florian Wetzl
* Author URI: http://www.florian-wetzl.de/
* License: GPLv2
*/


global $lastuseditems_db_version;
$lastuseditems_db_version = '2.15';

global $table_name;


//install plugin

function lastuseditems_install()
{
    
	global $wpdb;
	global $lastuseditems_db_version;

	$table_name = $wpdb->prefix . 'last_used_items';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  post_id mediumint(9) NOT NULL,
  user_id mediumint(9) NOT NULL,
  post_url TEXT,
  PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'lastuseditems_db_version', $lastuseditems_db_version );
	
	register_uninstall_hook( __FILE__, 'lastuseditems_uninstall' );  

}

register_activation_hook( __FILE__, 'lastuseditems_install' );


//uninstall plugin

function lastuseditems_uninstall()
{
	global $wpdb;
    $table_name = $wpdb->prefix.'last_used_items';
	$wpdb->query( "DROP TABLE IF EXISTS ".$table_name );
	
	delete_option( 'lastuseditems_db_version' );		
}


// update plugin

function lastuseditems_update_db_check()
{
    global $wpdb;
    global $lastuseditems_db_version;
    $table_name = $wpdb->prefix.'last_used_items';
    
    if (get_site_option( 'lastuseditems_db_version' ) < 2)
    {
        update_option( 'lastuseditems_db_version', 2); 
        $wpdb->query( "ALTER TABLE `".$table_name."` ADD `post_url` TEXT NOT NULL AFTER `post_id`" );
        $wpdb->query( "TRUNCATE TABLE `".$table_name."`" );
    }
	
    if (get_site_option( 'lastuseditems_db_version' ) < 2.13)
    {
        update_option( 'lastuseditems_db_version', 2.13); 
        $wpdb->query( "ALTER TABLE `".$table_name."` ADD `user_id` mediumint(9) NOT NULL AFTER `post_id`" );
        $wpdb->query( "TRUNCATE TABLE `".$table_name."`" );
    }
	
}
add_action( 'plugins_loaded', 'lastuseditems_update_db_check' );


//create menu items

function last_used_items_register_options_page() {
  add_options_page('Last Used Items log has been cleared', 'Last Used Items Clear Log', 'manage_options', 'last_used_items', 'last_used_items_options_page');
}
add_action('admin_menu', 'last_used_items_register_options_page');

function last_used_items_options_page()
{

	echo "<h2>Last Used Items</h2> <p>Log-Entries have been cleared.</p><p>You will be redirected in 5 seconds.</p>";

	global $wpdb;
	$table_name = $wpdb->prefix.'last_used_items';
	$wpdb->query( "TRUNCATE TABLE `".$table_name."`" );
	
	$home_url = get_home_url();
	$full_url = $home_url."/wp-admin";
	
	echo "
	<script>
         setTimeout(function(){
            window.location.href = '".$full_url."';
         }, 5000);
      </script>";

}



add_action('admin_bar_menu', 'last_used_items_add_toolbar_items', 100);

function last_used_items_add_toolbar_items($admin_bar)
{

    global $wpdb;
    $table_name = $wpdb->prefix.'last_used_items';

    $admin_bar->add_menu( array(
        'id'    => 'last-used-items',
        'title' => '<span class="ab-icon"></span><span class="ab-label">Last Used</span>',
        'href'  => '#',
        'meta'  => array(
            'title' => __('Last Used'),            
        ),
    ));

	
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	
    $result = $wpdb->get_results( "SELECT DISTINCT post_id, user_id, post_url FROM ".$table_name." WHERE user_id = ".$user_id." ORDER BY id DESC" );
    
    
	$i = 0;
	
    foreach( $result as $row )
    {
      $p_title = get_the_title($row->post_id);
      $p_type = get_post_type($row->post_id);

      //check if post_type still exists and if it is not in trash
      if(post_type_exists($p_type) && get_post_status($row->post_id) != 'trash')//
      {
          $submenu_title = $p_title." (".$p_type.")";
          
          $admin_bar->add_menu( array(
              'id'    => '',
              'parent' => 'last-used-items',
              'title' => $submenu_title,
              'href'  => $row->post_url,
              'meta'  => array(
                  'title' => __('Last Used Items Sub Menu Item'),
                  'target' => '_self',
                  'class' => 'last-used-items-sub-menu-item'
              ),
          ));
          
    $i++;  } 
		//echo $i." :".$p_title."<br>";
		
      
    }
  
}


//url functions

function lastuseditems_url_origin( $s, $use_forwarded_host = false )
{
    $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
    $sp       = strtolower( $s['SERVER_PROTOCOL'] );
    $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
    $port     = $s['SERVER_PORT'];
    $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
    $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
    $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function lastuseditems_full_url( $s, $use_forwarded_host = false )
{
    return lastuseditems_url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
}


//when publishing a new post or page, a cookie with the post-ID is set

function lastuseditems_pp($post_ID)
{
  global $post;
  $cookie_name = "lastuseditems_pp_id";
  $actual_link = lastuseditems_full_url( $_SERVER );
  
  setcookie($cookie_name, $post_ID, $actual_link);
  
  
  //echo $actual_link;
}
add_action('publish_post', 'lastuseditems_pp');
add_action('publish_page', 'lastuseditems_pp');


//when publishing a custom post type

add_action ('registered_post_type','get_p_types', 199);

function get_p_types()
{   
    $p_types_arr = get_post_types(array('_builtin' => false));
    
    foreach ($p_types_arr as &$value)
    {
        if($value != "scheduled-action" && $value != "product_variation" && $value != "shop_order" && $value != "shop_order_refund" && $value != "shop_coupon")
        {
            //echo $value;
            add_action('publish_'.$value, 'lastuseditems_pp');
        }
        
    }
}
 

//read cookie and save new published post to db

add_action( 'admin_notices', 'lastuseditems_log_pp_to_db' );

function lastuseditems_log_pp_to_db()
{

    if(isset($_COOKIE['lastuseditems_pp_id']))
    {
        $c_vals = explode(",",$_COOKIE['lastuseditems_pp_id']);
        $post_id = intval($c_vals[0]);
        $post_url = $c_vals[1];
        unset($_COOKIE['lastuseditems_pp_id']);
        setcookie('lastuseditems_pp_id', null, -1, '/'); 
        
        //$post_type = get_post_type($post_id); 
		
		$current_user = wp_get_current_user();
	    $user_id = $current_user->ID;
        
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'last_used_items';
    
        $wpdb->insert( 
              $table_name, 
              array( 
                  'id' => 'NULL',
                  'post_id' => $post_id,
				  'user_id' => $user_id,
                  'post_url' => $post_url
              )
          );
          
        lastuseditems_clean_up_db(); 
        
    }

}


//log edit post actions to db

add_action( 'admin_notices', 'lastuseditems_write_in_db' );

function lastuseditems_write_in_db() {
  
    global $wpdb;
    $current_screen = get_current_screen()->id;
    
    $table_name = $wpdb->prefix . 'last_used_items';
    
    if(isset($_GET['post']))
    {
    
      $post_id = intval($_GET['post']);
	  $current_user = wp_get_current_user();
	  $user_id = $current_user->ID;
      $actual_link = lastuseditems_full_url( $_SERVER );
    
      $wpdb->insert( 
            $table_name, 
            array( 
                'id' => 'NULL',
                'post_id' => $post_id,
				'user_id' => $user_id,
                'post_url' => $actual_link  
            )
        );
        
    }
      
    lastuseditems_clean_up_db();  
    
}


//function for logging posts viewed in edit-mode to db
/*
function lastuseditems_write_in_db_publish($post)
{

    global $wpdb;
    $current_screen = get_current_screen()->id;
    
    $table_name = $wpdb->prefix . 'last_used_items';
    
    $post_id = intval($_GET['post']);
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
    $actual_link = lastuseditems_full_url( $_SERVER );
    
	  $wpdb->insert( 
			$table_name, 
			array( 
				'id' => 'NULL',
				'post_id' => $post_id,
				'user_id' => $user_id,
				'post_url' => $actual_link  
			)
		);
      
    lastuseditems_clean_up_db(); 
}
*/

//Keep the log entries to a limit of 100

function lastuseditems_clean_up_db()
{

    global $wpdb;
    $table_name = $wpdb->prefix.'last_used_items';
    
    $result = $wpdb->get_results( "SELECT * FROM ".$table_name );

    if(count($result) > 1000)
    {
    
        $result = $wpdb->get_results( "SELECT * FROM ".$table_name." ORDER BY id DESC LIMIT 1000" );
    
        foreach( $result as $row )
        {
          $post = $row->id;
        }

        $result = $wpdb->get_results( "DELETE FROM ".$table_name." WHERE id < ".$post ); 
    
    }

}


//register wp-admin css

add_action( 'admin_enqueue_scripts', 'register_lastuseditems_admin_style' );
function register_lastuseditems_admin_style()
{
  wp_register_style( 'lastuseditems_admin_css', plugins_url( 'last-used-items-admin-styles.css', __FILE__  ), false, '1.0.0' );
  wp_enqueue_style( 'lastuseditems_admin_css' );  
}