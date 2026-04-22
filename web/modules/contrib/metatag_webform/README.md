# Metatag Webform

This module provides the ability to add metatags for webforms.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/metatag_webform).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/metatag_webform).


## Table of contents

- Requirements
- Installation
- Configuration
- Maintainers


## Requirements

This module requires the following modules:

- [Metatag](https://www.drupal.org/project/metatag)
- [Webform](https://www.drupal.org/project/webform)


## Installation

The installation of this module is like other Drupal modules.

- This module can to be installed [via Composer](https://www.drupal.org/docs/extending-drupal/installing-modules-composer-dependencies)
  or manually. `composer require "drupal/metatag_webform"`

- Enable the `Metatag Webform` module in `Extend`.
  (`/admin/modules`)


## Configuration

1. Navigate to `/admin/structure/webform` and click `Build` on the webform you want add metatags for.
2. Go to `"Settings"` primary tab, then `"Metatags"` secondary tab
3. Add metatags for the webform and click `"Save"`
4. Rebuild site's caches


## MAINTAINERS

Current maintainers for Drupal 9/10:

- Alexander Kovrigin - [a.kovrigin](https://www.drupal.org/u/akovrigin)
