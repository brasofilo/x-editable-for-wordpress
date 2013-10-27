<?php
/**
 * Plugin Name: (B5F) X-Editable in User Edit/Profile
 * Description: Testing how to use X-editable inside WordPress admin area.
 * Plugin URI:  https://github.com/brasofilo/x-editable-for-wordpress
 * Version:     2013.10.27
 * Author:      Rodolfo Buaiz
 * Author URI:  http://brasofilo.com
 * License:     GPLv3
 */

add_action(
	'plugins_loaded',
	array ( B5F_X_Editable_Profile::get_instance(), 'plugin_setup' )
);

class B5F_X_Editable_Profile
{
	protected static $instance = NULL;
	public $plugin_url = '';
	public function __construct() {}

    public static function get_instance()
	{
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
	}

    /**
     * Regular plugin work
     * 
     */
	public function plugin_setup()
	{
		$this->plugin_url = plugins_url( '/', __FILE__ );
        add_action( 'admin_init', array( $this, 'init') );
        add_action( 'wp_ajax_x_editable_profile', array( $this, 'x_editable_profile' ) );
        foreach( array( 'profile', 'user-edit' ) as $hook )
            add_action( "admin_print_scripts-$hook.php", array( $this, 'enqueue' ) );
    }


    /**
     * Add profile fields
     * 
     * @wp-hook admin_init
     */
    public function init() 
    {
        add_action( 'show_user_profile', array( $this, 'profile_fields' ), 10 );
        add_action( 'edit_user_profile', array( $this, 'profile_fields' ), 10 );
    }

    /**
     * Admin styles and scripts
     * 
     * @wp-hook admin_print_scripts-profile/user-edit
     */
    public function enqueue() 
    {
        $uid = 'admin_print_scripts-user-edit.php' == current_filter() 
            ? $_GET['user_id'] : get_current_user_id();
        wp_enqueue_style( 'bs-comb', "{$this->plugin_url}css/bootstrap-combined.min.css" );
        wp_enqueue_style( 'bs-ed', "{$this->plugin_url}css/bootstrap2-editable.css" );
        wp_register_script( 'bs-min', "{$this->plugin_url}js/bootstrap.min.js" );
        wp_register_script( 'bs-ed-min', "{$this->plugin_url}js/bootstrap2-editable.min.js" );
        wp_enqueue_script( 
             'x-editable-profile' 
            , "{$this->plugin_url}js/x-editable-profile.js"
            , array( 'bs-min', 'bs-ed-min', 'jquery' )
        );
        wp_localize_script( 
             'x-editable-profile' 
            , 'xep_ajax' 
            , array( 
                 'ajaxurl'      => admin_url( 'admin-ajax.php' ) 
                , 'ajaxnonce'   => wp_create_nonce( 'ajax_post_validation' ) 
                , 'user_id'    => $uid
            ) 
        );
    }

    /**
     * Ajax callback for X-editable
     * 
     * @return  false/JSON
     * @wp-hook wp_ajax_*
     */
    public function x_editable_profile()
    {
        check_ajax_referer( 'ajax_post_validation', 'security' );
        $return = false;
        
        # POSTED MY_USERNAME
        if( isset( $_POST['name'] ) && 'my_username' == $_POST['name'] )
            $return = update_user_meta( absint($_POST['user_id']), 'option_username', esc_attr( $_POST['value'] ) );
        
        # POSTED MY_STATUS
        if( isset( $_POST['name'] ) && 'my_status' == $_POST['name'] )
            $return = update_user_meta( absint($_POST['user_id']), 'option_status', esc_attr( $_POST['value'] ) );
        
        # RESPONSE
        if( !$return )
            wp_send_json_error( array( 'error' => __( 'Ajax ERROR: could not save data.' ) ) );
        else
            wp_send_json_success( $return );
    }

    
    /**
     * X-editable markup
     * 
     * Some attributes passed with data-*
     * others are set in JS
     * 
     * @param   object      $user
     * @wp-hook show_user_profile
     * @wp-hook edit_user_profile
     */
    public function profile_fields( $user ) 
    { 
        if( !$has_username = get_the_author_meta( 'option_username', $user->ID ) )
            $has_username = '';
        if( !$has_status = get_the_author_meta( 'option_status', $user->ID ) )
            $has_status = '1';
        ?>
        <table class="form-table">
            <tr>
                <th><label for="agree">Username</label></th>
                <td>
                    <div>
                        <a href="#" id="my_username" data-type="text" data-pk="1" data-placement="right" data-title="Enter username"><?php echo $has_username; ?></a>
                    </div>
                </td>			
            </tr>
            <tr>
                <th><label for="agree">Status</label></th>
                <td>
                    <div>
                        <a href="#" id="my_status" data-type="select" data-placement="right" data-pk="1" data-title="Select status" data-value="<?php echo $has_status; ?>"></a>
                    </div>
                </td>			
            </tr>
        </table>
        <?php 
    }
}