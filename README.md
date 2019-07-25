# CommitReact MantisBT Plugin

[![app-type](https://img.shields.io/badge/category-mantisbt%20plugins-blue.svg)](https://github.com/spmeesseman)
[![app-lang](https://img.shields.io/badge/language-php-blue.svg)](https://github.com/spmeesseman)
[![app-publisher](https://img.shields.io/badge/%20%20%F0%9F%93%A6%F0%9F%9A%80-app--publisher-e10000.svg)](https://github.com/spmeesseman/app-publisher)
[![semantic-release](https://img.shields.io/badge/%20%20%F0%9F%93%A6%F0%9F%9A%80-semantic--release-e10079.svg)](https://github.com/semantic-release/semantic-release)

[![authors](https://img.shields.io/badge/authors-scott%20meesseman-6F02B5.svg?logo=visual%20studio%20code)](https://github.com/spmeesseman)
[![GitHub issues open](https://img.shields.io/github/issues-raw/spmeesseman/mantisbt%2dplugins.svg?maxAge=2592000&logo=github)](https://github.com/spmeesseman/mantisbt-plugins/issues)
[![GitHub issues closed](https://img.shields.io/github/issues-closed-raw/spmeesseman/mantisbt%2dplugins.svg?maxAge=2592000&logo=github)](https://github.com/spmeesseman/mantisbt-plugins/issues)

- [CommitReact MantisBT Plugin](#CommitReact-MantisBT-Plugin)
  - [Description](#Description)
  - [Installation](#Installation)
  - [Usage](#Usage)
  - [Future Maybes](#Future-Maybes)

## Description

This plugin allows for automatic update of the "fixed in version" of a bug when a commit sets the bug status to "fixed".

Note that the [Source](https://github.com/mantisbt-plugins/source-integration) plugin is required, and does its own version handling by branch mapping.  By default, this branch mapping is disabled.  This plugin will set the "fixed in version" to the lowest version number having a "release date", not yet marked "released".  If you desire the version to be set according to branch mapping, configure Source plugin for this.

For example, consider the following project version set:

|Version|Released State|Release Date|
|-|-|-|
|1.2.0|Released|Set|
|1.2.1|Released|Set|
|1.2.2|Not Released|Set|
|1.3.0|Not Released|Set|
|1.4.0|Not Released|Set|

The version number used to set "fixed in version" in this case will be 1.2.2.

## Installation

Install the plugin using the default installation procedure for a MantisBT plugin.

## Usage

Coming soon, under development.

## Future Maybes

- Support for tag manipulation on commit fix
