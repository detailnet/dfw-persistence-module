# Zend Framework Module for Doctrine based persistence

[![Build Status](https://travis-ci.org/detailnet/dfw-persistence-module.svg?branch=master)](https://travis-ci.org/detailnet/dfw-persistence-module)
[![Coverage Status](https://img.shields.io/coveralls/detailnet/dfw-persistence-module.svg)](https://coveralls.io/r/detailnet/dfw-persistence-module)
[![Latest Stable Version](https://poser.pugx.org/detailnet/dfw-persistence-module/v/stable.svg)](https://packagist.org/packages/detailnet/dfw-persistence-module)
[![Latest Unstable Version](https://poser.pugx.org/detailnet/dfw-persistence-module/v/unstable.svg)](https://packagist.org/packages/detailnet/dfw-persistence-module)

## Introduction
This module provides useful classes for building a [Zend Framework](https://github.com/zendframework/zendframework) application with a persistance layer based on [Doctrine](https://github.com/doctrine).

## Requirements
[Zend Framework Skeleton Application](http://www.github.com/zendframework/ZendSkeletonApplication) (or compatible architecture)

## Installation
Install the module through [Composer](http://getcomposer.org/) using the following steps:

  1. `cd my/project/directory`
  
  2. Create a `composer.json` file with following contents (or update your existing file accordingly):

     ```json
     {
         "require": {
             "detailnet/dfw-persistence-module": "^1.1"
         }
     }
     ```
  3. Install Composer via `curl -s http://getcomposer.org/installer | php` (on Windows, download
     the [installer](http://getcomposer.org/installer) and execute it with PHP)
     
  4. Run `php composer.phar self-update`
     
  5. Run `php composer.phar install`
  
  6. Open `configs/application.config.php` and add following key to your `modules`:

     ```php
     'Detail\Persistence',
     ```

  7. Copy `vendor/detailnet/dfw-persistence-module/config/detail_persistence.local.php.dist` into your application's
     `config/autoload` directory, rename it to `detail_persistence.local.php` and make the appropriate changes.
