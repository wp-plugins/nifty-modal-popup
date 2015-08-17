<?php

/*
* Add default setting
*/
function skomfare2_nifty_modal_exit_intent_default_options(){
	$default_options_for_modal = get_option('albdesign_modal_popup_default_options',true);
	
	if(is_admin()){
		
		if(isset($default_options_for_modal['trigger_on_exit']) && isset($default_options_for_modal['trigger_on_exit_count']) ){
			
			return true;
		}
		
		$default_options_for_modal['trigger_on_exit'] = 'no';
		$default_options_for_modal['trigger_on_exit_count'] = 1;
		update_option('albdesign_modal_popup_default_options',$default_options_for_modal);
		
	}
	
	return true;

}
skomfare2_nifty_modal_exit_intent_default_options();


/*
* Add extra settings fields on the settings page 
*/
add_action('skomfare2_nifty_modal_settings_form','skomfare2_nifty_exit_intent_extra_settings');
function skomfare2_nifty_exit_intent_extra_settings($actual_modal_meta_infos){	

	if(!isset($actual_modal_meta_infos['trigger_on_exit'])){
		$actual_modal_meta_infos['trigger_on_exit'] = 'no';
	}
	
	if(!isset($actual_modal_meta_infos['trigger_on_exit_count'])){
		$actual_modal_meta_infos['trigger_on_exit_count'] = 1;
	}	
	
	?>
	
	<tr valign="top">
		<th scope="row" class="titledesc"><h2> Open on exit </h2></th>
		<td class="" >&nbsp;</td>
	</tr>	
	
	<tr valign="top">
		<th scope="row" class="titledesc"><label for="albdesign_modal_popup_trigger_trigger_on_exit">Open on exit</label></th>
		<td class="forminp" >
			<select  name="albdesign_modal_popup[trigger_on_exit]"  id="albdesign_modal_popup_trigger_trigger_on_exit">
				<option value="no"  <?php echo selected($actual_modal_meta_infos['trigger_on_exit'],'no');?>>No</option>
				<option value="yes"  <?php echo selected($actual_modal_meta_infos['trigger_on_exit'],'yes');?>>Yes</option>
			</select>
			<p class="description">Trigger on exit </p>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row" class="titledesc"><label for="albdesign_modal_popup_trigger_trigger_on_exit">How many times</label></th>
		<td class="forminp" >
			<input type="text"  name="albdesign_modal_popup[trigger_on_exit_count]"  id="albdesign_modal_popup_trigger_trigger_on_exit" value="<?php echo $actual_modal_meta_infos['trigger_on_exit_count']; ?>">
			<p class="description">How many times to open the popup on exit outside the window </p>
		</td>
	</tr>	
	
<?php
} //end function


/*
* Save extra modal settings
*/

add_filter('skomfare2_nifty_modal_before_save_modal_infos','skomfare2_nifty_save_modal_infos');
function skomfare2_nifty_save_modal_infos($existing_infos){
	
	//trigger on exit 
	$existing_infos['trigger_on_exit']  = (isset($_POST['albdesign_modal_popup']['trigger_on_exit']) ? $_POST['albdesign_modal_popup']['trigger_on_exit'] : 'no' );
	$existing_infos['trigger_on_exit_count']  = (isset($_POST['albdesign_modal_popup']['trigger_on_exit_count']) ? $_POST['albdesign_modal_popup']['trigger_on_exit_count'] : 1 );
	
	return $existing_infos;
}

/*
* Add extra JS
*/
add_action('skomfare2_nifty_modal_bottom_short_code_output','skomfare2_nifty_exit_intent_extra_js',10,2);
function skomfare2_nifty_exit_intent_extra_js($modal_id,$modal_settings){
		
	$open_on_exit = (isset($modal_settings['trigger_on_exit']) ? $modal_settings['trigger_on_exit'] : 'no');
	$open_on_exit_count = (isset($modal_settings['trigger_on_exit_count']) ? $modal_settings['trigger_on_exit_count'] : 1);
	
	//check if its a popup on exit
	if($open_on_exit == 'yes'){
	?>
		<script>
			jQuery(document).ready(function(){
				// Exit intent trigger
				
				triggerOnExitCount_<?php echo $modal_id;?> = <?php echo $open_on_exit_count; ?> ;
				triggerOnExitActualCount_<?php echo $modal_id;?> = 0 ;
				
				skomfare2_modalPopup_addEvent_mouse_exit(document, 'mouseout', function(evt) {
					if (evt.toElement == null && evt.relatedTarget == null ) {
						
						if(triggerOnExitCount_<?php echo $modal_id;?> > triggerOnExitActualCount_<?php echo $modal_id;?>){
							jQuery('div#albdesign-modal-<?php echo $modal_id;?>').addClass(' md-show');
						}
						triggerOnExitActualCount_<?php echo $modal_id;?> ++;
						
					};
				 
				});
			
			});
		</script>
	<?php 

	} // end check if its popup-on-exit
}

/*
* Add extra JS to head
*/

add_action('wp_head','skomfare2_nifty_exit_intent_extra_header_js');
function skomfare2_nifty_exit_intent_extra_header_js(){ ?>
	<script>
		// Exit intent
		
		var karipidhi= 0;
		
		function skomfare2_modalPopup_addEvent_mouse_exit(obj, evt, fn) {
			if (obj.addEventListener) {
				obj.addEventListener(evt, fn, false);
			}
			else if (obj.attachEvent) {
				obj.attachEvent("on" + evt, fn);
			}
		}
	</script>
<?php 
} //end function
?>