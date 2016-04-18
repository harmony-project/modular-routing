Modular Routing Component
=========================

The Harmony Modular Routing Component uses Symfony components to map a HTTP Request to a set of configuration variables. This component can be used either as a replacement of the Symfony Router or as an extension by using the Symfony CMF ChainRouter. This component is also available as a Symfony bundle.

Introduction
------------
This component allows you to use the Symfony route matching process against different sets of metadata depending on the request and other parameters.

Features include:
- Cache-enabled Router
- Routing metadata configurable by YAML
- Customizable mapping process

Upcoming features:
- Multiple module mappings in a single Router-instance

Installation
------------
This component is available as a package in Composer:

    composer require harmony-project/modular-routing

Documentation
-------------
Learn more about this component in the Harmony documentation.