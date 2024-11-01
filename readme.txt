=== Sisanu Site Deployment ===
Contributors: Sisanu 
Tags: admin,tools,site,deployment,upload
Requires at least: wpmu 3.0.1
Tested up to: wpmu 3.0.1
Stable tag:  trunk
Donate link: http://www.phpcoder.ro

This plugin adds an option page "PHP Site Deployment" under your admin menu. The plugin gives you the possibility to copy the content and all related to a specific site created in the WPMS and deploy all that on another application that uses also Wordpress MS.
== Description ==
If you use Wordpress MS, the plugin gives you the possibility to copy the content and all related to a specific site created in the WPMS and deploy all that on another application that uses also Wordpress MS. This plugin is for site admins (super administrators) of WPMS sites.

The purpose of this plug-in is to allow you to make changes in a "staging" environment and once you decide the site is ready to be published you can choose to deploy it to a "live" environment. Please note that this plugin has to be installed and activated on both application in order to have the deployment functionality.

Bug report, please go here: <br /><br />
http://www.phpcoder.ro/contact.php

== Installation ==
1. Upload `sisanu-site-deployment` to the `/wp-content/plugins/` directory of your application<br />
2. Login as Superadmin<br />
3. Activate the plugin through the 'Plugins' menu in WordPress<br />


How it works:
Just create a site and add content in it. When done, you will be able to copy that content in another WPMS application.

1. Install the plug-in in the sender application (SA)<br />
1. Install the plug-in in the receiver application (RA)<br />
2. Login as Superadmin in SA<br />
3. In SA edit the _sisanu_site_deployment_receivers.php file from the plug-in and add there the receivers urls = the url to RA<br />
4. In RA edit the _sisanu_site_deployment_senders.php file from the plug-in and add there the code of SA = the code of the application is shown in the right hand side in the administration section when you go to Deployment page.<br />
5. You are ready for deployment.

That's it! Have fun.<br>

== Screenshots ==
1. Select the site you want to deploy
1. First step of deployment

== License ==

**********************************************************************
This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
**********************************************************************

==  Version history  ==
0.1-alfa First testing alpha version.<br />
0.2-beta Beta version.

== Changelog == 
None

== Upgrade Notice ==
None

== Frequently Asked Questions ==
None