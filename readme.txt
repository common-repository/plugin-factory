=== Plugin Factory ===
Contributors: Frederic Vauchelles, Cathy Vauchelles
Donate link: http://fredpointzero.com
Tags: plugin, Wordpress, factory, maker
Requires at least: 2.8.3
Tested up to: 2.8.4
Stable tag: 0.1

Plugin Factory makes a lot easier Wordpress plugin creation.

== Description ==

Plugin Factory will generate some files for you. It can generate the basic skeleton of your plugin,
with the main class, a proper readme.txt and some other files.
It can also generate POT files for your plugins.
Finally, the configuration of your plugin generation is stored into a file and can be shared !

== Installation ==

1.  Download the plugin
1.  Copy the directory under your wp-content/plugins
1.  Enable the plugin in your admin pages

That's all !

== Frequently Asked Questions ==

= Where can I find translation files ? =
All translation files can be found in the lang subdirectory. It contains pot, po and mo files.

= Where can I find a tutorial to use Plugin Factory ? =
I am making some tutorial on my website, so checkout : http://fredpointzero in "Plugin Wordpress" page.

= Why there is so many file generated for my plugin ? =
Well, Plugin Factory has short simplified MVC library to render your plugin, so it generates a "views" directory
for views and each subdirectory correspond to a "controller". In fact, there is no controller : methods
of your plugin can render views and act as controller and action.
If you want to render the file located in "views/admin/menu.php", just call the method : render_admin_menu(), without defining it.
If you want to make some preprocessing before the rendering, define a function : public function pre_render_(controller)_(action)(){}
It will be called before the rendering

= How can I define Wordpress Options to be stored in the database ? =
Defining Wordpress Options is very simple, you have to define a public attribute in your plugin : $pluginOptions.
Do not take time to create an option page, there is already one for you ! Just use the id "options" in the admin menu tree method.
To have more details, please checkout http://fredpointzero.com/plugin-factory.

= How can I generate my admin pages ? =
This is very simple : you just have to define a method that will generate the admin menu tree.
To have more details, please checkout http://fredpointzero.com/plugin-factory.

== Features ==

= Plugin generation =

Plugin factory will generate a basic skeleton for your plugin :
*	myplugin.php
*	readme.txt
* views/
*	views/admin/
*	views/std/

You can regenerate your plugin to update fields with a new configuration file.
Please, be sured to have edited the file in [EDIT-*-BEGIN] [EDIT-*-END] sections,
otherwise, your code wil be overrided with the new generation.

= Plugin configuration =

Plugin factory let you define some fields for your plugin :
* name 							
* contributors			
* link							
* tags							
* require						
* tested						
* stable						
* shortDescription	
* description				
* FAQ							
* features					
* screenshots				
* URI								
* textDomain				
* className					
* changelog					
* authorURI					
* authorMail				
* directory

Then a configuration file is saved and you can store it for backup.

= Locale file generation =

Plugin Factory have some tools to help you to localize your plugins.
It can add the text domain in your files and generate POT files (for every plugin in your
wp-content/plugins directory under their own directory).

== Screenshots ==

== Changelog ==

= 0.1 =
* Initial widget