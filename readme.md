# Multisite Plugin CSV #

Contributors: ryanduff  
Tags: multisite, admin, multisite, plugin, management, network  
Requires at least: 3.8  
Tested up to: 3.8.1  
Stable tag: 1.0.0  
License: GPLv2 or later  

Generate a CSV list of all plugins and their activation status on a multisite network

## Description ##

Managing plugins on Multisite is hard. It's even harder when trying to figure out where and how a plugin is active on a large multisite network. This plugin generates a handy spreadsheet (CSV) containing all that information.

Across the top is a column for every plugin available on the network. It shows both the plugin name (as specified in the plugin file), as well as the plugin file path so that you can track it down.

Below that is a row for every site in your multisite network. This will show you if the plugin is activated manually (on a specific site), if it's network activated, or if it's not activated at all. 

## Installation ##

1. Network Activate plugin and visit Network Admin > Plugins > Multisite Plugin CSV
2. Click the `Generate Plugin Report` button to generate the report and save the file.

## Frequently Asked Questions ##

### Why does a column show both "Yes" and "Network Active"? ###

This means that the plugin is manually active on the site as well as the plugin being Network Activated. This usually happens if you began activating the plugin on sites then later set it to network activated. 

If you network deactivate the plugin, the sites marked as `Network Active` will change to `No` and the sites listed as `Yes` will remain as such. On those sites you'll have to visit them and manually deactivate the plugin. 

## Screenshots ##

1. Multisite Plugin CSV admin screen
2. Sample CSV output

## Changelog ##


### 1.0.0 ###
* Initial Release