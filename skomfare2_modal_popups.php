<?php
/*
Plugin Name: Nifty Modal Popups Lite
Description: Create  modal popups based on http://tympanus.net/
Author URI: http://profiles.wordpress.org/skomfare2/
Version: 1.6
Author: Skomfare2
Author URI: http://profiles.wordpress.org/skomfare2/
*/

class Albdesign_Modal_Popups_Lite {
	private static $instance = null;
	private $plugin_path;
	private $plugin_url;
    private $text_domain = '';

	/**
	 * Creates or returns an instance of this class.
	 */
	public static function get_instance() {
		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
	 */
	private function __construct() {
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );

		//Frontend scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_frontend' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles_frontend' ) );

		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		
		// Add CPT
		add_action( 'init', array($this,'register_custom_modal_popup_cpt' ));	
		
		//add custom columns to modal popup list
		add_action( 'manage_posts_custom_column' , array($this,'show_custom_columns'), 10, 2 );
		add_action( 'manage_edit-albdesign_popup_cpt_columns' , array($this,'add_custom_columns'), 10, 2 );
		
		//add metabox 
		add_action( 'add_meta_boxes', array($this,'albdesign_modal_popup_meta_box'));
		
		//save modal popup new post infos
		add_action( 'save_post', array($this,'albdesign_modal_popup_save_new_popup' ));
		
		//handle button addition below post title
		add_action('admin_footer',  array($this, 'add_mce_popup'));
		
		//register the shortcode
		add_shortcode('skomfare2_modal_popup',array($this, 'add_modal_popup_shortcode'));
		
		
		//admin notices
		add_action( 'admin_notices', array( $this, '_wp_admin_notices' ) );
		//close admin notices ajax 
		add_action( 'wp_ajax_nifty_modal_light_hide_admin_notice', array( $this, 'nifty_modal_light_hide_admin_notice' ) );
		
		$this->run_plugin();
	}

	public function get_plugin_url() {
		return $this->plugin_url;
	}

	public function get_plugin_path() {
		return $this->plugin_path;
	}

