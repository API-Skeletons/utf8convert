UTF8 Convert
============

If you're stuck in hell it's time to convert!  If you did not consider the whole world as your user you probably didn't create your database in UTF8.  This leads to a long road of ugly consequences such as utf8 encoding in latin1, double, triple, quadrouple and more utf8 encoding!  Oh gentle reader, take heed and convert your MySQL database to utf8 before the time is latter than it was when I wrote this.


About
-----

This is a program to facilitate the commands and sql as outlined in [https://www.bluebox.net/insight/blog-article/getting-out-of-mysql-character-set-hell](https://www.bluebox.net/insight/blog-article/getting-out-of-mysql-character-set-hell)

You will need to read this whole article.

This repository was created to convert db.etree.org to utf8.  It has been released in the hopes others can benefit with it.

Install
-------

Run ```composer.phar install``` then copy ```~/config/autoload/local.php.dist``` to ```~/config/autoload/local.php``` and edit for your environment.


Use
---

Run ```php public/index.php validate```  This will tell you if you have any tables or columns which are not set to utf8.  If you have tables which are not utf8 you will be prompted to run ```php public/index.php generate table conversion``` which will output the command line commands to change your tables to utf8.  You may pipe this directly to shell to run the commands: ```php public/index.php generate table conversion | sh```

If you have a supplement.sql script run it now.

Next is table refactoring.  This will change all char, enum, and varchar fields to varchar(255).  This step is included because if you're bothering to fix all your utf8 data you should probably fix your tables to Doctrine 2 standards while you're at it.  To refactor run ```php public/indx.php refactor```  For this writing you may not skip this step.  There may be sql errors raised.  This often happens when there is a multi-column index for strings and the new index size is out of bounds.  Create a supplement.sql script with sql to fix this problem the next time you run the refactoring.  If a sql error is encountered you may safely re-run this command after fixing it.


Tracking Changes
----------------

In the conversion process a new table is created called Utf8Changes with the old and new values for each datapoint changed in the conversion.  You will want to review all the data in this table to find any anomolies after the conversion is complete.


License
-------

Copyright (c) 2015, Stuki Org
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
      this list of conditions and the following disclaimer.

    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.

    * Neither the name of Stuki Org nor the names of its
      contributors may be used to endorse or promote products derived from this
      software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.