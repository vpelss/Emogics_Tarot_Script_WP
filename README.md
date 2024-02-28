## Emogic Tarot Reading plugin for Wordpress

You can edit EVERYTHING. This is the most flexible Tarot Script. Flexibility comes with a price and that price is a moderate learning curve.

Wordpress has some very amazing page editing tools.
With Wordpress shortcodes, and this script you can easily create new spread layouts in minutes.

This plugin is for those who are serious about making their own spreads, reading text, and use their own images.
We provide 2 databases, use the standard public domain Rider-Waite deck images, and a few spreads.

You can create your own spreads and databases.
Use any images.
It can easily be used for other readings.
Rune readings? You just need some rune graphics and a database.
If anyone wants to donate a database or graphics you created, to this project I will consider adding it to the plugin.

To make your own spreads you must have a working knowledge of Wordpress Shortcodes. Knowledge of HTML, CSS, and Javascript is very helpful.

## Benefits

See the future

Create your own flat file database by creating and editing a Wordpress page

Create your own Readings / Spreads by creating and editing a Wordpress page

Use your own images or multiple images

Reverse cards and readings

Multiple card text readings for same card possible

Use cookies to keep readings the same for a day, or another time period

Email readings possible

## Try

Currently running at https://tarot.emogic.com/

## Install

Place this directory under your Wordpress wp-content\plugins folder
Suggested plugin folder name "Emogic-Tarot-Reader-Plugin-for-Wordpress"
eg: wp-content\plugins\Emogic-Tarot-Reader-Plugin-for-Wordpress

Activate it

Your main tarot page will be: Emogic Tarot
?pagename=Emogic-Tarot
You can rename this page.

On activation:

The images will be imported to the media galery.

Pages will be imported and created from the plugin's "/pages" folder.
The pages will be created in a quazi folder structure (parent/child) which mirrors the plugin's "/pages" folder structure.
Pages with no content are considered 'folders'.
To put pages with content into these 'folders', just make the 'folders' the page's parent.

Important: If you deactivate or uninstall the plugin your the tarot pages, databases and images the plugin installed will be deleted.
If you edited the original plugin files, make copies or backup them if you want to save any changes you have made.

## Themes

This has been tested with the 'Twenty' themes.
I use Twenty Twenty-Three
It should work with any theme that can use shortcodes.

If it does not work with your sites current theme, you can install a second Wordpress installation on your server in a sub folder (eg: 'Tarot') and install the plugin there with a theme that will work.

## Main Calling Form

The HTML form that calls the reading must have certain 'input' and 'select' tags. The tag id must remain the same.

<input type="text" name="ETSWP_first_name" id="ETSWP_first_name" placeholder="First Name">

\<select name="ETSWP_deck" id="ETSWP_deck">
[ETSWP_deck_options]
\</select>

\<select name="ETSWP_spread" id="ETSWP_spread">
[ETSWP_spread_options]
\</select>

\<input type="text" size="40" name="ETSWP_question" id="ETSWP_question" placeholder="Your Question">

[ETSWP_get_cookie name='ETSWP_first_name']

[ETSWP_get_cookie name='ETSWP_deck']

[ETSWP_get_cookie name='ETSWP_spread']

[ETSWP_get_cookie name='ETSWP_question']

## Deck Databases

Deck databases are stored in Wordpress pages.
Deck database pages are stored in Wordpress with the parent page set to the 'emogic_databases' page. 
You can modify, or create your own Deck databases
Deck databases are in a text flat file format. 
Deck databases are set to 'draft' (not published), and should be kept that way to keep your database private.
Deck databases can be easily modified our updated in the Wordpress page editor, or just copy and paste then update.
To create a new database:
1: Set the 'emogic-databases' page to published (so you can use it as a parent page).
2: Create a new page for the database and set it's parent page to 'emogic-databases' and set it to draft, not publish. This hides it from the public.
3: Type or paste your flat file database into the page and save it.
4: Set the 'emogic-databases' page to draft to hide it again. IMPORTANT: If you skip this step, your database is visible
5: IMPORTANT: Ensure all your database files are set to draft. If you skip this step, your database is visible
6: Note that you can have multiple 'emogic-databases' page sub folders. eg: /emogic-databases/runes/(pages/db)  and /emogic-databases/tarot/(pages/db)

