[![Build Status](https://api.travis-ci.org/mlocati/cldr-to-gettext-plural-rules.svg?branch=master)](https://travis-ci.org/mlocati/cldr-to-gettext-plural-rules)
# gettext plural rules generated from CLDR data


## Static usage

1. To build the compressed JSON data
  ```bash
  php bin/export.php json
  ```

2. To build the uncompressed JSON data
  ```bash
  php bin/export.php prettyjson
  ```

3. To build a html table
  ```bash
  php bin/export.php html
  ```
  [See the result here](http://mlocati.github.io/cldr-to-gettext-plural-rules/)

3. To build a php file that can be included
  ```bash
  php bin/export.php php > yourfile.php
  ```
  Then you can use that generated fly in your php scripts:
  ```php
  <?php
  ...
  $rules = include 'yourfile.php';
  ...
  ```

## Dynamic usage

You can include the files in this project (by loading the `src/autoloader.php` file or using composer).
That way you can directly use the project classes in your php projects:
```php
<?php
...
require_once 'path/to/src/autoloader.php';
...
$allLanguages = GettextLanguages\Language::getAll();
...
$oneLanguage = GettextLanguages\Language::getById('en_US');
...
```


## Is this data correct?

Yes - as far as you trust the CLDR Project.

The conversion from CLDR to gettext includes also [a lot of tests](https://travis-ci.org/mlocati/cldr-to-gettext-plural-rules) to check the results.
And all passes :wink:.



## Reference

#### CLDR

The [CLDR specifications](http://unicode.org/reports/tr35/tr35-numbers.html#Language_Plural_Rules) define the following variables to be used in the CLDR plural formulas:
- `n`: absolute value of the source number (integer and decimals) (eg: `9.870` => `9.87`)
- `i`: integer digits of n (eg: `9.870` => `9`)
- `v`: number of visible fraction digits in n, with trailing zeros (eg: `9.870` => `3`)
- `w`: number of visible fraction digits in n, without trailing zeros (eg: `9.870` => `2`)
- `f`: visible fractional digits in n, with trailing zeros (eg: `9.870` => `870`)
- `t`: visible fractional digits in n, without trailing zeros (eg: `9.870` => `87`)

#### gettext
The [gettext specifications](http://www.gnu.org/savannah-checkouts/gnu/gettext/manual/html_node/Plural-forms.html) define the following variables to be used in the gettext plural formulas:
- `n`: unsigned long int

### Conversion CLDR > gettext

| CLDR variable | gettext equivalent |
|---------------|--------------------|
| `n`           | `n`                |
| `i`           | `n`                |
| `v`           | `0`                |
| `w`           | `0`                |
| `f`           | *empty*            |
| `t`           | *empty*            |


## Parenthesis in ternary operators

The generated gettext formulas contain some extra parenthesis, in order to avoid problems in some programming language.
For instance, let's assume we have this formula:
`(0 == 0) ? 0 : (0 == 1) ? 1 : 2`
- [in C it evaluates to `0`](http://codepad.org/Epw5WkmJ) since is the same as `(0 == 0) ? 0 : ((0 == 1) ? 1 : 2)`
- [in Java it evaluates to `0`](https://ideone.com/vbRHjW) since is the same as `(0 == 0) ? 0 : ((0 == 1) ? 1 : 2)`
- [in JavaScript it evaluates to `0`](http://jsfiddle.net/7fnxa599/) since is the same as `(0 == 0) ? 0 : ((0 == 1) ? 1 : 2)`
- [in PHP it evaluates to `2`](http://3v4l.org/QAAnA) since is the same as `((0 == 0) ? 0 : (0 == 1)) ? 1 : 2`

So, in order to avoid problems, instead of a simple
`a ? 0 : b ? 1 : 2`
the resulting formulas will be in this format:
`'a ? 0 : (b ? 1 : 2)`
