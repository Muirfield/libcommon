<!-- template: startup.md -->
<!-- end-include -->
<!-- template: header.md -->
<!-- end-include -->
<!-- meta: API = http://muirfield.github.io/libcommon/apidocs/index.html -->

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
- 