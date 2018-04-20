# ModularRouting
This component is part of the [Harmony Project](http://harmony-project.io).

The ModularRouting component extends the [Symfony Routing component](https://github.com/symfony/routing)
with a new router using a customizable matching process that's optimized for
reusable routes.

Features provided by this component are:
- Cache-enabled router
- Customizable mapping process
- Routing metadata configurable by XML, YAML

The `ModularRouter` class included in this component can either be used as a
replacement of Symfony's `Router` class or as an extension to it by using 
Symfony's CMF `ChainRouter` class. See [HarmonyModularBundle](https://github.com/harmony-project/modular-bundle)
for integration with the Symfony framework.

## Installation
This component is available as a package on [Packagist](https://packagist.org):

    composer require harmony-project/modular-routing

## Resources
* [GitHub repository](https://github.com/harmony-project/modular-routing)
* [Packagist package](https://packagist.org/packages/harmony-project/modular-routing)

<!-- Line break -->

* [Documentation](http://harmony-project.io/docs/modular-routing)
* [Getting Started guide](http://harmony-project.io/docs/modular-routing/getting-started)
* [Symfony integration](https://github.com/harmony-project/modular-bundle)

<!-- Line break -->

* [Symfony Routing](https://github.com/symfony/routing)
* [Symfony CMF Routing](https://github.com/symfony-cmf/routing)

## License
This component is released under the [MIT license](https://github.com/harmony-project/modular-routing/blob/master/license).
