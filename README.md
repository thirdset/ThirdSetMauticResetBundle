# ThirdSetMauticResetBundle

## DEPRICATION NOTICE

The ability for contacts to go through a campaign multiple times was added to
Mautic in [v2.14](https://github.com/mautic/mautic/releases/tag/2.14.0) (see
 #6132 in the [v2.14 release notes](https://github.com/mautic/mautic/releases/tag/2.14.0)).
Mautic 2.14 was released on 2018-07-25. If you are using Mautic 2.14 or greater,
you are advised to use Mautic's built in functionality instead of this plugin.

If you are using a version of Mautic older than 2.14, this plugin may still be
of value but all users are encouraged to upgrade.

Once you have upgraded to Mautic 2.14, this plugin can safely be deleted.

--------------------------------------------------------------------------------

## [Description](id:description)
The ThirdSetMauticResetBundle allows you to process a Mautic Contact through a Campaign multiple times.  See the [Usage](#usage) section below for how to use it.

## Compatibility
This plugin has been tested with up to v2.12.0 of Mautic.

## [Installation](id:installation)
1. Download or clone this bundle into your Mautic /plugins folder.
2. Manually delete your cache (app/cache/prod).
3. In the Mautic GUI, go to the gear and then to Plugins.
4. Click the down arrow in the top right and select "Install/Upgrade Plugins"
5. You should now see the Reset plugin in your list of plugins.

## [Usage](id:usage)

### Supported Tags
The plugin recognizes tags that are of a specific format.  You can then run commands against Contacts with recognized tags.

The plugin finds Contacts that are tagged with tags in the following fomat:

"reset_"<campaign_id>

Example:

"reset_12"

### Tag Your Contacts

Here's how to tag your contacts via a Campaign Action:

* In the Mautic Campaign Builder create a "Modify Contact's Tags" Action. 
* Add a tag such as "reset_12" where "12" is the id of the campaign.  You can get the id of the campaign from the url visible in your browser's address bar when editing the campaign (ex: "http://www.example.com/mautic/s/campaigns/edit/**12**")

### Run the process_resets Command

The plugin adds a command named `mautic:campaigns:process_resets`. 

You can run this command from the command line or via a scheduled cron job.

The process_resets command does the following:

* Finds all leads with a reset tag (see above for examples).
  * Gets the campaign id out of the tag
  * Clears the campaign_lead_event_log history for the lead/campaign so that the lead can go through the campaign again.

## More Features

### Process Resets for a Specific Campaign

You can also run the `process_resets` command for a specific campaign (by
default, the command will process resets for all campaigns). Just specify the
`--campaign-id` like so:

```
mautic:campaigns:process_resets --campaign-id 12
```

## Why Use This Plugin

This plugin works around a limitation with older versions of Mautic (< 2.14) 
that keeps you from being able to process a Contact through a Campaign more than
once.

This type of processing is often needed for campaigns related to orders, 
billing, etc.

With the release of [Mautic v2.14](https://github.com/mautic/mautic/releases/tag/2.14.0),
this plugin is now obsolete.
