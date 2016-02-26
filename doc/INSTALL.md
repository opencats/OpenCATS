# OpenCATS Installation Procedure

## Requirements

* GNU/Linux, FreeBSD or Windows NT-based (2000, XP, 2003, Vista) Operating System
* PHP
* MySQL or MariaDB
* Antiword 
* PdfToText
* html2text
* UnRTF
* PHP Zip library
* PHP LDAP library

## Installation

### Unix/Linux

#### Installing Pre-requisites

##### PHP
PHP can be installed from the distribution's package repository (yum/apt)

CentOS:
	#yum install php

##### Apache HTTPD
Apache HTTPD can be installed from distribution specific package repository (yum/apt)

CentOS:
	#yum install httpd

##### MySQL/MariaDB
MySQL in CentOS:
	yum install mysql-server

MariaDB in CentOS:
	yum install mariadb
	
##### PHP GD
CentOS:
	yum install php-gd

##### PHP LDAP
CentOS:
	yum install php-ldap

##### Antiword
Antiword is available in CentOS yum repository
	yum install antiword

##### PdfToText
[ http://www.foolabs.com/xpdf/ ]

##### html2text
[ http://www.mbayer.de/html2text/ ]

##### UnRTF
[ http://www.gnu.org/software/unrtf/unrtf.html ]

### OpenCATS

#### Step 1
Unpack tarball (cats-0.9.1.tar.gz) under your apache document root  (/var/www/html, /usr/local/apache2/htdocs, /cygdrive/c/wamp/www, or similar) and create a symlink to it named 'opencats':

	# cd /var/www/html
	# tar zxvf cats-0.9.1.tar.gz
	# ln -s cats-0.9.1 cats

#### Step 2
Launch MySQL client and create a new database and user.

	# mysql -uroot -p
	mysql> CREATE DATABASE opencats;
    mysql> GRANT ALL PRIVILEGES ON `opencats`.* TO  'opencats'@'localhost' IDENTIFIED BY 'password';
	mysql> EXIT;

#### Step 3
Change ownership of the installation directory to the user and group that your web server runs under. This is usually 'apache', 'nobody', or 'www' (you can do a ps -auxww to see what user your web server is running as).

	# chown apache:apache opencats
	# chown -R apache:apache opencats
	# chmod 770 opencats/attachments

#### Step 4
Preform any necessary apache configuration changes so that the installation is accessible from a web browser (aliases, virtual hosts, etc.). See apache documentation for how to do this.

#### Step 5
In a web browser, visit the OpenCATS installation

Example:
    http://localhost/opencats

(Replace *localhost* with your domain name, or the ip address of your server

*Tip: If the installer does not load, check to see if there is a file called 'INSTALL_BLOCK' in the OpenCATS directory. Delete it to allow the installer to be executed*

#### Step 6
Follow the installer directions to complete the installation.

If any tests do not pass, check your configuration and requirements fulfillment and refresh the page (hold down shift while refreshing for Firefox and IE to ensure a refresh). You may visit the forum on http://www.opencats.org/forums for support.

##### Step 7
OpenCATS should now be up and running. Enjoy! Please visit https://github.com/opencats if you wish to contribute to OpenCATS

### Windows
This instructions are for the WAMP environment only.

#### Pre-requisites

##### WAMP
        [ http://www.wampserver.com/en/ ]
##### WinRAR
        [ http://www.rarlab.com/ ]
##### PHP GD2
        PHP GD2 Module [ http://www.boutell.com/gd/ ] ***
##### Antiword
        Antiword [ www.winfield.demon.nl/ ]
##### PdfToText 
        PdfToText [ http://www.foolabs.com/xpdf/ ]
##### html2text
         html2text [ http://www.mbayer.de/html2text/ ]
##### UnRTF
         UnRTF [ http://www.gnu.org/software/unrtf/unrtf.html ]

#### OpenCATS

##### Step 1
Open tarball (cats-0.9.1.tar.gz) under WinRAR and extract all files to c:\wamp\www (or your web server's document root folder).

##### Step 2
Launch phpMyAdmin by clicking on the WAMP icon in your system tray and  selecting phpMyAdmin.  A web browser will open.  In the page that displays, type 'opencats' into the textbox under Create new database and click the Create button.

##### Step 3
Enable GD2 by clicking on the WAMP icon in your system tray and selecting 'PHP settings' followed by 'PHP extensions', and selecting 'php_gd2'.

##### Step 4
Bring your WAMP server online by clicking on the WAMP icon in your system tray and selecting 'Put Online'.

##### Step 5
In a Web Browser, visit http://localhost/cats-0.9.1/ .  If OpenCATS has been configured correctly, you should see a page that says:

        CATS has not yet been installed, or a previous installation was not completed.
                    Please visit the Installation Wizard to continue.

Follow the link to the Installation Wizard to complete installation.  When asked for database name, user, and password use database 'opencats', user 'root', and a blank password.

## Upgrading 

### Unix/Linux

*THESE INSTRCUTIONS ARE FOR USERS OF A LINUX OR UNIX OPERATING SYSTEM. For installation instructions for Windows, read part F) Upgrading from an Earlier Version (Windows)*

##### Step 1
Unpack tarball (cats-0.9.1.tar.gz) under your apache document root (/var/www/html, /usr/local/apache2/htdocs or similar).
	# cd /var/www/html
	# tar zxvf cats-0.9.1.tar.gz

##### Step 2
Back up the *opencats* database by issuing the following command at  your shell prompt:

        # mysqldump -uroot -p cats > ~/cats-backup.sql

(enter the password you created for the 'opencats' user during install when prompted to do so)

Please note that this backup can not be restored by the interactive CATS installer - it is a failsafe incase the upgrade fails and the database becomes corrupt. If you later need to restore the database from this backup, you can issue the command:

        # mysql -uroot -p cats < ~/cats-backup.sql

##### Step 3
Remove the 'opencats' symlink (DO NOT USE rm -rf; this would delete all of your attachments. USE rm WITH NO COMMAND LINE OPTIONS!):

        # rm cats

##### Step 4
Copy the attachments/ directory from cats-x.x.x/ to cats-0.9.1/:

        Linux:
        # cp -p -r cats-x.x.x/attachments/ cats-0.9.1/

        FreeBSD:
        # cp -p -R cats-x.x.x/attachments/ cats-0.9.1/

        Where x.x.x is your older version number of CATS.

##### Step 5
     5) Create a symlink to the cats-0.9.1 directory:

        # ln -s cats-0.9.1 cats

##### Step 6
     6) Change ownership of the installation directory to the user and group
        that your web server runs under. This is usually 'apache', 'nobody',
        or 'www' (you can do a ps -auxww to see what user your web server is
        running as).

        # chown apache:apache cats
        # chown -R apache:apache cats-0.9.1/

##### Step 7
     7) In a web browser, visit the CATS installer page inside the cats web
        directory to finish the installation process: *

        http://mydomain.com/cats/installwizard.php

        (Replacing 'mydomain.com' with your domain name, or the ip address
        of your server)

         * Tip: If the installer does not load, check to see if there is a file
           called 'INSTALL_BLOCK' in the CATS directory. Delete it to allow the
           installer to be executed.

##### Step 8
     8) Follow the installer directions to complete the installation. Your
        database schema will be upgraded automatically.

        If any tests do not pass, check your configuration and requirements
        fulfillment and refresh the page (hold down shift while refreshing
        for Firefox and IE to ensure a refresh). You may visit the forum
        on http://www.catsone.com/ for support.

##### Step 9
     9) CATS should now be up and running. Enjoy! Remember to visit
        http://www.catsone.com/ and participate in the forum.

### Windows

##### Step 1 
     1) Open tarball (cats-0.9.1.tar.gz) and extract all files
        to c:\wamp\www (or your web server's document root folder).

##### Step 2
     2) Launch phpMyAdmin by clicking on the WAMP icon in your system tray and
        selecting phpMyAdmin.  A web browser will open.

        On the dropdown menu under Database on the left, Choose 'cats'.

        Select the 'Export' tab.

        Check 'Save as File' at the bottom of the window and press 'Go'.  Download
        and put the resulting SQL file somewhere safe on your computer.
        you will need this file to restore your database in the event
        something goes wrong during the upgrade process.

##### Step 3 
     3) Copy the attachments folder from your previous installation of CATS
        into your new installation of CATS.  For example, if you installed
        CATS 0.8.0 in c:\wamp\www\cats-0.8.0, move c:\wamp\www\cats-0.8.0\attachments
        to c:\wamp\www\cats-0.9.1\attachments.

##### Step 4 
     4) In a Web Browser, visit http://localhost/cats-0.9.1/ .  If CATS has been
        configured correctly, you should see a page that says:

        CATS has not yet been installed, or a previous installation was not completed.
                    Please visit the Installation Wizard to continue.

        Follow the link to the Installation Wizard to complete installation.




## Advanced CATS Add-ons

        For advanced users, CATS has a few special features which can be
        manually installed.

### Sphinx Indexing
     1) Sphinx Indexing [ http://www.sphinxsearch.com/ ]:
        CATS can integrate with the advanced Sphinx to dramatically improve
        the speed which indexed documents are searched (more than 200x speed
        improvement).  To learn how to integrate with the Sphinx engine, visit
        the CATS forums at http://www.catsone.com/forum/.
### Scheduled E-Mail reminders
     2) Scheduled E-Mail reminders
        CATS can send out E-Mail reminders for calendar events before they happen.
        To enable this feature, configure cron or another scheduling daemon to
        invoke QueueCLI.php every minute.  An example crontab line would look like:

            * * * * * /usr/local/bin/php /var/www/html/cats/QueueCLI.php

        Or,

            * * * * * curl http://mysite.com/QueueCLI.php > /dev/null
### CLI / On demand backups

     3) CLI / On demand backups
        CATS can generate a backup from the command line on Unix systems.  If the
        Unix zip utility is installed, then you can execute:

        php scripts/makeBackup.php 1

        from the CATS root directory to generate a backup in scrips/backup/catsbackup.bak.

        This, combined with a script to rotate backups which was executed from
        cron, could yield automated backups.

## Integration with Sphinx

        As of CATS 0.9.1 Sphinx integration is available via separate package.
        Sphinx speeds up text-based database searches considerably.
        
        Download URL:
        http://www.catsone.com/modules/asp/website/tarballs/sphinx_for_cats.tar.gz

## Enable LDAP Authentication

