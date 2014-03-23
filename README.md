[PhpSpec](http://phpspec.net) Code Coverage
===========================================

Add it to your `composer.json` file to install with [Composer](http://getcomposer.org):

``` json
{
    "require" : {
        "henrikbjorn/phpspec-code-coverage" : "1.0@dev"
    }
}
```

``` bash
$ composer update henrikbjorn/phpspec-code-coverage
```

Enable it in your `phpspec.yml` file:

``` yaml
extensions:
    - PhpSpec\Extension\CodeCoverageExtension
```

Now run your specs with the normal `phpspec run` and voila your code coverage will be available in
`coverage`.

Configuration Options
---------------------

It is possible to control a bit out how the code coverage is done through `phpspec.yml`. This is done by
adding a `code_coverage` key which takes a hash of options.

* `whitelist` takes an array of directories to whitelist (default: `lib`, `src`).
* `blacklist` takes an array of directories to blacklist (default: `test`, `spec`, `vendor`).
* `whitelist_files` takes an array of files to whitelist (default: none).
* `blacklist_files` takes an array of files to blacklist (default: none).
* `output` takes a location relative to the place you are running `phpspec run` (default: `coverage`).
* `format` (optional) could be one of: `clover`, `php`, `text`, `html` (default `html`)
* `show_uncovered_files` for including uncovered files in coverage reports (default `true`)
* `lower_upper_bound` for coverage (default `35`)
* `high_lower_bound` for coverage (default `70`)