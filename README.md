[PhpSpec](http://phpspec.net) Code Coverage
===========================================

Install with [Composer](http://getcomposer.org):

``` bash
composer require --dev henrikbjorn/phpspec-code-coverage
```

Enable it in your `phpspec.yml` file:

``` yaml
extensions:
    - PhpSpecCodeCoverage\CodeCoverageExtension
```

Now run your specs with the normal `phpspec run` and voilà your code coverage will be available in
`coverage`.

Configuration Options
---------------------

It is possible to control a bit out how the code coverage is done through `phpspec.yml`. This is done by
adding a hash of options to the extension key.

* `whitelist` takes an array of directories to whitelist (default: `lib`, `src`).
* `whitelist_files` takes an array of files to whitelist (default: none).
* `blacklist` takes an array of directories to blacklist
* `blacklist_files` takes an array of files to blacklist
* `format` (optional) could be one or many of: `clover`, `php`, `text`, `html` (default `html`)
* `output` takes a location relative to the place you are running `phpspec run` (default: `coverage`). If you configure multiple formats, takes a hash of format:output e.g.
```yaml
extensions:
  PhpSpecCodeCoverage\CodeCoverageExtension:
    format:
      - html
      - clover
    output:
      html: coverage
      clover: coverage.xml
```
* `show_uncovered_files` for including uncovered files in coverage reports (default `true`)
* `lower_upper_bound` for coverage (default `35`)
* `high_lower_bound` for coverage (default `70`)

**Note**: If the clover format option requires you to also set an output location!

Running with phpdbg (PHP 7.x and up)
-------------------

For faster execution, run phpspec with [phpdbg](http://phpdbg.com) instead of xdebug:
```sh
phpdbg -qrr phpspec run
```

**Note**: The code coverage extension only works with phpdbg if you're using PHP 7.
In PHP 5.6, phpdbg is missing the phpdbg_start_oplog function which is required to generate
code coverage.
