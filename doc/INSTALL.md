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
Unpack tarball (cats-0.9.1.tar.gz) under your apache document root  (/var/www/html, /usr/local/apache2/htdocs, /cygdrive/c/wamp/www, or similar) and rename the folder to *opencats*:

	# cd /var/www/html
	# tar zxvf cats-0.9.1.tar.gz
	# mv cats-0.9.1 opencats

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

If selinux is enabled run the following command:
	# chcon -R -t httpd_sys_rw_content_t opencats -R

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

If any tests do not pass, check your configuration and requirements fulfillment and refresh the page (hold down shift while refreshing for Firefox and IE to ensure a refresh). You may visit the forum on http://forums.opencats.org for support.

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
        PHP GD2 Module [ http://www.boutell.com/gd/ ]
##### Resume Indexing Tools
Download iand install the tools from the following URL:
 
http://downloads.opencats.org/setupResumeIndexingTools.exe

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
Open tarball (cats-0.9.1.tar.gz) under WinRAR and extract all files to c:\wamp\www (or your web server's document root folder). Rename cats-0.9.1 to *opencats*.

##### Step 2
Launch phpMyAdmin by clicking on the WAMP icon in your system tray and selecting phpMyAdmin. A web browser will open.  In the page that displays, type 'opencats' into the textbox under Create new database and click the Create button.

##### Step 3
Enable GD2 by clicking on the WAMP icon in your system tray and selecting 'PHP settings' followed by 'PHP extensions', and selecting 'php_gd2'.

##### Step 4
Bring your WAMP server online by clicking on the WAMP icon in your system tray and selecting 'Put Online'.

##### Step 5
In a Web Browser, visit http://localhost/opencats/. If OpenCATS has been configured correctly, you should see a page that says:

	CATS has not yet been installed, or a previous installation was not completed.
    Please visit the Installation Wizard to continue.

Follow the link to the Installation Wizard to complete installation. When asked for database name, user, and password use database 'opencats', user 'root', and a blank password.

## Upgrading 

### Unix/Linux

##### Step 1
Rename the *opencats* folder to *opencats-old*

	# mv opencats opencats-old

##### Step 2
Unpack tarball (cats-0.9.1.tar.gz) under your apache document root (/var/www/html, /usr/local/apache2/htdocs or similar).

	# cd /var/www/html
	# tar zxvf cats-0.9.1.tar.gz
	# mv cats-0.9.1 opencats	

##### Step 3
Back up the *opencats* database by issuing the following command at  your shell prompt:

	# mysqldump -uroot -p cats > ~/cats-backup.sql

(enter the password you created for the 'opencats' user during install when prompted to do so)

Please note that this backup can not be restored by the interactive OpenCATS installer - it is a failsafe in case the upgrade fails and the database becomes corrupt. If you later need to restore the database from this backup, you can issue the command:

	# mysql -uroot -p cats < ~/cats-backup.sql

##### Step 4
Copy the attachments/ directory from opencats-old/ to opencats/:

Linux:

	# cp -p -r opencats-old/attachments/ opencats/

FreeBSD:

	# cp -p -R opencats-old/attachments/ opencats/

##### Step 5
Change ownership of the installation directory to the user and group that your web server runs under. This is usually 'apache', 'nobody', or 'www' (you can do a ps -auxww to see what user your web server is running as).

	# chown apache:apache opencats
    # chown -R apache:apache opencats-0.9.1/

If selinux is enabled run the following command:

	# chcon -R -t httpd_sys_rw_content_t opencats -R

##### Step 6
In a web browser, visit the CATS installer page inside the cats web directory to finish the installation process: *

        http://localhost/opencats/installwizard.php

(Replace 'localhost' with your domain name, or the ip address of your server)

*Tip: If the installer does not load, check to see if there is a file called 'INSTALL_BLOCK' in the CATS directory. Delete it to allow the installer to be executed.*

##### Step 7
Follow the installer directions to complete the installation. Your database schema will be upgraded automatically.

If any tests do not pass, check your configuration and requirements fulfillment and refresh the page (hold down shift while refreshing for Firefox and IE to ensure a refresh). You may visit the forum on http://forums.opencats.org for support.

##### Step 8
OpenCATS should now be up and running. Enjoy! Please visit https://github.com/opencats if you wish to contribute to OpenCATS

### Windows

##### Step 1 
Open tarball (cats-0.9.1.tar.gz) and extract all files to c:\wamp\www (or your web server's document root folder).

##### Step 2
Launch phpMyAdmin by clicking on the WAMP icon in your system tray and selecting phpMyAdmin.  A web browser will open. On the dropdown menu under Database on the left, Choose 'opencats'. Select the 'Export' tab.

Check 'Save as File' at the bottom of the window and press 'Go'.  Download and put the resulting SQL file somewhere safe on your computer. You will need this file to restore your database in the event something goes wrong during the upgrade process.

##### Step 3 
Copy the attachments folder from your previous installation of OpenCATS into your new installation of OpenCATS. For example, if you installed OpenCATS 0.8.0 in c:\wamp\www\cats-0.8.0, move c:\wamp\www\cats-0.8.0\attachments to c:\wamp\www\cats-0.9.1\attachments.

##### Step 4 
In a Web Browser, visit http://localhost/opencats.  If OpenCATS has been configured correctly, you should see a page that says:

    OpenCATS has not yet been installed, or a previous installation was not completed.
    Please visit the Installation Wizard to continue.

Follow the link to the Installation Wizard to complete installation.

## Advanced CATS Add-ons

For advanced users, CATS has a few special features which can be manually installed.

### Sphinx Indexing
Sphinx Indexing [ http://www.sphinxsearch.com/ ]:

OpenCATS can integrate with the advanced Sphinx to dramatically improve the speed which indexed documents are searched (more than 200x speed improvement).  To learn how to integrate with the Sphinx engine, visit the CATS forums at http://forums.opencats.org.

### Scheduled Email reminders
OpenCATS can send out Email reminders for calendar events before they happen. To enable this feature, configure cron or another scheduling daemon to invoke QueueCLI.php every minute.  An example crontab line would look like:

    * * * * * /usr/local/bin/php /var/www/html/cats/QueueCLI.php

OR

    * * * * * curl http://mysite.com/QueueCLI.php > /dev/null

### CLI / On demand backups
CLI / On demand backups OpenCATS can generate a backup from the command line on Unix systems.  If the Unix zip utility is installed, then you can execute:

    php scripts/makeBackup.php 1

from the OpenCATS root directory to generate a backup in scrips/backup/catsbackup.bak. This, combined with a script to rotate backups which was executed from cron, could yield automated backups.

## Integration with Sphinx

As of OpenCATS 0.9.1 Sphinx integration is available via separate package. Sphinx speeds up text-based database searches considerably.

Download URL:

    http://downloads.opencats.org/sphinx_for_cats.tar.gz

## Enable LDAP Authentication

Make sure that you are logged in to OpenCATS using an Administrator account before you perform any configuration changes for LDAP. Do not logout of the system unless you are done with the configuration changes. Otherwise you might be locked out of the system if there are any issues with your LDAP configuration.

The following instructions are tested in OpenLDAP and ActiveDirectory (AD).

The user list is still maintained in the mysql table, but the authentication request will be directed to the ldap database. To create the user it is enough to attempt to login with LDAP credentials for the first time and the user will be created in the database if the authentication through LDAP is successful. The user information in mysql table will be populated with information from LDAP. Password is not stored in mysql database.
New LDAP user will need to wait to be approved by system administrator before being able to log into the system. 

Edit config.php and modify the following parameters:

| Parameter | Value example | Comment |
| --------- | ------ | ------- |
| AUTH_MODE | `ldap` or `sql+ldap` | if mode is `sql+ldap` it is also possible to log in with mysql-only user and also to create mysql-only users. |
| LDAP_HOST | `ldap.example.com` or `ldaps://ldap.example.com` | host name of LDAP server |
| LDAP_PORT | `389` or `636` | port of LDAP server |
| LDAP_PROTOCOL_VERSION | 3 | version of LDAP protocol, ususaly 3 or 2 (for AD use 3) |
| LDAP_BASEDN | `dc=example,dc=com` or `OU=Employees,DC=example,DC=com` | baseDN in LDAP or in active directory |
| LDAP_BIND_DN | `cn=read-only-admin,dc=example,dc=com` | bind DN of technical user to be used for initial bind; if not defined (is empty), username provided is used to bind |
| LDAP_BIND_PASSWORD | `password` | password of technical user for initial bind; can be skipped if LDAP_BIND_DN not defined |
| LDAP_ACCOUNT | `domain\\{$username}` or `{$username}@example.com` | if initial bind is done using logged in user, this is form of string to be used as username for bind; `{$username}` is replaced with provided username during login | 
| LDAP_ATTRIBUTE_UID | `uid` or `sAMAccountName` | name of attribute for user id; Used to fill in Username of user in DB and also in search for user in LDAP based on Username |
| LDAP_ATTRIBUTE_DN | `dn` or `distingushedname` | name of attribute for distinguished name |
| LDAP_ATTRIBUTE_LASTNAME | `sn` | name of attribute for last name / surname; Used to fill in Last Name of user in DB |
| LDAP_ATTRIBUTE_FIRSTNAME | `givenname` | name of attribute for first name / given name; Used to fill in First Name of user|
| LDAP_ATTRIBUTE_EMAIL | `mail` or `email` | name of attribute for email address;; Used to fill in Email Address of user |
| LDAP_SITEID | 1 | ID of site where user is created (only one site is supported for user) |

### Example of configuration for OpenLDAP

```php
define ('LDAP_HOST', 'ldap.forumsys.com');
define ('LDAP_PORT', '389');
define ('LDAP_PROTOCOL_VERSION', 3);
define ('LDAP_BASEDN', 'dc=example,dc=com');
define ('LDAP_BIND_DN', 'cn=read-only-admin,dc=example,dc=com');
define ('LDAP_BIND_PASSWORD', 'password');

define ('LDAP_ATTRIBUTE_UID', 'uid');
define ('LDAP_ATTRIBUTE_DN', 'dn');
define ('LDAP_ATTRIBUTE_LASTNAME', 'sn');
define ('LDAP_ATTRIBUTE_FIRSTNAME', 'givenname');
define ('LDAP_ATTRIBUTE_EMAIL', 'mail');

define ('LDAP_SITEID', 1);
```

### Example of configuration for Active Directory
```php
define ('LDAP_HOST', 'ldaps://ldap.example.com');
define ('LDAP_PORT', '636');
define ('LDAP_PROTOCOL_VERSION', 3);

define ('LDAP_BASEDN', 'OU=Employees,DC=example,DC=com');
define ('LDAP_BIND_DN', '');
define ('LDAP_ACCOUNT', '{$username}@example.com');

define ('LDAP_ATTRIBUTE_UID', 'sAMAccountName');
define ('LDAP_ATTRIBUTE_DN', 'distinguishedname');
define ('LDAP_ATTRIBUTE_LASTNAME', 'sn');
define ('LDAP_ATTRIBUTE_FIRSTNAME', 'givenname');
define ('LDAP_ATTRIBUTE_EMAIL', 'mail');

define ('LDAP_SITEID', 1);
```
