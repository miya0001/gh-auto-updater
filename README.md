# Automatic Updater with GitHub API for WordPress Plugin

[![Build Status](https://travis-ci.org/miya0001/gh-auto-updater.svg?branch=master)](https://travis-ci.org/miya0001/gh-auto-updater)

This library enables your WordPress plugin to automatic update with GitHub API.

## Getting Started

### 1. The easier way to install this is by using composer.

```
$ composer require miya/gh-auto-updater
```

This project is in progress, you should add `"minimum-stability": "dev"` into `composer.json`.

### 2. Activate automatic update in your WordPress plugin.

```
</php

// Autoload
require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

add_action( 'init', function() {
	$plugin_slug = plugin_basename( __FILE__ );
	$gh_user = 'miya0001';                // The user name of GitHub.
	$gh_repo = 'gh-auto-updater-example'; // The repository name of your plugin.

	// Activate automatic update.
	new Miya\WP\GH_Auto_Update( $plugin_slug, $gh_user, $gh_repo );
} );
```

## How to update your plugin.

To release the new version, please do as follows:

### 1. Tag and push to GitHub.

```
$ git tag 1.1.0
$ git push origin 1.1.0
```

* `1.0.0` is a version number, it have to be same with version number in your WordPress plugin.
* You have to contain `vendor` directory in your plugin or you should use [automatic release](https://docs.travis-ci.com/user/deployment/releases/).

### 2. Release the new version.

1. Please visit "releases" in your GitHub repository.
2. Choose a tag.
3. Fill out the release note and title.
4. Upload your plugin which is comporessed with zip. (Optional)
5. Press "Publish release".

![](https://www.evernote.com/l/ABVIG1PlajlJ5rfHivP4BJRELESS9uKndHAB/image.png)

## Example Projects

Please install old version of following projects, then you can see update notice.

* https://github.com/miya0001/gh-auto-updater-example
* https://github.com/miya0001/miya-gallery

These projects deploy new releases automatically with Travis CI.

```
$ travis setup releases
```

Please check `.travis.yml` and [documentation](https://docs.travis-ci.com/user/deployment/releases/).
