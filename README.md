Emocic Tarot Script for Wordpress

## Benefits

See the future

##Install

place this directory under your Wordpress wp-content\plugins folder

activate it

Your main tarot page is: Emogic Tarot
?pagename=Emogic-Tarot

##Deck Databases

Deck databases are text flat file format.
They are stored in Wordpress pages. They are set to 'draft' (not published), and should be kept that way, to keep your cherished database private.
They are stored as sub pages under the empty 'decks' page
The can be easily modified our updated in the Wordpress page editor, or copy and paste in and update.

The format is thus:

itemnumber|itemname|itemimage|itemblurb
1|Ace of Cups|/images/Rider_Waite/normal/cups01.jpg|The start of love, joy and fruitfulness. Spirituality aids the material world.
1|Ace of Cups|/images/Rider_Waite/reversed/cups01.jpg|The start of love, joy and fruitfulness. Spirituality aids the material world.
2|Two of Cups|/images/Rider_Waite/normal/cups02.jpg|This card signifies balance and give and take [custom1]. You may be entering a friendship with the opposite sex.
2|Two of Cups|/images/Rider_Waite/reversed/cups02.jpg|This card signifies balance and give and take [first_name]. You may be entering a friendship with the opposite sex.
3|Three of Cups|/images/Rider_Waite/normal/cups03.jpg|Achievement and abundance are headed your way [first_name].
3|Three of Cups|/images/Rider_Waite/reversed/cups03.jpg|Achievement and abundance are headed your way [first_name].

You can have multiple alternate options for the same card. Just make a duplicate card line, and give it the same itemnumber.
The script randomly shuffles the order of the database.
If you have seven lines with the same itemnumber, the script will only use one, randomly chosen, in the shuffled database.
I use it so you can have upright, or upside down cards and matching reading text.

Note: You can add your own database fields/columns.
These new fields can easily be displayed on your custom created spreads.
Our script is designed for you to do that!

##Spreads

They are stored in Wordpress pages and displayed as such.
They are stored as sub pages under the empty 'spreads' page
This is where you can modify, or create your own spreads.

You should be comfortable to create and edit pages in Wordpress.

Before a spread page is displayed, our script reads the chosen database, shuffles it's order.
Then the script can display the card name, image, blurb by using Wordpress shortcodes.

##Shortcodes

Spread shortcodes:

[ETSWP item='1' column='itemname']
This shortcode will return the itemname column data (column='itemname') from the first item (item=1) found in the shuffled database

cookie [ETSWP_get_cookie name='cookie name'] ['ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question'];

input [ETSWP_get_input name='cookie name'] ['ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question'];

To display the 5th shuffled card itemimage column data use the following:
[ETSWP item='5' column='itemimage']

Main page shortcodes:

[ETSWP_deck_options] returns the html options for all the deck sub pages found under your 'decks' page
[ETSWP_spread_options] returns the html options for all the spread sub pages found under your 'spreads' page

Other:

[ETSWP_pluginpath] This returns the URL path to this plugin. eg: https://mysite.com/tarot/wp-content/plugins/Emogics_Tarot_Script_WP.
I use this shortcode so I do not need to have the full URL path of my images in the deck databases.



## Liabilty

This program is subject to change and no assumption of reliability can be assumed.


## To Do