The format for the deck databases is:

- itemnumber|itemname|itemimage|itemblurb
- 1|Ace of Cups|/images/Rider_Waite/normal/cups01.jpg|The start of love, joy and fruitfulness. Spirituality aids the material world.
- 1|Ace of Cups|/images/Rider_Waite/reversed/cups01.jpg|The start of love, joy and fruitfulness. Spirituality aids the material world.
- 2|Two of Cups|/images/Rider_Waite/normal/cups02.jpg|This card signifies balance and give and take [first_name]. You may be entering a friendship with the opposite sex.
- 2|Two of Cups|/images/Rider_Waite/reversed/cups02.jpg|This card signifies balance and give and take [first_name]. You may be entering a friendship with the opposite sex.
- 3|Three of Cups|/images/Rider_Waite/normal/cups03.jpg|Achievement and abundance are headed your way [first_name].
- 3|Three of Cups|/images/Rider_Waite/reversed/cups03.jpg|Achievement and abundance are headed your way [first_name].

The first line of the database must be the Column Names.

There is one record per line.

Each column of the record is separated by the delimiter character '|'. The pipe character, usually above the '\'

You must ensure that you have the same number of columns as you do on the first line with the Column Names.

All column names can be changed EXCEPT for the fist one, the itemnumber. If you change the name, the script will break. Note that if you chane the colummn names you will need to change the shortcodes on your spreads pages.

You may add as many columns as you like but all records must have the same number of columns.

You can have multiple alternate records for the same card.
Just make a duplicate card record, modify it, and give it the same itemnumber.
The script randomly shuffles the order of the database records.
If you have seven records with the same itemnumber, the script will chose one by random to use in the in the shuffled database.
I use it so you can have upright, or upside down cards and matching reading text.

Note: You can add your own database columns. These new columns can easily be displayed on your custom created spreads. See Shortcodes. Our script is designed for you to do that!

You may also note that [first_name] placed in the database will be replaced by the First Name input if filled out by the visitor.

## Spreads

Spreads are stored in Wordpress pages and displayed as such.
Spreads are stored as sub pages under the empty 'spreads' page.
This is where you can modify, or create your own spreads.
To modify or create spreads, you should be comfortable to create and edit pages in Wordpress. A little css, html and JS knowledge will go a long way if you want to get creative.
To create a new spread:
1: Set the 'spreads' page to published (so you can use it as a parent page).
2: Create a new page for the spread and set it's parent page to 'emogic-readings' and set it to published.
3: Edit your spread using shortcodes below.
4: Set the 'emogic-readings' page to draft to hide it again.
5: Note that you can have multiple 'emogic-readings' page sub folders. eg: /emogic-readings/runes/(pages/db)  and /emogic-readings/tarot/(pages/db)

Before a spread page is displayed, our script reads the chosen database, shuffles it's order. Then the script can display the card name, image, blurb by using Wordpress shortcodes.

## Shortcodes

Spread shortcodes:

[ETSWP_get_db_item item='1' column='itemname'] This shortcode will return the itemname column data (column='itemname') from the first item (item=1) found in the shuffled database

To display the 5th shuffled card itemimage column data use the following: [ETSWP_get_db_item item='5' column='itemimage']

[ETSWP_get_input name='column name'] Returns the input data used in the form from the main page. 'column name' can be: 'ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question'

Main page shortcodes:

[ETSWP_deck_options] Returns the html options for all the deck sub pages found under your 'decks' page

[ETSWP_spread_options] Returns the html options for all the spread sub pages found under your 'spreads' page

[ETSWP_get_cookie name='cookie name'] Returns specific cookie data. This is used to retrieve and set (via JS) the previous tarot reading settings. 'cookie_name' can be: 'ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question';

Other:

[ETSWP_pluginpath] Returns the URL path to this plugin. eg: https://mysite.com/tarot/wp-content/plugins/Emogics_Tarot_Script_WP. I use this shortcode so I do not need to have the full URL path of my images in the deck databases.

