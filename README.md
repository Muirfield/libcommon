<!-- template: startup.md -->
<!-- end-include -->
<!-- template: header.md -->
<!-- end-include -->
<!-- meta: API = http://muirfield.github.io/libcommon/apidocs/index.html -->
<!-- php:$copyright="2016"; -->
## Overview

`libcommon` implements functionality that can be used in other plugins,
like for example, ScriptPlugins.

API Features:

<!-- snippet: api-features -->
<!-- end-include -->

See [API](https://muirfield.github.io/libcommon/apidocs/index.html)
documentation for full details.

The `libcommon` library is my standard library that I personally use when
developing PocketMine-MP plugins.  It can work equally well embeddeed within a
a plugin `.phar` file or as a stand-alone plugin.

## Code Generators and dev tools

- [x] mkplugin
- [ ] (WIP) gd3tool
- [ ] version.php
  - it TRAVIS_TAG get plugin.yml version, and error if different
  - update version.php
- [ ] embed common
- [ ] update msg catalogues

## Completed

- [x] PluginCallbackTask

## WIP

- [ ] Cmd
- [ ] mcbase
- [ ] mcc
- [ ] BasicPlugin
- [ ] ExpandVars
- [ ] PMScript
- [ ] Singleton
- [ ] Plugin loader
- [ ] MPMU
- [ ] access/permissions/inGame | addPerm
- [ ] Ver

## Backlog

- [ ] Testing Harness
- [ ] Modular
- [ ] FileUtils
- [ ] Armoritems
- [ ] Command/Sub-commands
- [ ] Paginated output
- [ ] Help for sub-commands
- [ ] Player state | Session state | iName
- [ ] Freeze Session
- [ ] Chat Session
- [ ] Invisible Session
- [ ] Inventory Utils
- [ ] Item Name
- [ ] MoneyAPI
- [ ] Version
- [ ] gameModeStr
- [ ] popup/tip mgr
- [ ] NPC
- [ ] Shield Session
- [ ] Shopping Cart
- [ ] Sign Utils
- [ ] Skin utils
- [ ] Spy Session
- [ ] TP Utils

## Removed

- FastTransfer
- Command Selector
- GetMotd
- GetMotdAsyncTask
- QueryAsyncTask
- xPaw MinecraftQuery


## Changes

- 2.0.0: Complete rewrite

- 1.92.0: Update to new API
  * Added a FastTransfer class, with temporary work-around
- 1.91.0: De-bundle
  * New modules: TPUtils, ShoppingCart, SignUtils, SkinUtils, SpySession
  * De-bundled, now it is just a library again.  All sub-commands were moved
    to GrabBag.
  * Bug-Fixes: Cmd, InvUtils, Session, ShieldSession, BaseSelector
- 1.90.0: Major Update 2
  * MoneyAPI bug fix
  * Fixed BasicPlugin bug
  * Lots of new API features.
  * Added sub-commands
  * Bug Fixes:
    * MoneyAPI crash
    * BasicPlugin permission dispatcher
  * API Features
    * GetMotdAsyncTask
    * Session management
    * FileUtils
    * ArmorItems
    * Variables
    * PMScripts
    * ItemName can load user defined tables
    * SubCommandMap spinned-off from BasicPlugin
  * Sub commands
    * DumpMsgs
    * echo and basic echo
    * rc
    * motd utils
    * version
    * trace
- 1.1.0: Update 1
  * Added ItemName class (with more item names)
  * Removed MPMU::itemName
- 1.0.0: First release

<!-- template: license/gpl2.md -->
<!-- end-include -->
