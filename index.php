<?php
/*
	Plugin Name: Sisanu Site Deployment
	Plugin URI: http://wordpress.org/extend/plugins/sisanu-site-deployment/
	Description: Allows you to export a site with all the related content and install it in another wordpress MS. Easy for deployments
	Author: Sisanu - PHP Coder - Romania
	Version: 0.2-beta
	Author URI: http://www.phpcoder.ro
	Text Domain: sisanu-site-deployment
	Domain Path: /lang
*/

/*  Copyright 2010 Sisanu (email : not available, contact me on wp forum if the case)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//---------------------------------------------------------
if (!session_id())
{
	@session_start();
}
include (dirname (__FILE__).'/_sisanu_site_deployment_receivers.php');
include (dirname (__FILE__).'/_sisanu_site_deployment_senders.php');
include (dirname (__FILE__).'/_sisanu_site_deployment.lib.php');
//---------------------------------------------------------

if(!empty($_REQUEST["location"]))
{
	$wpmuDEP=new wpmu_deployment();
	if($_REQUEST["location"]=='sender')//----------act like sender of data------------------------------
	{
		echo $wpmuDEP->send_step($_REQUEST["step"]);
		die();
	}
	elseif($_REQUEST["location"]=='receiver')//----act like receiver of data----------------------------
	{
		if(!empty($_REQUEST["sender"]) && in_array($_REQUEST["sender"],$sisanu_site_deployment_senders))
		{
			$wpmuDEP->act_like_receiver();
		}
		else
		{
			echo 'The code is not valid or it has expired. Resend your code to the webmaster of the receivers application to update it.';
		}
		die();
	}
}
else
{
	add_action('admin_menu', 'sisanu_site_deployment_menu');
	function sisanu_site_deployment_menu() 
	{
		add_menu_page('Sisanu Site Deployment Options', 'PHP Site Deployment', 'install_themes', 'sisanu-site-deployment-id', 'sisanu_site_deployment_options',get_option('siteurl').'/wp-content/plugins/sisanu-site-deployment/sisanu_site_deployment.png');
	}
	function sisanu_site_deployment_options()
	{
		if(!is_super_admin())
		{
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}

		$wpmuDEP=new wpmu_deployment();
		$wpmuDEP->plugin_action_path=get_option('siteurl').'/wp-admin/admin.php?page=sisanu-site-deployment-id';

		if(!empty($_REQUEST["reset_receiver"]))
		{
			$wpmuDEP->reset_deployment();
		}

		if(empty($wpmuDEP->selected_receiver) || !empty($_REQUEST["reset_receiver"]))
		{
			$wpmuDEP->display_deployment_selector();
			$wpmuDEP->display_navigation_path();
		}
		else
		{
			if(!empty($_REQUEST["selected_blog"]))
			{
				$wpmuDEP->blog_id=$_REQUEST["selected_blog"];
			}
			$wpmuDEP->display_deployment_page();
			$wpmuDEP->display_navigation_path();
		}

		echo '<link rel="stylesheet" type="text/css" media="screen" href="'.get_option('siteurl').'/wp-content/plugins/sisanu-site-deployment/style.css" />';
		echo '<script type="text/javascript" language="javascript" src="'.get_option('siteurl').'/wp-content/plugins/sisanu-site-deployment/loader.js"></script>';
		
		echo '
		<div class="wrap">
			<div class="sisanu_site_deployment">'
			.$wpmuDEP->navigation_path;
			echo $wpmuDEP->display_current_receiver();	
			echo $wpmuDEP->display_deployment_content;
		echo '
			</div>
		</div>';
		
	}
	//------------------------------------------------------------
}
?>