    /**
     * Set default options for every new plugin.
     */
    public function activation() {

		
		//default options to be used for a new modal popup
		$default_options = array(
							'modal_background_color' 	=> '#ff0000',
							'modal_effect'				=> '1',
							'general_text_color'		=> '#ffffff',
							
							'title_text_color'			=> '#336699',
							'title_background_color'	=> '#f845D1',
							
							'overlay_color'				=> '#f845D1',
							'overlay_opacity'			=> '0.8',
							
							'modal_padding_top'			=> '40',
							'modal_padding_right'		=> '40',
							'modal_padding_bottom'		=> '40',
							'modal_padding_left'		=> '40',
							
							'modal_width'				=> '50',
							
							'triggered_by'				=> 'a',
							'trigger_inside_text'		=> 'Open popup',
						);
		update_option('albdesign_modal_popup_default_options', $default_options);
	}


	
// Register Custom Post Type
	function register_custom_modal_popup_cpt() {

	$labels = array(
		'name'                => _x( 'Modal Popup', 'Modal Popup', 'text_domain' ),
		'singular_name'       => _x( 'Modal Popup', 'Modal Popup', 'text_domain' ),
		'menu_name'           => __( 'Modal Popups', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Item:', 'text_domain' ),
		'all_items'           => __( 'All Modal Popups', 'text_domain' ),
		'view_item'           => __( 'View Modal Popup', 'text_domain' ),
		'add_new_item'        => __( 'Add New Modal Popup', 'text_domain' ),
		'add_new'             => __( 'Add New', 'text_domain' ),
		'edit_item'           => __( 'Edit Modal Popup', 'text_domain' ),
		'update_item'         => __( 'Update Modal Popup', 'text_domain' ),
		'search_items'        => __( 'Search Modal Popup', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$args = array(
		'label'               => __( 'albdesign_modal_popup', 'text_domain' ),
		'description'         => __( 'Modal Popup', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( 'title','editor' ),
		'taxonomies'          => array(),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 65,
		'can_export'          => false,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'rewrite'             => false,
		'capability_type'     => 'page',
		'register_meta_box_cb' => array($this,'albdesign_modal_popup_meta_box'), // Callback function for custom metaboxes
	);
	register_post_type( 'albdesign_popup_cpt', $args );

}


	public function _wp_admin_notices() {
		
		global $current_user;
		$has_closed_before =  get_user_meta($current_user->ID,'_hide_nifty_light_admin_notice', true);
		
		if($has_closed_before=='yess'){
			return ;
		}
		 
		?>
			<div id="message" class="updated">
				<?php echo $this->_wp_admin_notices_text(); ?> 
				
			</div>
		<?php

	}
	
	
	public function _wp_admin_notices_text() {
		$return_html = '<p>
					<strong>Hello from Nifty Modal Popup! </strong> <a href="https://wordpress.org/support/view/plugin-reviews/nifty-modal-popup?filter=5#postform" target="_blank" > Kindly rate us ★★★★★ on Wordpress.org. The first 20 persons rating us 5 stars will <b>win a free copy</b> of the Pro version. Leave your email address on the review so we can send it. A big THANK YOU in advance.</a>  ( <a href="#" class="admin_notice_close_nifty_light_message">close</a> )
				</p>';
		return $return_html;
	}

	
	function nifty_modal_light_hide_admin_notice(){
		
		global $current_user;
		update_user_meta($current_user->ID, '_hide_nifty_light_admin_notice', 'yess');
		die(json_encode(array('status'=>'ok')));
		
	}
	
	/*
	* Add the metabox , remove slug 
	*/
	function albdesign_modal_popup_meta_box(){
   
		//add color/text/padding/margin metabox 
		add_meta_box('albdesign-cpt-meta-box', 'Modal Popup', array($this,'albdesign_add_metabox_to_modal_popup'), 'albdesign_popup_cpt','normal','high');
		

		//remove "slug" metabox
		remove_meta_box( 'slugdiv', 'albdesign_popup_cpt', 'normal' );

	}

	
	/*
	* Add the metabox to EDIT/CREATE page for modal popup CPT
	*/
	function albdesign_add_metabox_to_modal_popup(){
		global $post;

		
		$actual_modal_meta_infos = get_post_meta( $post->ID, 'albdesign_single_modal_popup_infos',true);
		
		if(!is_array($actual_modal_meta_infos)){
			$actual_modal_meta_infos = $this->get_default_settings_for_modal();
		}
		
		?>

		
		<form  action="" method="post">
		    <table class="form-table" >
				<tbody>
				
					<tr valign="top">
						<th scope="row" class="titledesc"><label for="albdesign_modal_popup_effect">Popup Effect</label></th>
						<td class="forminp" >
							<select name="albdesign_modal_popup[modal_effect]"  id="albdesign_modal_popup_effect"  >
								<option value="1" <?php selected($actual_modal_meta_infos['modal_effect'],1); ?>>Fade in &amp; Scale</option>
								<option value="2" <?php selected($actual_modal_meta_infos['modal_effect'],2); ?>>Slide in right</option>
								<option value="3" <?php selected($actual_modal_meta_infos['modal_effect'],3); ?>>Slide in bottom</option>
								<option value="4" <?php selected($actual_modal_meta_infos['modal_effect'],4); ?>>Newspaper</option>
								<option value="5" <?php selected($actual_modal_meta_infos['modal_effect'],5); ?>>Fall</option>
								<option value="6" <?php selected($actual_modal_meta_infos['modal_effect'],6); ?>>Side Fall</option>
								<option value="7" <?php selected($actual_modal_meta_infos['modal_effect'],7); ?>>Sticky Up</option>
								<option value="8" <?php selected($actual_modal_meta_infos['modal_effect'],8); ?>>3D Flip - horizontal</option>
								<option value="9" <?php selected($actual_modal_meta_infos['modal_effect'],9); ?>>3D Flip - vertical</option>
								<option value="10" <?php selected($actual_modal_meta_infos['modal_effect'],10); ?>>3D Sign</option>
								<option value="11" <?php selected($actual_modal_meta_infos['modal_effect'],11); ?>>Super Scaled</option>
								<option value="12" <?php selected($actual_modal_meta_infos['modal_effect'],12); ?>>Just Me</option>
								<option value="13" <?php selected($actual_modal_meta_infos['modal_effect'],13); ?>>3D Slit</option>
								<option value="14" <?php selected($actual_modal_meta_infos['modal_effect'],14); ?>>3D Rotate Bottom</option>
								<option value="15" <?php selected($actual_modal_meta_infos['modal_effect'],15); ?>>3D Rotate In Left</option>
								<option value="17" <?php selected($actual_modal_meta_infos['modal_effect'],17); ?>>Let me in</option>
								<option value="18" <?php selected($actual_modal_meta_infos['modal_effect'],18); ?>>Make way!</option>
								<option value="19" <?php selected($actual_modal_meta_infos['modal_effect'],19); ?>>Slip from top</option>
							</select>
							<p class="description">Select the effect of the modal popup</p>
						</td>
					</tr> 	

					<tr valign="top">
						<th scope="row" class="titledesc"><label for="albdesign_modal_popup_triggered_by">Text for the clickable element</label></th>
						<td class="forminp" >
							<input name="albdesign_modal_popup[trigger_inside_text]"  id="albdesign_modal_popup_trigger_inside_text" value="<?php echo  $actual_modal_meta_infos['trigger_inside_text']; ?>"  />
								
							<p class="description">Text for the clickable element </p>
						</td>
					</tr>						


					
					
				</tbody>
			</table>        
			
			
			<div  style="opacity:1">
				<a href="http://pidhasome.com/albdesign/plugins/nifty_modal_windows_wordpress_plugin/documentation/documentation/" target="_blank">Documentation</a> <br><br>
				<a href="http://gplplugins.com/product/nifty-modal-window-effect/"  target="_blank">Click here to see and purchase the full  features and options for this plugin</a> <br><br>
				
				<?php echo $this->_wp_admin_notices_text();?>
				
			</div>
			
			<input type="hidden" name="prevent_delete_meta_movetotrash" id="prevent_delete_meta_movetotrash" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />

			
		</form> 		
		<?php
	} //ends albdesign_add_metabox_to_modal_popup
	
	
	/*
	* Save the  newly created modal popup post options/infos
	*/
	function albdesign_modal_popup_save_new_popup($post_id){

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
			 return $post_id;
		}
	
		//empty array
		$update_modal_infos = array();
		

		
		if(isset($_POST['albdesign_modal_popup'])){
		
			//prevent delete CPT  custom meta when the CPT is moved to trash 
			if (!wp_verify_nonce($_POST['prevent_delete_meta_movetotrash'], plugin_basename(__FILE__))) { 
				return $post_id; 
			}
		

			$update_modal_infos['modal_effect']				 	= $_POST['albdesign_modal_popup']['modal_effect'];


			//trigger inside text
			if($_POST['albdesign_modal_popup']['trigger_inside_text']==''){
				$_POST['albdesign_modal_popup']['trigger_inside_text'] = 'Open Popup';
			}
			$update_modal_infos['trigger_inside_text'] 			= $_POST['albdesign_modal_popup']['trigger_inside_text']; 
			
			//save the custom options for this modal
			update_post_meta( $post_id, 'albdesign_single_modal_popup_infos', $update_modal_infos );
		
		}
	
	} //ends albdesign_modal_popup_save_new_popup
	

    /**
     * Enqueue and register scripts fronend.
     */	
	public function register_scripts_frontend(){
		wp_enqueue_script( 'albdesign-modal-popup-classie',$this->get_plugin_url().'js/classie.js',array( 'jquery' ),'1',true);
		
		wp_enqueue_script( 'albdesign-modal-popup-effects',$this->get_plugin_url().'js/modalEffects.js',array( 'jquery' ),'1.0.0',true);
		
		wp_enqueue_script( 'albdesign-modal-popup-cssParser',$this->get_plugin_url().'js/cssParser.js',array( 'jquery' ),'1.0.0',true);
		wp_enqueue_script( 'albdesign-modal-popup-polyfill',$this->get_plugin_url().'js/css-filters-polyfill.js',array( 'jquery' ),'0.22',true);

	}
	

    /**
     * Enqueue and register CSS frontend.
     */
	public function register_styles_frontend(){
		wp_enqueue_style( 'albdesign-modal-popups',$this->get_plugin_url().'css/modal_popup_component.css' );
	}
	
    /**
     * Hooking for head/media buttons
     */
    private function run_plugin() {
		add_action('wp_head',array($this,'header_elements'));
		
		//Adding "embed form" button
        add_action('media_buttons', array($this, 'add_form_button_to_page'), 20);
	}
	

    /**
     * Add button on page/post aside ADD MEDIA button
     */	
	public function add_form_button_to_page(){
	
		//dont display the "add albdesign popup" button when creating/editing a modal popup
		$curret_screen =  get_current_screen();
		if($curret_screen->id =='albdesign_popup_cpt'){
			return;
		}
		
		
		// display button matching new UI
        echo '<style>.gform_media_icon{
                   
                    display: inline-block;
                    height: 16px;
                    margin: 0 2px 0 0;
                    vertical-align: text-top;
                    width: 16px;
                    }
                    .wp-core-ui a.gform_media_link{
                     padding-left: 0.4em;
                    }
                 </style>
                  <a href="#TB_inline?width=480&inlineId=select_albdesign_modal_popup_form" class="thickbox button gform_media_link" id="add_gform" title="Add Modal Popup">Add Modal Popup</a>';
	}
	
    /*
	* Add the MCE editor button next to the "add media" button
	*/
    public static function add_mce_popup(){
	

		
        ?>
        <script>
            function AlbDesign_Modal_Popup_InsertForm_Lite(){
                var form_id = jQuery("#add_albdesign_modal_popup_id").val();
                if(form_id == ""){
                    alert("Please select a modal popup");
                    return;
                }

				//Custom modal effect
				var modalEffect = jQuery("#albdesign_modal_popup_effect_shortcode  option:selected").val();

                var form_name = jQuery("#add_albdesign_modal_popup_id option[value='" + form_id + "']").text().replace(/[\[\]]/g, '');

                window.send_to_editor("[skomfare2_modal_popup id=\"" + form_id + "\"]");
            }
			
			
			jQuery('document').ready(function(){
				jQuery('a.admin_notice_close_nifty_light_message').click(function(){
					jQuery.ajax({
						url:ajaxurl,
						type:'POST',
						data:'action=nifty_modal_light_hide_admin_notice',
						success:function(result){}
					});
					return false;
				});
			});
			
        </script>

        <div id="select_albdesign_modal_popup_form" style="display:none;">
            <div class="wrap ">
                <div>
                    <div style="padding:15px 15px 0 15px;">
                        <h3 style="color:#5A5A5A!important; font-family:Georgia,Times New Roman,Times,serif!important; font-size:1.8em!important; font-weight:normal!important;">Insert a Modal Popup</h3>
                        <span>
                           Select a modal popup below to add it to your post or page.
                        </span>
                    </div>
                    <div style="padding:15px 15px 0 15px;">
                        <select id="add_albdesign_modal_popup_id">
                           
                            <?php
                                $modals = Albdesign_Modal_Popups_Lite::get_all_modal_popup();
								
								if(is_array($modals)){
									echo '<option value="">Select a Modal Popup </option>';
									foreach($modals as $modal){
										?>
										<option value="<?php echo absint($modal['post_id']) ?>"><?php echo esc_html($modal['post_title']) ?></option>
										<?php
									}
								}else{
									echo '<option value="">No Published Modals Found </option>';
								}
                            ?>
                        </select> <br/>
                        
                    </div>

                    <div style="padding:15px;">
                        <input type="button" class="button-primary" value="Insert Modal Popup" onclick="AlbDesign_Modal_Popup_InsertForm_Lite();"/>&nbsp;&nbsp;&nbsp;
                    <a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;">Cancel</a>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }	
	
	/*
	* Return all published popups as array
	*/
	static function get_all_modal_popup(){
	
		query_posts(array(
		   'posts_per_page' => -1 ,
		   'post_type' 		=> 'albdesign_popup_cpt',
		   'post_status' 	=> 'publish',
		   'orderby' 		=> 'title',
		   'order' 			=> 'ASC'
		   
		));
	
		if (have_posts()) {
			while (have_posts()){
				the_post();
				
				$modals_array['post_id'] = get_the_ID();
				$modals_array['post_title'] = get_the_title();
				$all_modals[] = $modals_array;
			}
			return $all_modals;
		}
		
		return false;
		
		
	}

	/*
	* Add the code to the header of WP hook
	*/
	public function header_elements(){
		
		echo "<script type='text/javascript'>
				var polyfilter_scriptpath = '". $this->get_plugin_url() ."js/lib/';
				var polyfilter_skip_stylesheets = true;  
			 </script>";
	}
	
	/*
	* Get the default options for a new popup
	*/
	public function get_default_settings_for_modal(){
		$default_options_for_modal = get_option('albdesign_modal_popup_default_options',true);
		return $default_options_for_modal;
	}

	/*
	 *  Shortcode parser
	 */
	function add_modal_popup_shortcode($atts){
	
		extract(
				shortcode_atts( 
								array(
										'id' => '',
										'title' => 'show',
										'custom_title' => 'ThisIsDefaultTitle',
										'custom_effect' => 'ThisIsDefaultCustomEffect'
									), 
								$atts
				)
			);
		
		$theModalPopup = get_post($id);
		
		//if post ID doesnt exists return an empty string
		if(!$theModalPopup){
			return '';
		}
		
		//if post isnt published return an empty string
		if($theModalPopup->post_status!='publish'){
			return '';
		}		
		

		//modal popup custom meta
		$modal_custom_meta =  get_post_meta($id,'albdesign_single_modal_popup_infos',true);
		
		$post_title = $theModalPopup->post_title;
		
		$post_content = $theModalPopup->post_content;
			
		$modal_effect = $modal_custom_meta['modal_effect'];

		//text for the clickable element
		$textForTheClickableElement = $modal_custom_meta['trigger_inside_text'];
		
		
		ob_start();
		
		?>	
		
		<div class="md-modal md-effect-<?php echo $modal_effect;?>" id="albdesign-modal-<?php echo $id;?>">
			<div class="md-content albdesign_modal_popup_<?php echo $id;?>">

					<h3><?php echo apply_filters('the_title',$post_title); ?></h3>
				
				<div>
					<?php echo $post_content; ?>
					<?php echo apply_filters('albdesign_modal_popup_close_button-'.$id,'<button class="md-close">Close</button>', $id);?>
				</div>
			</div>
		</div>	
		<div class=" albdesign-modal-overlay-<?php echo $id;?> md-overlay"  ></div><!-- the overlay element -->

		<a class="md-trigger" data-modal="albdesign-modal-<?php echo $id;?>"> <?php echo $textForTheClickableElement;?> </a>
		<?php 

		$html_to_return = ob_get_clean();
		
		return $html_to_return;
	}
	

	
	//SHOW ADDITIONAL COLUMNS ON MODAL POPUP LIST
	function show_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'shortcode' :
					 echo '[skomfare2_modal_popup id="'.$post_id.'"]';
				break;
		}
	}
	

	function add_custom_columns($columns) {
		
		$columns['shortcode'] = 'Shortcode';

		return $columns;
	}
	
}


//Start it all 
Albdesign_Modal_Popups_Lite::get_instance();