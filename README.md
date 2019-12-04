utf8convert
============

This application reads every text and character field in a MySQL database 
and analyzes each DataPoint 
(the intersection of a row and column like a cell in a spreadsheet)
looking for invalid utf8 sequences.  This is a command line only application.


utf8convert database
--------------------

The utf8convert database stores a deconstructed target database and provides
a working environment for storing conversions and converted data.

![erd](https://raw.githubusercontent.com/API-Skeletons/utf8convert/master/media/erd.png)


Invalid utf8 sequence
---------------------

`8Â°6 crew`

This should be rendered as `8°6 crew` but the extended ° character has been decoded
from utf8 into multibyte component parts.  Where there is one there are 
probably many.  

`Duvalierâ€™s Dream`

This will be corrected to `Duvalier’s Dream`.

I created this tool to correct every invalid utf8 sequence in my database in 
a single conversion.  My example database finds around 89,000 invalid sequences.
Only DataPoints which have been converted will be exported.  Valid utf8 characters
will be evaluated too and ignored if they are correct.


About
-----

This application was inspired by [https://www.bluebox.net/insight/blog-article/getting-out-of-mysql-character-set-hell](https://www.bluebox.net/insight/blog-article/getting-out-of-mysql-character-set-hell)


Install
-------

```
composer install
cp config/autoload/local.php.dist config/autoload/local.php
; edit local for specific environment
php public/index orm:schema-tool:create
```


Use
---


Validation
----------

Validation occurs before a conversion may be ran.

Step 1: Validate the database.  This command will verify all database settings, table data types, and column data types are utf8.

```sh
php public/index.php database:validate
```

If the validate command failed you must correct the problem(s) before continuing.


Create a Conversion
-------------------

You need to create a conversion.  Each conversion requires a name.

whitelist and blacklist are comma delimited lists of table names.  If not specified then all tables will be evaluated.

```sh
php public/index.php conversion:create [--name=conversionName] [--whitelist=] [--blacklist=]
```


Run a Conversion
----------------

After your conversion has been created you must convert it.  

```sh
php public/index.php conversion:convert --name=conversionName
```


Export a Conversion
-------------------

To copy corrected utf8 data back into your database you must export it:

```sh
php public/index.php conversion:export --name=conversionName
```


Clone a Conversion
-----------------

To clone a conversion to a new name:

```sh
php public/index.php conversion:clone --from=conversionName --to=conversionName
```


Troubleshooting
---------------

If you ever get stuck you can always delete the `utf8convert` database and start over.
