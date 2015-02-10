gettext plural rules generated from CLDR data
=============================================


### Usage

1. To build the compressed JSON data
  ```bash
  php convert.php json
  ```

2. To build the uncompressed JSON data
  ```bash
  php convert.php prettyjson
  ```

3. To build a html table
  ```bash
  php convert.php html
  ```
  [See the result here](http://mlocati.github.io/cldr-to-gettext-plural-rules/)

3. To build a php file that can be included
  ```bash
  php convert.php php > yourfile.php
  ```
  Then you can use that generated fly in your php scripts:
  ```php
  <?php
  ...
  $rules = include 'yourfile.php';
  ...
  ```


### Is this data correct?

Yes - as far as you trust the CLDR Project.

The conversion from CLDR to gettext includes also a lot of tests to check the results. And all passes.