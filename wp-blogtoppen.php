<?php
/*
Plugin Name: WP-Blogtoppen
Plugin URI: http://rasmus.nerdd.dk/software/wp-blogtoppen
Description: Et lille plugin, som embedder tracking JavaScript fra Blogtoppen.dk.
Version: 0.3
Author: Rasmus Bang Grouleff
Author URI: http://rasmus.nerdd.dk
*/

/*
Copyright 2009  Rasmus Bang Grouleff  (email : rasmus@nerdd.dk)

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

 1. Redistributions of source code must retain the above copyright notice, this
    list of conditions and the following disclaimer.
 2. Redistributions in binary form must reproduce the above copyright notice,
    this list of conditions and the following disclaimer in the documentation
    and/or other materials provided with the distribution.
 3. The name of the author may not be used to endorse or promote products derived
	from this software without specific prior written permission.
	
THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED
WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

function wp_blogtoppen_admin_menu() {
	if (function_exists('add_options_page')) {
		add_options_page('WP-Blogtoppen', 'WP-Blogtoppen', 'administrator', basename(__FILE__), 'wp_blogtoppen_options_page');
	}
}

function wp_blogtoppen_options_page() {
	$blogtoppen_key = get_option('blogtoppen_key');
	$blogtoppen_role = get_option('blogtoppen_role');
	
	$roles_obj = new WP_Roles();
	$rolenames = $roles_obj->get_names();
	
	$role_options = "";
	foreach($rolenames as $role => $name) {
	  $selected = $role == $blogtoppen_role ? ' selected="selected"' : "";
	  $role_options .= "<option value=\"{$role}\"{$selected}>{$name}</selected>";
	}
	
?>
		<div class="wrap">
		  <h2>WP-Blogtoppen</h2>
		  <form action="options.php" method="post">
		    <table class="form-table">
		      <tr valign="top">
		        <th scope="row">Blogtoppen N&oslash;gle</th>
		        <td><input type="text" name="blogtoppen_key" value="<?php echo $blogtoppen_key; ?>"/></td>
		      </tr>
		      <tr valign="top">
		        <th scope="row">V&aelig;lg Rolle</th>
		        <td><select name="blogtoppen_role"><?php echo $role_options; ?></select></td>
		      </tr>
		    </table>
        <?php settings_fields('blogtoppen_options_group'); ?>
		    <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>"></p>
		  </form>
	  </div>
<?php	
}

function register_blogtoppen_options() {
  register_setting('blogtoppen_options_group', 'blogtoppen_key');
  register_setting('blogtoppen_options_group', 'blogtoppen_role');
}

function wp_blogtoppen_footer() {
  global $current_user;
  $blogtoppen_role = get_option('blogtoppen_role');
  if (!$blogtoppen_role || $blogtoppen_role == "") {
    $blogtoppen_role = "administrator";
  }
  $role = get_role($blogtoppen_role);
  $role_caps = $role->capabilities;
  get_currentuserinfo();
  $user_caps = $current_user->allcaps;
  $caps_intersect = array_intersect($role_caps, $user_caps);
  $user_has_all_role_capabilities = $caps_intersect == $role_caps;
  if ($current_user->ID && $user_has_all_role_capabilities) {
    return;
  }
  $blogtoppen_key = get_option('blogtoppen_key');
  if (!$blogtoppen_key || $blogtoppen_key == "") {
    return;
  }

echo <<< END_FOOTER_SCRIPT
  <script type="text/javascript" src="http://www.blogtoppen.dk/media/js/bt_tracker.js"></script>
  <script type="text/javascript">
    bt_track_blog("{$blogtoppen_key}");
  </script>  
END_FOOTER_SCRIPT;
}

add_action('wp_footer', 'wp_blogtoppen_footer', 102);

add_action('admin_menu', 'wp_blogtoppen_admin_menu');
add_action('admin_init', 'register_blogtoppen_options');
?>