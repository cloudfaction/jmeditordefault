# JM Editor Default Plugin

## Overview
JM Editor Default is a Joomla system plugin that provides a convenient way to switch between different editors while editing content in Joomla articles and modules. It adds a dropdown selector in the toolbar, allowing users to change their default editor on the fly without navigating to their user profile settings.

## Features
- Quick editor switching from the toolbar
- Works in both Article and Module editing views
- Preserves content when switching editors
- Supports all installed and enabled Joomla editors
- User-specific editor preferences are saved automatically

## Installation
1. Install the plugin through Joomla's Extension Manager
2. Enable the plugin from System → Plugins
3. Configure the plugin settings if needed

## Configuration Options
The plugin has a simple configuration with one option:
- **Show Editor Switch**: Enable or disable the editor switch button in the toolbar (Default: Enabled)

## Usage
1. Open any article or module for editing
2. Look for the editor dropdown selector in the toolbar
3. Select your preferred editor from the dropdown
4. The page will reload with your newly selected editor

## Technical Details
- The plugin hooks into Joomla's `onAfterDispatch` event
- Editor preferences are saved per user
- Content is temporarily stored in the session during editor switches
- Compatible with Joomla 4.x

## Requirements
- Joomla 4.x or Joomla 5.x
- At least one editor plugin installed and enabled
- User permissions to edit content

## Support
For support and questions, please contact:
- Email: hello@jmodules.com
- Website: www.jmodules.com

## License
GNU General Public License version 2 or later

## Credits
Created by Maarten Blokdijk
Copyright (C) 2024 Maarten Blokdijk. All rights reserved.
