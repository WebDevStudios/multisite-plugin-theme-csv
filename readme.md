# Multisite Plugin and Theme CSV #

Contributors: ryanduff  
Tags: multisite, admin, multisite, plugin, theme, management, network  
Requires at least: 3.8  
Tested up to: 3.8.1  
Stable tag: 1.1.0  
License: GPLv2 or later  

Generate a CSV list of all plugins or themes on a multisite network and their activation status

## Description ##

Managing plugins and themes on Multisite is hard. It's even harder when trying to figure out where and how a plugin or theme is active on a large multisite network. This plugin generates a handy spreadsheet (CSV) containing all that information.

Across the top is a column for every plugin or theme available on the network. For plugins, it shows both the plugin name (as specified in the plugin file), as well as the plugin file path so that you can track it down. For themes it shows the Theme name (as specified in the style.css header), as well as the theme folder name.

Below that is a row for every site in your multisite network. For plugins this will show you if the plugin is activated manually (on a specific site), if it's network activated, or if it's not activated at all. For themes this will tell you if the theme is active. It will also give you availability status for a theme on a given site-- either network active or manually available for the site. 

## Installation ##

1. Network Activate plugin and visit Network Admin > Sites > Multisite Plugin and Theme CSV
2. Click the `Generate Plugin Report` button to generate a report for plugins.
3. Or click the `Generate Theme Report` button to generate a report for themes.

## Frequently Asked Questions ##

### Why does a column show both "Yes" and "Network Active"? ###

This means that the plugin is manually active on the site as well as the plugin being Network Activated. This usually happens if you began activating the plugin on sites then later set it to network activated. 

If you network deactivate the plugin, the sites marked as `Network Active` will change to `No` and the sites listed as `Yes` will remain as such. On those sites you'll have to visit them and manually deactivate the plugin. 

## Screenshots ##

1. Multisite Plugin and Theme CSV admin screen
2. Sample CSV output for plugins
3. Sample CSV output for themes

## Changelog ##

### 1.1.0 ###
* Added theme functionality
* Minor tweaking and refactoring of existing code

### 1.0.0 ###
* Initial Release