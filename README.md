<!-- template: startup.md -->


<!-- end-include -->
<img id="common.png" src="https://raw.githubusercontent.com/Muirfield/libcommon/master/media/common.png" style="width:64px;height:64px" width="64" height="64"/>
<!-- template: header.md -->

# libcommon

- Summary: Muirfield common library
- PocketMine-MP API version: 2.0.0
- DependencyPlugins: 
- OptionalPlugins: 
- Categories: N/A
- WebSite: https://github.com/Muirfield/libcommon


<!-- end-include -->
<!-- meta: API = http://muirfield.github.io/libcommon/apidocs/index.html -->
<!-- php:$copyright="2016"; -->

## Overview

`libcommon` implements functionality that can be used in other plugins,
like for example, ScriptPlugins.

API Features:

<!-- snippet: api-features -->
- Variable expansions
- Misc shorcuts and pre-canned routines
- Scripts
- Permission checks and utilities
- Unit Testing functionality
- API version checking
- Translations
- Paginated output
- Modular plugins
- Sub command support

<!-- end-include -->

See [API](http://muirfield.github.io/libcommon/apidocs/index.html )
documentation for full details.

The `libcommon` library is my standard library that I personally use when
developing PocketMine-MP plugins.  It can work equally well embeddeed within a
a plugin `.phar` file or as a stand-alone plugin.

In addition to the library code, the [github](https://github.com/Muirfield/libcommon) repository
includes a number of utility scripts for development.

- [x] mkplugin - create phar plugins
- [x] gd3tool - generate documentation
- [x] mkver - check version numbers
- [x] mcgen - generate message catalogues and encodings
- [ ] embed common
- [ ] precommit - Basis for a pre-commit hook
- [ ] test - Basis for unit testing in a CI context

## PMScripts

<!-- snippet: pmscript -->

The PMScript module implements a simple [PHP](https://secure.php.net/)
based scripting engine.  It can be used to enter multiple PocketMine
commands while allowing you to add PHP code to control the flow of
the script.

While you can embed any arbitrary PHP code, for readability purposes
it is recommended that you use
[PHP's alternative syntax](http://php.net/manual/en/control-structures.alternative-syntax.php)

By convention, PMScript's have a file extension of ".pms" and they are
just simple text file containing PocketMine console commands (without the "/").

To control the execution you can use the following prefixes when
entering commands:

* **+op:** - will give Op access to the player (temporarily) before executing
  a command
* **+con:** - execute command as if run from the console with the output sent to the player.
* **+syscon:** - run the command as if it was run from the console.

Also, before executing a command variable expansion (e.g. {vars}).

Available variables depend on installed plugins, pocketmine.yml
settings, execution context, etc.

It is possible to use PHP functions and variables in command lines by
surrounding PHP expressions with:

     '.(php expression).'

For example:

     echo MaxPlayers: '.$interp->getServer()->getMaxPlayers().'

### Adding logic flow to PMScripts

Arbitrary PHP code can be added to your pmscripts.  Lines that start
with "@" are treated as PHP code.  For your convenience,
you can ommit ";" at the end of the line.

Any valid PHP code can be used, but for readability, the use of
alternative syntax is recommended.

The execution context for this PHP code has the following variables
available:

* **$interp** - reference to the running PMSCript object.
* **$ctx** - This is the CommandSender that is executing the script
* **$server** - PocketMine server instance
* **$player** - If **$ctx** refers to a player, **$player** is defined, otherwise it is NULL.
* **$args** - Script's command line arguments

`{varnames}` are also available directly.

Example:

    # Sample PMScript
    #
    ; You can use ";" or "#" as comments
    #
    # Just place your commands as you would enter them on the console
    # on your .pms file.
    echo You have the following plugins:
    plugins
    echo {GOLD}Variable {RED}Expansions {BLUE}are {GREEN}possible
    echo TPS: {tps} MOTD: {MOTD}
    #
    # You can include in there PHP expressions...
    say '.$ctx->getName().' is AWESOME!
    ;
    # Adding PHP control code is possible:
    @if ({tps} > 10):
      echo Your TPS {tps} is greater than 10
    @else:
      echo Your TPS {tps} is less or equal to 10
    @endif
    ;
    ;
    echo You passed '.count($args).' arguments to this script.
	echo Arguments: '.print_r($args,TRUE).'

<!-- end-include -->

## Gd3Tool

gd3tool is a development tool used for automatically generating documentation.
It does this by analyzing the source code for specific tags, and then it will
rite them to a output document that contains markup that gets substituted with
the analyzed data.

### Embedded documentation syntax

<!-- snippet: gd3syntax -->

Embedded documentation is a "//" comment in PHP with the following text:

    //=

This introduces a new snippet definition.

    //#

This introduces a new snippet definition, but the text is also used

    //:

This adds body text to the snippet definition.

    //>

This adds body text but html entities are escaped.

If the "=" or "#" tags are not used, the current file name is used.

Also in addition, the following strings are looked for:

- Perms::add - these lines are added to the `rtperms` snippet.
- "# key" => "description" - these lines are added to the last
  snippet section found as a definition list.


<!-- end-include -->

### Output document

The output document also acts as a template (so the output of gd3tool can be
fed to itself as input).

<!-- snippet: doc-format -->

Document will look for the following templates codes to perform substitutions:

- &lt;!-- snippet: SNIPPET --&gt;' : Insert a snippet found in the source code
- &lt;!-- template: template --&gt;' : Insert a Markdown template (which may contain PHP codes)
- &lt;!-- end-include --&gt;' : Terminates a template/snippet.
- &lt;!--$varname--&gt;value&lt;!--$--&gt; : Looks up varname and replace value with it.
- \[varname\]\(value\) : Looks up varname and replaces value with it.


<!-- end-include -->

## Completed

- [x] PluginCallbackTask
- [x] Cmd
- [x] mc
- [x] Singleton
- [x] ExpandVars
- [x] MPMU
- [x] UniTest
- [x] Ver
- [x] access/permissions/inGame | addPerm
- [x] PMScript
- [x] Pager
- [x] IModular
- [x] = ModularPlugin
- [x] IModule
- [x] = BaseModule
- [x] IDispatcher
- [x] IDispatchable
- [x] CmdDispatcher implements IDispatcher
- [x] BaseCmdModule implements IDispatchable extends BaseModule
- [x] SubCmdDispatcher implements IDispatcher
- [x] BaseSubCmd(extends BaseModule)
- [x] HelpSubCmd
- [x] BasicPlugin

## WIP

## Backlog

- [ ] FileUtils
- [ ] Armoritems
- [ ] Player state | Session state | iName
- [ ] Freeze Session
- [ ] Chat Session
- [ ] Invisible Session
- [ ] Inventory Utils
- [ ] Item Name
- [ ] MoneyAPI
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
- GetMotd
- GetMotdAsyncTask
- QueryAsyncTask
- xPaw MinecraftQuery
- Command Selector

## Notes

- travis should check mkgen version and create plugins PHAR files

## Changes

- 2.0.0: Complete rewrite
  * All network related functions have been removed. (FastTransfer,
    GetMotd, GetMotdAsyncTask, QueryAsyncTask and xPaw's MinecraftQuery)
  * Removed Command selectors

- 1.92.0: Update to new API
  * New modules
  * Bug-Fixes
- 1.0.0: First release

<!-- template: license/gpl2.md -->
# Copyright

    libcommon
    Copyright (C) 2016 Alejandro Liu
    All Rights Reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.


<!-- end-include -->

