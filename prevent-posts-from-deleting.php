<?php
/**
 * @package prevent-posts-from-deleting
 * @version 1.2
 */
/*
Plugin Name: Prevent Pages From Deleting
Plugin URI: http://wordpress.org/plugins/prevent-posts-from-deleting
Description: Prevent selected pages from deleting by users
Version: 1.2
Author: AboZain, Mohammed J. AlBanna
Author URI: https://profiles.wordpress.org/abozain
*/

add_action( 'admin_menu', 'register_prevent_deleting' );

function register_prevent_deleting(){
	add_options_page( __('Prevent Deleting', 'PreventDeleting'), __('Prevent Deleting', 'PreventDeleting'), 'administrator', 'prevent_deleting', 'prevent_deleting_page'); 
	//add_menu_page( 'Prevent Deleting', 'Prevent Deleting', 'administrator', 'prevent_deleting', 'prevent_deleting_page', '', 81 ); 
}

//////////////////////////
# Load plugin text domain
add_action( 'init', 'pfd_plugin_textdomain' );
# Text domain for translations
function pfd_plugin_textdomain() {
    $domain = 'PreventDeleting';
    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
    load_textdomain( $domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo' );
    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
//////////////////////////


function prevent_deleting_page(){

	//echo 'my test';
	if(isset($_POST['submit']) ){
		$restricted_posts = $_POST['restricted_posts'];
		update_option( 'restricted_posts', $restricted_posts );
		echo '<br /> <br /> <h2 style="
  color: green;
  background-color: white;
  height: 15px;
  width: 95%;
  padding: 20px;">'.__('Saved Successfully', 'PreventDeleting').'</h2>';

	}else{
		$restricted_posts =  get_option('restricted_posts');
	}
	?>
        <div class="wrap">
            <h2><?php _e('Prevent Pages From Deleteing', 'PreventDeleting') ?></h2>
            <form method="post" action="">
				<?php settings_fields( 'disable-settings-group' ); ?>
            	<?php do_settings_sections( 'disable-settings-group' ); ?>
                <table class="form-table">
                    <?php 
					$post_type = array('page');
					foreach($post_type as $post_type): 
					
					// Get all post type attributes (eg. name, labels etc......)
					$post_type_attr = get_post_type_labels(get_post_type_object( $post_type )); ?>   
                     
                    <tr valign="top">
                        <th scope="row"> <?php printf( __('Select %s To Disable From Deleteing', 'PreventDeleting'), $post_type_attr->name) ?>
                        </th>
                        <td>
                            <?php 
                            $args = array(
                                'post_type' => $post_type,
                                'order' => 'ASC',
                                'orderby' => 'title',
                                'showposts' => -1,
								'suppress_filters' => true
                            );
                            $query = new WP_Query($args);
                            if($query->have_posts()):
                                echo '<ul class="' . $post_type . '">';
                                while($query->have_posts()): $query->the_post();
                                    $page_id = get_the_ID();
                                    
									if($restricted_posts):
										if(in_array($page_id,$restricted_posts)){
											$check = 'checked';
											}
										else{
											$check = '';
											}
									else:
										$check = '';
									endif;
                                    
                                    echo '<li>';
									echo '<label><input name="restricted_posts[]" type="checkbox" value="'. get_the_ID() .'"'. $check .'>' ; the_title(); echo '</label>';
								
									echo '</li>';
                                endwhile;
                                echo '</ul>';
                            endif;
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>	
<?php
}

//////////////////////////
add_action('wp_trash_post', 'restrict_post_deletion', 10, 1);
add_action('before_delete_post', 'restrict_post_deletion', 10, 1);


function restrict_post_deletion($post_id) {
	$restricted_posts =  get_option('restricted_posts');
		if(! is_array($restricted_posts)) { $restricted_posts = array();} 
  //if( ! is_super_admin() ) {

    if( in_array($post_id, $restricted_posts)) {
      exit(__('The page you were trying to delete is protected.', 'PreventDeleting' ));
    }
  //}
}