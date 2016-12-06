=== SalsaPress ===
Contributors: mojowen
Donate link: http://busproject.org/donate
Tags: salsa, democracy in action, wired for change, events, signup
Requires at least: 3.0
Tested up to: 4.3
Stable tag: trunk

Connects WordPress to Salsa for embedding events, sign up forms, and reports.

== Description ==

SalsaPress connects your WordPress bllog to [Salsa](http://salsalabs.com), allowing you to embed sign up forms, events, and reports into your WordPress site. Once embedded, Salsa Press keeps theses embeds up-to-date with any changes made in Salsa, meaning you can embed them and continue to manage and tweak the content in Salsa afterwards.

[Please post all issues on Github](https://github.com/BusProject/SalsaPress/issues/) not WordPress.

Full features include:

* Sign Up Form Widget
* Coming Events Widget
* Event Sign Up Form Widget
* Petitions Widget
* Embeddable Reports, Sign Up Forms, Events, and Petitions
* Filtering of Salsa Data by Chapter
* Filtering of Events by Template
* Translatable - including strings from Salsa like field names


Funding for the development of this plugin is provided by [Kampaweb](http://www.kampaweb.ch/) and [Bus Federation](http://busfederation.com).

With open source development by [Lukas Zurschmiede](https://github.com/LukyLuke), [Dave Hall](https://d-g-h.co/), [Ben Byrne](http://cornershopcreative.com/), and [iCasper](http://caspar.green).

All Salsa accounts can use Salsa's API -- no activation is required. First-time users may want to go to [this page](https://salsasupport.zendesk.com/entries/23514381-Definitions-for-common-terms) in Salsa's documentation to learn how to retrieve the API hostname that Salsa Press needs. If you have questions about this document, or Salsa in general, please do contact [Salsa Support](mailto:support@salsalabs.com).


== Installation ==

1. Download source to your WordPress plugin directory /wp-content/plugins
1. Visit Administration Panels > Plugins form the Admin Console and activate the plugin
1. You should see a Salsa Icon appear in your Admin Console. Enter your Salsa Email / Password to connect Salsa to WordPress.
1. You should be able to add widgets and embed reports, events, and sign up forms. GO CRAZZY!!

Older Salsa accounts should just work - newer account may need salsa to activate their API credentials or something. They should do this for free if you contact [support@salsalabs.com](mailto:support@salsalabs.com) and request it.


== Frequently Asked Questions ==

= Do I need to Have Salsa to Use This Plugin =

Yes...

= How do I find my Base URL =

1. Log into Salsa
1. Look at your URL
1. Clear off any of the crap at the beginning (http, hq-, etc) and the anything after the .org or .com. There you have it.

= It's not working but it's telling me I'm authenticated =

Older Salsa accounts should just work - newer account may need salsa to activate their API credentials or something. They should do this for free if you contact [support@salsalabs.com](mailto:support@salsalabs.com) and request it.

When you contact Salsa support you should mention that it's pretty messed up that the /api/authenticate.sjs endpoint returns "success" for customers that aren't allowed to use the rest of the API and that an error message on that endpoint - or heck even just a normal authentication failure - would be so much better.

== Screenshots ==

1. Authenticating Salsa
2. Adding and customizing Salsa Widgets
3. Embedding Salsa to a Page or Post
4. Customizing Embedded Salsa
5. Embedded Salsa in the WordPress Editor

== Changelog ==

= 3.6 =
 * [Fix the SalsaPressVars bug](https://github.com/BusProject/SalsaPress/issues/58) thanks [Evan](https://github.com/aaemnnosttv)
 * Fix Oregon

= 3.5 =
 * Replace deprecated widget constructors, work done by [iCasper](https://github.com/BusProject/SalsaPress/pull/50).

= 3.4 =
 * [Fixes zip as required field](https://github.com/BusProject/SalsaPress/issues/46)
 * [JS Bug I introduced](https://github.com/BusProject/SalsaPress/commit/132be03bbaa15ab11d0eb676f336f208345deda6)
 * [Unclosed tag](https://github.com/BusProject/SalsaPress/commit/c247516aa34b29ebbc3adb01631d2058fd80b030)

= 3.3 =
 * [Fixing JS Errors](https://github.com/BusProject/SalsaPress/commit/001225d474e48772a26a2c634033123c98444f49)

= 3.2 =
 * [Fixing after save bug](https://github.com/BusProject/SalsaPress/commit/ef575a027f99a014f4a1f4197852a520488565de)
 * [Fixing inserting plugin in text mode bug](https://github.com/BusProject/SalsaPress/commit/2f05b8780822ce60ddfb16f9b5c1c9968e40496f)

= 3.1 =
 * Fixing mystery checkbox bug
 * Fixing TinyMCE problem

= 3.0 =
* Supports Translations
* Supports Petitions
* Supports Event Attendence Limits and Waitlists
* Supports "extra" fields for signup pages
* Supports page tags

Thanks [Kampaweb](http://www.kampaweb.ch/) for funding additional development!

= 2.2 =
* More group shenangins that aren't working
* Some fixes for Calendar
* By the way - want a calendar? [Demo here](http://busproject.org/cal)

= 2.1 =
* Passing source information to Salsa
* Works with Custom Fields OMG...
* Works with redirects
* Inconsistent event and signup_page data models for groups - set up fallback to fix bug introduced in 2.0

= 2.0 =
* Fixing group add issues

= 1.9 =
* Fixing bug displaying reports

= 1.8 =
* Major bug which prevented RSVPing to events with optional or required groups

= 1.7 =
* Bug that may have prevented automatic groups from being saved
* May have also prevented tags from being saved

= 1.6 =
* Bug that prevented groups from being shown for some accounts is now fixed

= 1.5 =
* Bug that effected the after-save effect are now fixed

= 1.4 =
* Bug that effected the title and description for sign up pages, was set to be always on
* Wasn't caching sign up and event forms, fixed
* Excerpt for coming events wasn't working

= 1.3 =
* Small bug that wouldn't correctly link events to their chaptered location
* Added precaution that a 'save' call is never cached
* Added two new methods to SalsaReport, json() which returns a json encoded object form the report and data_dump() which returns the object
* Fixed a bug that wasn't loading the stylesheet and scripts correctly
* Small style tweak
* Got Screen shots working... I hope
* Editor fix
* Updating Event Compact
* Clearing caching was working, just didn't acknowledge it when you hit the button

= 1.2 =
* Smaller bug that makes it think it's not authenticated

= 1.1 =

* Small tweaks for spelling mistakes
* Fixed bug where widgets wouldn't be activated when caching

= 1.0 =
* FIRST

== Upgrade Notice ==

= 1.0 =
* First

== GitHub Repo ==

[Fork me!](https://github.com/BusProject/SalsaPress)

== Acknowledgements ==

* Big shout out to [WP-Jalapeno](http://www.wpjalapeno.com/) by [New Signature](http://www.newsignature.com/) for the initial inspiration
* The tool also uses [Simple HTML DOM Library](http://sourceforge.net/projects/simplehtmldom/) by S.C. Chen
* Also utilizes [jQuery](http://jquery.com/)
* Developed by the nonpartisan nonprofits the [Bus Federation](http://busfederation.com) & [Bus Project](http://busproject.org).
* Further development funded by [Kampaweb](http://www.kampaweb.ch/)
