Polr Admin
==========

An alternative admin dashboard for the Polr URL shortener.

Our goal is to provide a dashboard with advanced features for Polr.

Features
--------

The features are mostly the same as in the Polr Admin section, but with few differences.

- The link redirection feature is not included.
- This dashboard is based on Laravel instead of Lumen.
- AngularJS is dropped in favor of Jaxon [https://www.jaxon-php.org](https://www.jaxon-php.org).
- A `Confirm Password` field is added to the `Change Password` form.

Installation
------------

Clone this repository to a local directory.

Get into the installed directory and run `composer install` to install the dependencies.

Fill the `env.example` file with the same parameters as your Polr installation, and rename to `.env`.

Setup your web server to serve the application from the `public` directory.
See the `Running Polr on...` section in the [Polr installation guide](https://docs.polrproject.org/en/latest/user-guide/installation/) to learn how to configure your prefered web server.
