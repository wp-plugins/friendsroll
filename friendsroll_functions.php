<?php

/**
 * FriendsRoll Wordpress plugin
 * Copyright (c) 2008 76design/Thornley Fallis Communications
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
*/


function widget_friendsroll_admin_init()
{
	include_once("admin-functions.php");
	add_submenu_page('edit.php','My Friends', '<strong>friends</strong>roll', 1, 'friendsroll', 'widget_friendsroll_admin');
}

function widget_friendsroll_create_database()
{
  	global $wpdb;
  		$table = FRIENDSROLL_DB_TABLE;
 
  		if (!preg_match('/'.$wpdb->prefix.'/', $table)) $table = $wpdb->prefix . "friendsroll"; 
  		
   	if ($wpdb->get_var("SHOW TABLES LIKE '" . $table . "'") != $table) {
   		$query = "
   			CREATE TABLE IF NOT EXISTS " . $table . " (
   				id int(11) unsigned not null auto_increment,
   				name varchar(255),
   				email varchar(255) not null,
   				url varchar(255) not null,
   				website_name varchar(255),
   				status enum('pending', 'accepted', 'rejected') default 'pending',
   				created datetime not null,
   				modified datetime not null,
   				PRIMARY KEY (id),
   				UNIQUE INDEX (email)
   			)
   		";
   		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
   		dbDelta($query);

   		$default = "
   			INSERT INTO " . $table . " VALUES (
   				null, 'friendsroll.com', 'info@friendsroll.com',
   				'http://friendsroll.com', 'Friends Roll', 'accepted',
   				now(), now()
   			)
   		";
   		$wpdb->query($default);
   	}
}

function widget_friendsroll_display_nav($current_page=0){
	?>
		<a href="#" id="fr_prev_page" style="display:none">&lt; prev</a>&nbsp;&nbsp;<a href="#" id="fr_next_page">next &gt;</a>
	<?php
}

function widget_friendsroll_admin()
{
	$fs = new FriendStore();
	$updated = false;
	if ($_POST['friendsroll_moderate']==1)
	{
		$f_update = $fs->find((int)$_POST['friendsroll_friend_id']);
		if ($_POST['friendsroll_submit'] == 'Accepted') $f_update->_status = "accepted";
		else if ($_POST['friendsroll_submit'] == 'Denied') $f_update->_status = "rejected";
		else if ($_POST['friendsroll_submit'] == 'Pending') $f_update->_status = "pending";
		$fs->update($f_update);

		if ($f_update->_status == "accepted") {
			wp_mail($f_update->_email, 'Welcome to my friendsroll!', sprintf(FRIENDSROLL_ACCEPT_EMAIL, trim(get_option('blogname')), trim(get_option('siteurl'))));
		}

		$updated = true;
	}

	$friends = $fs->findAll(null, 0, "all");
	if ($updated) {
		print "<div id='message' class='fade updated'><p><strong>$f_update->_name</strong> was set to <strong>$f_update->_status</strong>.</p></div>";
	}
	?>
<div class="wrap">
	<h2><strong>friends</strong>roll</h2>
	<p>The following people have requested to be part of your <strong>friends</strong>roll. You can choose to approve or deny them, or just leave them for later. Approved people will show up on your <strong>friends</strong>roll sidebar.</p>
	<p>Currently showing
		<select name="cat_id">
			<option selected="selected" value="all">All</option>
			<option value="2">Pending</option>
			<option value="2">Accepted</option>
			<option value="2">Denied</option>
		</select>
		links ordered by
		<select name="order_by">
			<option value="order_id">Link ID</option>
			<option selected="selected" value="order_name">Date</option>
			<option value="order_url">Website</option>
			<option value="order_rating">Name</option>
		</select>
		<input type="submit" value="Update" name="action"/>
	</p>
	<table class="widefat">
		<thead><tr><th>&nbsp;</th><th>Name</th><th>Email</th><th>Website</th><th>Date Requested</th><th>Status</th><th>Update Status</th></tr></thead>
		<?php
		$i = 0;
		foreach($friends as $friend)
		{
			$class = '';
			if ($i % 2) $class=' class="alternate"';
			echo "<tr$class>
						<td><img src='" . widget_friendsroll_favicon($friend->_url) . "' width='16' height='16' /></td>
						<td>$friend->_name</td><td>$friend->_email</td>
						<td><a href=\"$friend->_url\" target=\"_blank\">$friend->_url</a></td>
						<td>$friend->_created</td>
						<td>$friend->_status</td>
						<td>
							<p style='margin: auto;'>
							<form action='' method='POST'>
								<input type='hidden' name='friendsroll_moderate' value='1' />
								<input type='hidden' name='friendsroll_friend_id' value='$friend->_id' />";
			switch($friend->_status) {
				case "pending" :
					echo "<input type='submit' name='friendsroll_submit' value='Accepted' />
								<input type='submit' name='friendsroll_submit' value='Denied' />";
					break;
				case "rejected" :
					echo "<input type='submit' name='friendsroll_submit' value='Accepted' />
								<input type='submit' name='friendsroll_submit' value='Pending' />";
					break;
				default: // here we are accepted
					echo "<input type='submit' name='friendsroll_submit' value='Pending' />
								<input type='submit' name='friendsroll_submit' value='Denied' />";
			}
			echo "	</form>
							</p>
						</td>
						</tr>";
			$i++;
		}
		?>
	</table>
</div>
<?php
}

