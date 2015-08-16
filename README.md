UTF8 Convert
============

If you're stuck in hell it's time to convert!  If you did not consider the whole world as your user you probably didn't create your database in UTF8.  This leads to a long road of ugly consequences such as utf8 encoding in latin1, double, triple, quadrouple and more utf8 encoding!  Oh gentle reader, take heed and convert your MySQL database to utf8 before the time is latter than it was when I wrote this.


About
-----

This is a program to facilitate the commands and sql as outlined in [https://www.bluebox.net/insight/blog-article/getting-out-of-mysql-character-set-hell](https://www.bluebox.net/insight/blog-article/getting-out-of-mysql-character-set-hell)


Install
-------

```
./composer.phar install
cp config/autoload/local.php.dist config/autoload/local.php
; edit local for specific environment
php public/index orm:schema-tool:create
php public/index data-fixture:import
php public/index create-administrator --email=email@net --displayName=administrator
```
The admin login information is returned from create-administrator

Use
---

Validation
----------

Validation occurs before a conversion may be ran.

Step 1: Validate the database.  This command will verify all database settings, table data types, and column data types are utf8.

```sh
php public/index.php validate
```

Step 2: If the validate command failed you my create a SQL script with the commands to fix the database.

```sh
php public/index.php generate table conversion
```

Create a Conversion
-------------------

You can create multiple conversions in order to break up the sections of your data.  This isn't necessary and one
large conversion will work too.  At any rate, you need to create a conversion.  Each conversion requires a name.

whitelist and blacklist are comma delimited lists of table names.

```sh
php public/index.php create conversion [--name=conversionName] [--whitelist=] [--blacklist=]
```

Run a Conversion
----------------

This step is not a part of creating a conversion.  After your conversion has been created you must run it.  

```sh
php public/index.php run conversion --name=conversionName
```

Copy a Conversion
-----------------

To copy a conversion which has already been ran to a new name.

```sh
php public/index.php copy conversion --from=conversionName --to=conversionName
```

Refactor
--------

You may choose to refactor your database.  With this tool you can create a script to convert all varchar, char, and enum
fields to varchar(255) and all text, mediumtext to longtext.  This is a *strong* command which will change your database
structure permenantly.  Because of this a supplement.sql script is necessary to setup the database usually to adjust
table keys and indexes.

whitelist and blacklist are comma delimited lists of table names.

```sh
php public/index.php refactor --supplement-has-been-ran [--whitelist=] [--blacklist=]
```

Troubleshooting
---------------

Delete all the conversion data for all conversions from the conversion database
```sh
php public/index.php truncate conversion data
```

