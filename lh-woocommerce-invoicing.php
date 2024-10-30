<?php
/**
* Plugin Name: LH Woocommerce Invoicing
* Plugin URI: https://lhero.org/portfolio/lh-woocommerce-invoicing/
* Version: 1.03
* Author: Peter Shaw
* Author URI: https://shawfactor.com
* Description: Adds some basic invoicing support for Woocommerce
* Text Domain: lh_woocommerce_invoicing
* License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists('LH_Woocommerce_invoicing_plugin')) {

class LH_Woocommerce_invoicing_plugin {

    private static $instance;

    static function return_plugin_namespace(){
    
        return 'lh_woocommerce_invoicing';
    
    }
    
    static function return_post_type(){
    
        return 'shop_order';
    
    }


    public function add_meta_boxes($post_type, $post) {
    
        $post_types = array(self::return_post_type());
    
        if (in_array($post_type, $post_types)) {
    
            add_meta_box(self::return_plugin_namespace()."-details-div", _('Invoicing Details', self::return_plugin_namespace()), array($this,"metabox_content"), $post_type, "normal", "high");
    
        }
    
    }

public function metabox_content($post, $args){




?>
<table class="form-table">
<tr valign="top">
<th scope="row"><label id="<?php  echo self::return_plugin_namespace()."-subject-prompt-text";  ?>" for="<?php  echo self::return_plugin_namespace()."-subject";  ?>"><?php _e("Email Subject:", self::return_plugin_namespace() ); ?></label></th>
<td>
<input name="<?php  echo self::return_plugin_namespace()."-subject";  ?>" id="<?php  echo self::return_plugin_namespace()."-subject";  ?>" placeholder="Enter Email Subject" value="<?php echo get_post_meta($post->ID, '_'.self::return_plugin_namespace().'-subject', true); ?>" /></td>
</tr>
<tr valign="top">
<th scope="row"><label id="<?php  echo self::return_plugin_namespace()."-heading-prompt-text";  ?>" for="<?php  echo self::return_plugin_namespace()."-heading";  ?>"><?php _e("Email Heading:", self::return_plugin_namespace()); ?></label></th>
<td>
<input name="<?php  echo self::return_plugin_namespace()."-heading";  ?>" id="<?php  echo self::return_plugin_namespace()."-heading";  ?>" placeholder="Enter Email Heading" value="<?php echo get_post_meta($post->ID, '_'.self::return_plugin_namespace().'-heading', true); ?>" /></td>
</tr>
<tr valign="top">
<th scope="row"><label id="<?php  echo self::return_plugin_namespace()."-message-prompt-text";  ?>" for="<?php  echo self::return_plugin_namespace()."-emessage";  ?>"><?php _e("Enter Message", self::return_plugin_namespace()); ?></label></th>
<td>
<?php 
											  
		
$settings = array('textarea_rows' => 5, 'media_buttons' => false );											  
											  
wp_editor( get_post_meta($post->ID, '_'.self::return_plugin_namespace().'-message', true), self::return_plugin_namespace()."-message", $settings);
  
  ?> 
</td>
</tr>
</table>
<?php
	  
wp_nonce_field( self::return_plugin_namespace()."-shop_order-nonce", self::return_plugin_namespace()."-shop_order-nonce" );




}
  
    public function save_meta_boxes( $post_id, $post, $update ) {
    
      if(defined('DOING_AUTOSAVE') and DOING_AUTOSAVE)
            return;
     
        if(!current_user_can('edit_post', $post_id))
            return;
      
        if(!empty($_POST[self::return_plugin_namespace()."-shop_order-nonce"]) and wp_verify_nonce($_POST[self::return_plugin_namespace()."-shop_order-nonce"], self::return_plugin_namespace()."-shop_order-nonce") ){
     
            update_post_meta($post_id, '_'.self::return_plugin_namespace()."-subject", sanitize_text_field($_POST[self::return_plugin_namespace()."-subject"]));
              
            update_post_meta($post_id, '_'.self::return_plugin_namespace()."-heading", sanitize_text_field($_POST[self::return_plugin_namespace()."-heading"]));  
              
            update_post_meta($post_id, '_'.self::return_plugin_namespace()."-message", wp_kses_post($_POST[self::return_plugin_namespace()."-message"]));  
      
        }
    
    }
  
    public function woocommerce_email_subject_customer_invoice($string, $object){
    	
        $subject = get_post_meta($object->post->ID, '_'.self::return_plugin_namespace().'-subject', true);
      
        if (!empty($subject)){
    	
            return $subject;	
    	
        } else {
    	
    	    return $string;
    	
        }
    	
    }
  

    public function woocommerce_email_heading_customer_invoice($string, $object){
    	
        $heading = get_post_meta($object->post->ID, '_'.self::return_plugin_namespace().'-heading', true);
      
        if (!empty($heading)){
    	
            return $heading;	
    	
        } else {
    	
            return $string;
            
        }
    	
    }
  
  

    public function woocommerce_email_order_meta( $order, $sent_to_admin = true, $plain_text = false ) {
      
        $message = get_post_meta($order->get_id(), '_'.self::return_plugin_namespace().'-message', true);
      
        if (!empty($message)){
    	
            echo $message;	
    	
        } 
    	 
    }
  
    public function plugin_init(){
             
        if (! function_exists( 'is_woocommerce_activated' )){
            
            //load translations
            load_plugin_textdomain( self::return_plugin_namespace(), false, basename( dirname( __FILE__ ) ) . '/languages' );
            
            // Define the custom metabox:
            add_action('add_meta_boxes', array($this,"add_meta_boxes"),10,2);
      
            //save the metabox content
            add_action('save_post', array($this,"save_meta_boxes"), 10, 3);
      
            //Filter the subject
            add_filter( 'woocommerce_email_subject_customer_invoice', array($this,"woocommerce_email_subject_customer_invoice"), 1000, 2);
     
      
            //Filter the header
            add_filter( 'woocommerce_email_heading_customer_invoice', array($this,"woocommerce_email_heading_customer_invoice"), 1000, 2);
      
            //add the message to the invoice
            add_action( 'woocommerce_email_order_meta', array($this,"woocommerce_email_order_meta"), 9, 3 );
            
            
        }
    
    }
  
    /**
     * Gets an instance of our plugin.
     *
     * using the singleton pattern
     */
     
    public static function get_instance(){
        
        if (null === self::$instance) {
            
            self::$instance = new self();
            
        }
 
        return self::$instance;
        
    }
    




    public function __construct() {
        
    	 //run our hooks on plugins loaded to as we may need checks       
        add_action( 'woocommerce_init', array($this,'plugin_init'));
    
    }

}

$lh_woocommerce_invoicing_instance = LH_Woocommerce_invoicing_plugin::get_instance();

}

?>