function widget_friendsroll_enqueue_script() {
	wp_enqueue_script("jquery_latest", get_bloginfo('url') . "/wp-content/plugins/friendsroll/js/jquery.js");
	wp_enqueue_script("jquery.form", get_bloginfo('url') . "/wp-content/plugins/friendsroll/js/jquery.form.js", array('jquery_latest'), '');
	wp_enqueue_script("jquery.hint", get_bloginfo('url') . "/wp-content/plugins/friendsroll/js/jquery.hint.js", array('jquery_latest'), '');
	wp_enqueue_script("friendsroll", get_bloginfo('url') . "/wp-content/plugins/friendsroll/js/friendsroll.js", array('jquery_latest'), '');
}

function widget_friendsroll_head() {
	?>
	<link rel="stylesheet" href="<?= get_bloginfo('url') ?>/wp-content/plugins/friendsroll/friendsroll.css" type="text/css" media="screen" />
	<script type="text/javascript">
		var friendsroll_url = "<?=  get_bloginfo('url') ?>";
	</script>
	<?php
}

function widget_friendsroll_initjs() {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		friendsroll.init();
	});
	</script>
	<?php
}

function widget_friendsroll_clear_post_vars()
{
	$required_checks = array("friendsroll_name", "friendsroll_email", "friendsroll_website_name", "friendsroll_url");
	foreach($required_checks as $toclear)
	{
		unset($_POST[$toclear]);
	}
}

function widget_friendsroll_validate()
{
	$required_checks = array("friendsroll_name", "friendsroll_email", "friendsroll_website_name", "friendsroll_url", "friendsroll_captcha");
	$validators = array(
		"friendsroll_name" => null,
		"friendsroll_email" => "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}/",
		"friendsroll_website_name" => null,
		"friendsroll_url" => "/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)[\/]?[0-9A-Z_-~&\?]*/i",
	);
	$message_lookup = array(
		"isset_friendsroll_name" => "Provide your name.",
		"isset_friendsroll_email" => "Provide your email.",
		"isset_friendsroll_website_name" => "Provide your site name.",
		"isset_friendsroll_url" => "Provide your site URL.",
		"isset_friendsroll_captcha" => "Answer the security question.",
		"valid_friendsroll_name" => "",
		"valid_friendsroll_email" => "Provide a valid email address.",
		"valid_friendsroll_website_name" => "",
		"valid_friendsroll_url" => "Provide a valid site URL.",
		"valid_friendsroll_captcha" => "Wrong security answer.",
	);
	$messages = array();
	foreach ($required_checks as $to_check)
	{
		$value = $_POST[$to_check];
		if ($to_check == "friendsroll_captcha") {
			$fss = new FriendsrollSecurity();
			if (!$fss->validate($value, $_POST['friendsroll_sqk'])) {
				$messages[] = $message_lookup['valid_' . $to_check];
				continue;
			}
		}
		if (!isset($value) || strlen($value) < 1)
		{
			$messages[] = $message_lookup["isset_" . $to_check];
			continue;
		}
		if ($validators[$to_check] != null)
		{
			if (!preg_match($validators[$to_check], $value))
			{
				$messages[] = $message_lookup['valid_' . $to_check];
			}
		}
	}

	if(empty($messages)) {
		$fs = new FriendStore();
		$existing = $fs->findByEmail($_POST['friendsroll_email']);
		if (!empty($existing)) {
			$messages[] = "It appears you have already requested to be on my <strong>friends</strong>roll.";
		}
	}

	return $messages;
}

