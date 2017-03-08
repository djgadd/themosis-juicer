Themosis Juicer
===============

A WordPress plugin for the Themosis framework that provides an wrapper for the
Juicer.io API. Requires a Juicer account to be of any use, charges apply. Also
provides an admin UI to do some basic tasks, such as adding sources and removing
posts from a feed, and an ACFv5 field.

Install
-------
Install through composer: -

`composer require keltiecochrane/themosis-juicer`

Create a juicer.config.php, and add the following: -

```
return [
  'slug' => 'feed-slug',
  'token' => 'your-token',
];
```

Usage
-----
Once activated, use the facade to access the different endpoints, e.g.: -

```
$feed = Juicer::feed(Config::get('juicer.slug'));

$feed->update_frequency = 240;
$feed->save();

$sources = $feed->sources()->get();

$posts = $feed->posts()
  ->filter('Twitter')
  ->get();
```

Todo
----
* Caching needs further integration.
* Access a source's posts.
* Documentation.


Support
-------
This plugin is provided as is, though we'll endeavour to help where we can. By
using this plugin you forfeit your right to any warranty or costs associated with
it's use.

Contributing
------------
Any contributions would be encouraged and much appreciated, you can contribute by: -

* Reporting bugs
* Suggesting features
* Sending pull requests
