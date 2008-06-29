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


/*
Plugin Name: FriendsRoll
Plugin URI: http://friendsroll.com
Description: The friends roll wordpress plugin.
Author: 76design
Version: 1.4
Author URI: http://76design.com
*/
@session_start();
if (file_exists('../../../wp-config.php') && !function_exists('wp_mail'))
{
	require_once('../../../wp-config.php');
}

include_once(dirname(__FILE__) . "/class_friendstore.php");
include_once(dirname(__FILE__) . "/class_friendsroll_security.php");
include_once(dirname(__FILE__) . "/friendsroll_functions.php");

//ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . dirname(__FILE__));
global $wpdb;

$fr_security_questions =

define("FRIENDSROLL_DB_TABLE", $wpdb->prefix . "friendsroll");

define("FRIENDSROLL_ADMIN_EMAIL",
"
You have a new friendsroll request!

%s (%s) has requested to be placed on your friendsroll. You can approve their request through the friendsroll admin section, which is in the Manage section of your WordPress admin.

Thanks!
Your friendsroll WordPress plugin
"
);

define("FRIENDSROLL_ACCEPT_EMAIL",
"
Congratulations, the owner of %s has accepted your request to join their friendsroll! Check out %s and look for your name in the friendsroll sidebar widget!

Do you own a blog and want your own friendsroll sidebar widget? Check out http://friendsroll.com for all the details.

Thanks!
"
);


// Lets check for a new friend and create them if thats the case!
$friendAdded = false;
$friendMessages = array();
if (isset($_POST['fr_newfriend']))
{

	$friendMessages = widget_friendsroll_validate();

	if (sizeof($friendMessages) == 0)
	{
		$newFriend = new Friend($_POST);
		$fs = new FriendStore();
		$fs->insert($newFriend);
		$friendAdded = true;
		wp_mail(get_option('admin_email'), 'A new friendsroll request was received.', sprintf(FRIENDSROLL_ADMIN_EMAIL, trim($newFriend->_name), trim($newFriend->_email)));
	}

	if (isset($_POST['fr_ajaxsubmit'])) {
		//$messages = '';
		$messages = get_option('admin_email');
		if (sizeof($friendMessages) > 0)
		{
			$messages = implode("</li><li>", $friendMessages);
			$messages = "<ul><li>" . $messages . "</li></ul>";
			$messages = "<h3>Oops!</h3>" . $messages;
		}
		else if ($friendAdded == true)
		{
			$messages = "<h3>Success!</h3>";
			$messages .= "<p>You've been added to the list of pending friend requests.</p>";
		}
		$success = ($friendAdded) ? 'success' : 'error';
		echo '{"status": "' . $success . '",';
		echo '"message": "' . $messages . '"}';
	}
}
else if (isset($_GET['fr_getallfriends'])) {
	$fs = new FriendStore();
	$friends = $fs->findAll('accepted', 0, 0);
	ob_start();
	$i = 0;
	foreach ($friends as $friend) {
		++$i;
		$class = ($i%2) ? 'even' : 'odd';
		widget_friendsroll_displayfriend($friend, $class);
	}
	$content = ob_get_contents();
	ob_end_clean();
	echo $content;
}

if (isset($_GET['fr_getpagefriends'])) {
	$current_page = $_GET['fr_getpagefriends'];
	$start_num = $current_page * 5;
	$fs = new FriendStore();
	$links = $fs->findByPage($start_num, 5);
	ob_start();
	$i = 0;
	foreach($links as $friend)
	{
		$class = "even";
		if ($i % 2) $class = "odd";
		widget_friendsroll_displayfriend($friend, $class);
		$i++;
	}
	$content = ob_get_contents();
	ob_end_clean();
	echo $content;
}

if (isset($_GET['fr_maxpages'])) {
	$fs = new FriendStore();
	$max = $fs->findMaxPage();
	echo $max;
}


function widget_friendsroll_init() {
	// Check to see required Widget API functions are defined...
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
			return; // ...and if not, exit gracefully from the script.

	// This registers the widget. About time.
	register_sidebar_widget('friendsroll', 'widget_friendsroll');

	// This registers the (optional!) widget control form.
	//register_widget_control('friendsroll', 'widget_friendsroll_control');

	// Add the styles
	if (!is_admin()) {
		add_action('wp_head', 'widget_friendsroll_head');
		add_action('wp_print_scripts', 'widget_friendsroll_enqueue_script');
		add_action('wp_footer', 'widget_friendsroll_initjs');
	}
}

// Delays plugin execution until Dynamic Sidebar has loaded first.
add_action('plugins_loaded', 'widget_friendsroll_init');


// Add the install action so we can create our database.
register_activation_hook('friendsroll/friendsroll.php', 'widget_friendsroll_create_database');

add_action('admin_menu', 'widget_friendsroll_admin_init');