## Advanced DB and Spreads

Believe it or not, you can also add your own columns in the databases and use those columns in your spreads.

eg:

DB:

- itemnumber|itemname|itemimage|itemblurb|mynewcolumn
- 1|Ace of Cups|/images/Rider_Waite/normal/cups01.jpg|The start of love, joy and fruitfulness. Spirituality aids the material world.|new column stuff

Spread shortcode:

[ETSWP_get_input item='3' name='mynewcolumn']


## Cookies
After the main form calls a spread, the following cookies are set:

ETSWP_first_name

ETSWP_deck

ETSWP_spread

ETSWP_question

various cookies that look like "e276a1c4" :
These are stored shuffles for a combination of ETSWP_first_name, ETSWP_deck, ETSWP_spread, ETSWP_question.
The life of these cookies can be set by the following hidden input on the main calling form.
You can choose your own time in hours, or set it to zero to disable it.
eg:
<input type="hidden" name="ETSWP_deck_life_in_hours" value="24">

## Email Readings

To make yor reading template send by email, it must contain:
<!-- just_say_yes_to_email -->

To stop the reading from being shown on the website, and ONLY send it in email, you must also add the following to your reading page.
<!-- redirect_to_email_has_been_sent -->

Every email sent will have a wrapper page applied to it. See the 'Emogic Reading Email Template' page.
It can contain the following shortcodes:

[ETSWP_link_to_reading] : this shortcode link will direct back to the website and show the reading sent in the email.

[ETSWP_spread] : this shortcode will show the reading in the email. If you are having difficulty formatting decent email readings, just use [ETSWP_link_to_reading] and do not use [ETSWP_spread]

Sending email readings is fraught with formatting challenges.
The easiest solution is just don't allow them.
However if you insist:

All css in email should be 'inline css'.
Wordpress formatting will not help  much with email formatting as it does not use inline css.
Be creative.
Keep it simple is a good motto.
Tables are a good solution for spreads in emails.
Javascript: No, no, no.

Anchors 'may' or 'may not' work in email.
Gmail requires that the anchor be:

\<a href="#PastLink">Link\</a>

....

\<a name="PastLink">\</a> 

This plugin comes with an admin setting to change the From email address and name.

To avoid your sent emails ending up in spam folders set up your from email address with appropriate DKIM and DMARC settings for your internet provider.
This is outside the scope of this plugin.

## What happens in a typical reading

A visitor will typically start at your main form page.
If they have visited before, their browser session may have cookies that can be used to pre fill in some of the form data using Javascript.
The main page will typically have user fillable inputs that ask for:
First Name
Deck database
Spread
Their question or concern for this reading

There may also be cookies containing the deck shuffles for previous readings.
Typically I set the life of these deck shuffle cookies to 24 hours.
So if they enter the same name, deck, spread, and question, the reading will be the same for the next 24 hours.

Some Wordpress shortcodes are used on this page to set the decks and spreads available.

When they click on "Get Reading", their forms are sent to the spread page they chose.

When the spread page is displayed, the deck database is read and then shuffled. If there was an cookie for a deck shuffle that matches this reading, then that deck shuffle will be used.
Card images, text, db columns, etc are displayed using shortcodes.

## FAQs

Why am I not seeing your images on the Wordpress edit page?

To distribute the plugin to multiple sites this is required. When you add images from your site, you will see them in your edit page.

Note: the images where the selected cards will show will always be blank. In the edit screen a card has not been selected yet.

## Support

Support is limited to bugs as time permits.
This is a free product.
We will not be teaching Wordpress, html, css, etc...

## Liability

This program is subject to change and no assumption of reliability can be assumed.

## Not for resale

Not for resale. Do not charge for this.

## To Do

-Everything

-export and import spreads and decks -backup/restore spreads? -backup/restore decks

-allow email input or insist we have an account : account / input : enable / disable? account avoids spam

-make a post welcome message...

-more spreads
-Numbers Speak reading
-click to show popup card reading 

