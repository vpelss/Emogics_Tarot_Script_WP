## Emogic Tarot Reading plugin for Wordpress

This plugin is for those who are serious about making their own spreads, reading text, and use their own images.
We provide 2 databases, use the standard Rider-Waite deck images, and a few spreads.

You can create your own spreads and databases.
Use any images.
It can easily be used for other readings.
Rune readings? You just need some graphics and a database.
If anyone wants to donate a database or graphics you created, to this project I will consider adding it to the plugin.

## Benefits

See the future.

## Try

Currently running at https://tarot.emogic.com/

## Install

Place this directory under your Wordpress wp-content\plugins folder

Activate it

Your main tarot page will be: Emogic Tarot
?pagename=Emogic-Tarot
You can rename the page.

Important: If you uninstall the plugin your tarot pages and databases will be deleted.
If you edited the original plugin files, make copies or backup if you want to save any changes you have made.
There are many good plugins to backup your site

## Themes

This has been tested with the 'Twenty' themes.
I use Twenty Twenty-Three
It should work with any theme that can use shortcodes.

If it does not work with your sites current theme, you can install a second Wordpress installation on your server in a sub folder (eg: 'Tarot') and install the plugin there with a theme that will work.

## Deck Databases

Deck databases are in a text flat file format. 
Deck databases are stored in Wordpress pages.
Deck databases are set to 'draft' (not published), and should be kept that way, to keep your cherished database private.
Deck databases are stored as sub pages under the empty 'decks' page
Deck databases can be easily modified our updated in the Wordpress page editor, or just copy and paste in and update.

The format for the deck databases is:

- itemnumber|itemname|itemimage|itemblurb
- 1|Ace of Cups|/images/Rider_Waite/normal/cups01.jpg|The start of love, joy and fruitfulness. Spirituality aids the material world.
- 1|Ace of Cups|/images/Rider_Waite/reversed/cups01.jpg|The start of love, joy and fruitfulness. Spirituality aids the material world.
- 2|Two of Cups|/images/Rider_Waite/normal/cups02.jpg|This card signifies balance and give and take [first_name]. You may be entering a friendship with the opposite sex.
- 2|Two of Cups|/images/Rider_Waite/reversed/cups02.jpg|This card signifies balance and give and take [first_name]. You may be entering a friendship with the opposite sex.
- 3|Three of Cups|/images/Rider_Waite/normal/cups03.jpg|Achievement and abundance are headed your way [first_name].
- 3|Three of Cups|/images/Rider_Waite/reversed/cups03.jpg|Achievement and abundance are headed your way [first_name].

The fist line of the database must be the Column Names.

There is one record per line.

Each column of the record is separated by the delimiter character '|'.

You must ensure that you have the same number of columns as you do on the first line with the Column Names.

All column names can be changed EXCEPT for the fist one, the itemnumber. If you change the name, the script will break. Note that if you chane the colummn names you will need to change the shortcodes on your spreads pages.

You may add as many columns as you like but all records must have the same number of columns.

You can have multiple alternate records for the same card.
Just make a duplicate card record, modify it, and give it the same itemnumber.
The script randomly shuffles the order of the database records.
If you have seven records with the same itemnumber, the script will chose one by random to use in the in the shuffled database.
I use it so you can have upright, or upside down cards and matching reading text.

Note: You can add your own database fields/columns. These new fields can easily be displayed on your custom created spreads. See Shortcodes. Our script is designed for you to do that!

You may also note that [first_name] placed in the database will be replaced by the First Name given field if filled out by the visitor.

## Spreads

They are stored in Wordpress pages and displayed as such.
They are stored as sub pages under the empty 'spreads' page.
This is where you can modify, or create your own spreads.

To modify or create spreads, you should be comfortable to create and edit pages in Wordpress. A little css, html and JS knowledge will go a long way if you want to get creative.

Before a spread page is displayed, our script reads the chosen database, shuffles it's order. Then the script can display the card name, image, blurb by using Wordpress shortcodes.

## Shortcodes

Spread shortcodes:

[ETSWP item='1' column='itemname'] This shortcode will return the itemname column data (column='itemname') from the first item (item=1) found in the shuffled database

To display the 5th shuffled card itemimage column data use the following: [ETSWP item='5' column='itemimage']

[ETSWP_get_input name='field name'] Returns the field data used in the form from the main page. 'field name' can be: 'ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question'

Main page shortcodes:

[ETSWP_deck_options] Returns the html options for all the deck sub pages found under your 'decks' page

[ETSWP_spread_options] Returns the html options for all the spread sub pages found under your 'spreads' page

[ETSWP_get_cookie name='cookie name'] Returns specific cookie data. This is used to retrieve and set (via JS) the previous tarot reading settings. 'cookie_name' can be: 'ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question';

Other:

[ETSWP_pluginpath] Returns the URL path to this plugin. eg: https://mysite.com/tarot/wp-content/plugins/Emogics_Tarot_Script_WP. I use this shortcode so I do not need to have the full URL path of my images in the deck databases.

## Support

Support is limited to bugs as time permits.
This is a free product.
We will not be teaching Wordpress, html, css, etc...

## Liability

This program is subject to change and no assumption of reliability can be assumed.

## Not for resale

Not for resale. Do not charge for this.

## To Do

Everything.

