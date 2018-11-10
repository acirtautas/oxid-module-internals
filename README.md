Great module proudly presented by [OXID Hackathon 2017](https://openspacer.org/12-oxid-community/185-oxid-hackathon-nuernberg-2017/) ;-)

Module version for OXID eShop 6. Original module by [Alfonsas Cirtautas](https://github.com/acirtautas/oxid-module-internals).

# Features

 * Display highlighted metadata file content.
 * Reset module related shop cache data.
 * Toggle module activation / deactivation
 * Compare and troubleshoot metadata vs internally stores data
   * Extended classes
   * Template blocks
   * Settings
   * Registered files
   * Registered templates
   * Version
   * Events

# Installation

```
composer require oxid-community/moduleinternals
```

# Screenshot

![OXID_moduleinternals](screenshot.png)

# Ideas

 * Consistency check for namespaces, if namespace is not registered or wrongly spelled in some classes
 * Check for usage of unified namespaces

# Changelog

* 2017-12-15	1.0.1	namespace, docblocks
* 2017-12-09	1.0.0	module release
* 2018-09-13    1.1.0   add external module healthy status page