UTF8 Convert
============

If you're stuck in hell it's time to convert!  If you did not consider the whole world as your user you probably didn't create your database in UTF8.  This leads to a long road of ugly consequences such as utf8 encoding in latin1, double, triple, quadrouple and more utf8 encoding!  Oh gentle reader, take heed and convert your MySQL database to utf8 before the time is latter than it was when I wrote this.


About
-----

This is a program to facilitate the commands and sql as outlined in [https://www.bluebox.net/insight/blog-article/getting-out-of-mysql-character-set-hell](https://www.bluebox.net/insight/blog-article/getting-out-of-mysql-character-set-hell)

You will need to read this whole article.


Install
-------

Run ```composer.phar install``` then copy ```~/config/autoload/local.php.dist``` to ```~/config/autoload/local.php``` and edit for your environment.


Use
---

Run ```php public/index.php validate```  This will tell you if you have any tables or columns which are not set to utf8.  If you have tables which are not utf8 you will be prompted to run ```php public/index.php generate table conversion``` which will output the command line commands to change your tables to utf8.  You may pipe this directly to shell to run the commands: ```php public/index.php generate table conversion | sh```

If you have a supplement.sql script run it now.

Next is table refactoring.  This will change all char, enum, and varchar fields to varchar(255).  This step is included because if you're bothering to fix all your utf8 data you should probably fix your tables to Doctrine 2 standards while you're at it.  To refactor run ```php public/indx.php refactor```  For this writing you may not skip this step.  There may be sql errors raised.  This often happens when there is a multi-column index for strings and the new index size is out of bounds.  Create a supplement.sql script with sql to fix this problem the next time you run the refactoring.  If a sql error is encountered you may safely re-run this command after fixing it.


Utf8Changes
-----------

In the conversion process a new table is created called Utf8Changes with the old and new values for each datapoint changed in the conversion.  You will want to review all the data in this table to find any anomolies after the conversion is complete.

