# TwitterRSS 
## A Wordpress Plugin

This plugin is designed to offer a relatively painfree hack to cover any broken  dependencies arising from the Twitter's discontinuation of RSS support. In our case, we have digital signage which runs news tickers whose content is pulled in from an RSS feed of a named Twitter account. The firmware on the signage can't cope with JSON data, so we created a proxy which translates JSON data from Twitter's APv1.1 to the old RSS format.

Why a Wordpress plugin? Well, there's many ways you could do this, and it just so happened that the quickest and easiest solution was to use a WP plugin rather than set up a standalone script. Plus, our environment has a lot of Wordpress sites that use social media widgets including RSS feeds, so the fit seemed good. You could just pull out the twitterrss.php and rssWriter.php files and use them cold - the only WP-specifc code used is the specification of the plugin_dir_path() function to include the necessary scripts, so you can just edit that to your own local settings.

## Installation

Just unpack the plugin into your plugins directory. The script will try to set itself up on first use, so all you need to do is ensure that the twitterrss directory is writable; the first thing the plugin will do is try to create a folder in which to cache feeds it has processed.

To use the plugin, decide on a url (i.e. a Wordpress page) you'd like to use as the proxy for your RSS feed. Create a template for that page, and add the following code to it:

<?php $feed = new TwitterRSS($_GET); ?>

Then when you visit that page in your browser, in order to get an RSS feed for the Twitter account you want, just add ?screen_name=foo where foo is the Twitter handle you want to retrieve.

Some options you can add to your url query string:

* cacheExpiry     - no of mins to wait between cache refresh
* forceRefresh    - ignore cached version if true
* cacheFolder     - replaces default value "./cache"
* verbose         - determines whether error messages are echoed
* screen_name     - specifies the Twitter account RSS feed to be cached / returned

## Dependencies

We assume php-xml is installed.

## Credits

We use the excellent twitteroauth library: https://github.com/abraham/twitteroauth