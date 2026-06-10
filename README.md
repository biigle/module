# BIIGLE Module Template

[![Test status](https://github.com/biigle/module/workflows/Tests/badge.svg)](https://github.com/biigle/module/actions?query=workflow%3ATests)

This is a template that can be used for the development of new BIIGLE modules.

## How to develop BIIGLE modules

The BIIGLE manual contains [some tutorials](https://biigle-admin-documentation.readthedocs.io/module-development/module-development/) that can get you started with BIIGLE module development. This repository implements the code that is covered in the first three tutorials.

## How to use this template

First, [create a new repository](https://github.com/biigle/module/generate) (either private or public) based on this template.

## Development & Development Installation

Clone the module to a local directory. The name of this module needs to be updated in several locations. For your convenience, we provide a Python script to automate this process.

Run `python changeModuleName.py` to update all locations at once. The module name is the name you gave to the module when you created the repository, i.e. the name of the parent folder of this file.

If your module is not (yet) published on Packagist, you need to add the following to biigle's composer.json (found in the BIIGLE root directory; if repositories already exist, add the new one to the list):

```
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:<github_username>/<your_module_name>"
        }
    ]
```

If the repository is private you need to have ssh-keys configured for your GitHub account. Run `composer require <github_username>/<your_module_name>:dev-main --ignore-platform-req=ext-ffi` in the BIIGLE root directory to install the module to BIIGLE. Still in the BIIGLE root directory run `php artisan vendor:publish --tag=public`.

The module is now installed to `biigle/vendor/<github_username>/<your_module_name>`. You can go there and develop your module. 

- `npm run dev`: Continuously builds the assets during development.
- `npm run build`: Builds, minifies and publishes the assets once.
- `npm run lint`: Run static analysis to check for errors.

Also update this readme for your new module. You should remove the first three subsections and update the installation instructions. And don't forget to change the author and GitHub address in `composer.json` as well.

## Installation

Note that you have to replace `biigle/module` with the actual name of your module/repository.

1. Run `composer require biigle/module`. *This requires your module to be published on [Packagist](https://packagist.org/). If you don't want to publish your package, read more on [alternative options](https://getcomposer.org/doc/05-repositories.md#vcs).*
2. Run `php artisan vendor:publish --tag=public` to refresh the public assets of the modules. Do this for every update of this module.

## Developing

Take a look at the [development guide](https://github.com/biigle/core/blob/master/DEVELOPING.md) of the core repository to get started with the development setup.

Want to develop a new module? Head over to the [biigle/module](https://github.com/biigle/module) template repository.

## Contributions and bug reports

Contributions to BIIGLE are always welcome. Check out the [contribution guide](https://github.com/biigle/core/blob/master/CONTRIBUTING.md) to get started.
