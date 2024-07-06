=== Emogic Tarot Reader ===
Contributors: vpelss
Tags: Tarot , Reader , Tarot Card , Tarot Reader , Card
Donate link: https://www.paypal.com/donate/?hosted_button_id=26D64Y78Q96RJ
Requires at least: 4.0
Tested up to: 6.6.0
Requires PHP: 5.6
Stable tag: 0.8.2
License: GPLv3
License URI: https://github.com/vpelss/Emogics_Tarot_Script_WP?tab=GPL-3.0-1-ov-file#readme

A Tarot Reader for casual and advanced users. Very flexible. There is no Pro version. This is the Pro version.

== Description ==

Casual users will be able to easily add a Tarot reader to their wordpress site.
We provide 2 databases, use the standard Rider-Waite deck images, and a few spreads.

Advanced users will find this is a very flexible Tarot plugin.
It was designed for those serious about making their own readings, databases, and using their own images.
You can edit everything using the wordpress editor and shortcodes.
It can easily be used for other types of readings. Rune readings for example.
You just need some Rune graphics and a Rune database.
Flexibility comes with a price and that price is a moderate learning curve.
To make your own spreads you should have a working knowledge of Wordpress Shortcodes.
Knowledge of HTML, CSS, and Javascript is very helpful.

If anyone would like to donate databases or graphics that you have created, I would consider adding it to this plugin.

Try it in the Wordpress Playground first:
https://playground.wordpress.net/?theme=twentytwentytwo&plugin=emogics-tarot-reader-for-wp&login=yes&storage=browser&networking=yes

== Installation ==
On activation:

169 images will be copied from the plugin to the "uploads/Emogic-Images" folder.
These images will be imported to the wordpress media gallery.
All the files under this plugin's "/pages" folder will be imported as wordpress pages.
The pages will be created in a quasi folder structure (parent/child) and will mirror the plugin "/pages" folder structure.
Your main tarot page will be: Emogic Tarot or ?pagename=Emogic-Tarot
You can rename this page.

Four Emogic Tarot pages will be published and may show in any menus (like a wordpress Page List) that automatically shows published pages.

Important:

If you deactivate this plugin tarot pages, databases, and images installed by this plugin will be deleted. Before disabling this script, make copies or backups if you want to save any changes you have made.


== Frequently Asked Questions ==
= Why am I not seeing images on the Wordpress edit page? =

To distribute the plugin to multiple sites this is required. When you add images from your site, you will see them in your edit page.

Note: the images where the selected cards will show will always be blank. In the edit screen a card has not been selected yet.

= How do I change your awful css choices? =

My css is in css/EmogicWPTarot.css
I use that to distribute my brilliant css choices to your wordpress installation.
To override them, delete them in the css in the file.
Then create a new Wordpress Page Template and add your CCS there.

EmogicWPTarot.css

== Changelog ==

= 0.8.2 =
Changed my css to enqueue as it should be.

= 0.8.1 =
Attempt to make playground friendly.

= 0.8.0 =
* Initial release.

== Upgrade Notice ==
= 0.8.1 =
Attempt to make playground friendly.