# Biigle Module Template

This is a template that can be used for the development of new BIIGLE modules.

## How to develop BIIGLE modules

The BIIGLE manual contains [some tutorials](https://biigle-admin-documentation.readthedocs.io/module-development/module-development/) that can get you started with BIIGLE module development. This repository implements the code that is covered in the first three tutorials.

## How to use this template

First, [create a new repository](https://github.com/biigle/module/generate) based on this template. Then update the name of this module from `biigle/module` to whatever name you want to use for your module. The name has to be updated at the following locations:

1. [`QuotesController.php`](src/Http/Controllers/QuotesController.php#L16)
2. [`index.blade.php#L13`](src/resources/views/index.blade.php#L13)
3. [`index.blade.php#L16`](src/resources/views/index.blade.php#L16)
4. [`ModuleServiceProvider.php#L20`](src/ModuleServiceProvider.php#L20)
5. [`ModuleServiceProvider.php#L29`](src/ModuleServiceProvider.php#L29)
6. [`ModuleServiceProvider.php#L42`](src/ModuleServiceProvider.php#L42)
7. [`composer.json#L2`](composer.json#L2)
8. [`test.yml#L15`](.github/workflows/test.yml#L15)

Next, update the namespace of all PHP classes (`Biigle\Modules\Module`) and replace `Module` with the name of your module. Do this in [`webpack.mix.js`](webpack.mix.js#L23), too. Now you can install the module and start developing.

In addition to the code of the [tutorials](https://biigle.de/manual#developer-tutorials) this repository already contains the configuration for [Laravel Mix](https://laravel.com/docs/6.x/mix) as build system. To install the build system, run and then run `npm install`. Now you can use the following commands:

- `npm run dev`: Builds and publishes the assets once.
- `npm run prod`: Builds, minifies and publishes the assets once. Always do this before you commit new code.
- `npm run watch`: Continuously builds and publishes the assets whenever an asset file is changed.
- `npm run lint`: Run static analysis to check for errors.

## How wo install this module

Note that you have to replace `biigle/module` with the actual name of your module/repository.

1. Run `composer require biigle/module --prefer-source`. This requires your module to be published on [Packagist](https://packagist.org/). If you don't want to publish your package, read more on [alternative options](https://getcomposer.org/doc/05-repositories.md#vcs).
2. Add `Biigle\Modules\Module\ModuleServiceProvider::class` to the `providers` array in `config/app.php`. Replace `Module` in the class namespace with the name of your module.
3. Run `php artisan vendor:publish --tag=public` to refresh the public assets of the modules. Do this for every update of this module.