// This function prints the sidebar widget--the cool stuff!
function widget_friendsroll($args) {
	global $friendAdded;
	global $friendMessages;
	// $args is an array of strings which help your widget
	// conform to the active theme: before_widget, before_title,
	// after_widget, and after_title are the array keys.
	extract($args);

	// Collect our widget's options, or define their defaults.
	$options = get_option('widget_friendsroll');
	$toshow = empty($options['toshow']) ? 5 : $options['toshow'];

	$fs = new FriendStore();
	$approved_friends = $fs->findAll('accepted', 0, $toshow);

	$fss = new FriendsrollSecurity();
	$question = '';
	$question_key = $fss->getRandom($question);

	// It's important to use the $before_widget, $before_title,
	// $after_title and $after_widget variables in your output.
	if(isset($before_widget) && preg_match("/id=[\"\']friendsroll[\"\']/", $before_widget)){
		echo $before_widget;
	}else{
		echo "<div id=\"friendsroll\">";
		$after_widget = "</div>";
	}

	$fr_form_class = $fr_message_class = 'class="off"';
	if (sizeof($friendMessages) > 0)
	{
		$messages = implode("</li><li>", $friendMessages);
		$messages = "<ul><li>" . $messages . "</li></ul>";
		$messages = "<h3>Oops!</h3>" . $messages;
		$fr_form_class = $fr_message_class = 'class="on error"';
	}
	else if ($friendAdded == true)
	{
		widget_friendsroll_clear_post_vars();
		$messages = "<h3>Success!</h3>";
		$messages .= "<p>You've been added to the list of pending friend requests.</p>";
		$fr_form_class = $fr_message_class = 'class="on success"';
	}
	?>
	<div>
		<a name="friendsroll"></a>
		<h1><em>friends</em>roll</h1>

		<ol id="fr_friendslist">
		<?php
		$i = 0;
		foreach($approved_friends as $friend)
		{
			$class = "even";
			if ($i % 2) $class = "odd";
			widget_friendsroll_displayfriend($friend, $class);
			$i++;
		}
		if ($friendAdded == true) $fr_form_class = 'class="off"';

		$url = $_POST['friendsroll_url'];
		if (!isset($_POST['friendsroll_url'])) $url = "http://";
		$current_page = 0;
		?>
		</ol>
		<?php if ($fs->findMaxPage() > 1) { ?>
		<div id="fr_page_nav"><?php widget_friendsroll_display_nav($current_page); ?></div>
		<?php }	?>
		<ul>
			<li>
				<a href="" id="friendsroll_addbutton">Want to be my friend?</a>
				<form id="friendsroll_addform" action="#friendsroll" method="POST" <?php echo $fr_form_class; ?>>
					<div id="friendsroll_message" <?php echo $fr_message_class; ?>>
						<?php echo $messages; ?>
					</div>
					<input type="hidden" name="fr_newfriend" value="1" />
					<input class="text" name="friendsroll_name" type="text" value="<?php echo htmlentities($_POST['friendsroll_name']); ?>" title="Your name" />
					<input class="text" name="friendsroll_email" type="text" value="<?php echo htmlentities($_POST['friendsroll_email']); ?>" title="Your email" />
					<input class="text" name="friendsroll_website_name" type="text" value="<?php echo htmlentities($_POST['friendsroll_website_name']); ?>" title="Your site's name" />
					<input class="text" name="friendsroll_url" type="text" value="<?php echo htmlentities($url) ?>" />
					<input name="friendsroll_sqk" type="hidden" value="<?php echo $question_key ?>" />
					<p><?php echo $question; ?></p>
					<input class="text" name="friendsroll_captcha" type="text" value="" title="Answer?" />
					<input class="button" type="submit" name="submit" value="Add me" />
				</form>
			</li>
			<li><a href="http://friendsroll.com">Want your own <strong>friends</strong>roll?</a></li>
		</ul>

		<div id="fr_logo">
			<a href="#"><img src="<?php bloginfo('url'); ?>/wp-content/plugins/friendsroll/images/friendsroll.gif" /></a>
		</div>

	</div>
	<?
	echo $after_widget;
}

function widget_friendsroll_favicon($url) {
	$urlbits = parse_url($url);
	$favicon = $urlbits['scheme'] . "://" . $urlbits['host'] . "/favicon.ico";
	//if (!@file_get_contents($favicon)) return get_bloginfo('url') . "/wp-content/plugins/friendsroll/images/default_favicon.gif";
	return $favicon;
}

function widget_friendsroll_displayfriend($f, $class = "odd")
{
	$favicon = widget_friendsroll_favicon($f->_url);
	?>
	<li class="<?php echo $class?>">
		<a href="<?php echo $f->_url; ?>">
			<img src="<?php echo $favicon; ?>" width="16" height="16" />
			<p class="name"><?php echo $f->_name; ?></p>
			<p><?php echo $f->_website_name; ?></p>
		</a>
	</li>
	<?php
}
