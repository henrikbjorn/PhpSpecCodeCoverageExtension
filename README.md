PhpSpec Code Coverage
=====================

Install it trought Composer:

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
