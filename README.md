# SilverTwit

A social networking platform in SilverStripe

## Getting Started

### Installation

* Clone the repository
* If you have phing, just run `phing` to create all the default files needed and skip the following bits
* Otherwise, you'll need to copy the following files manually
  * build/configs/silverstripe/local.conf.sample.php to mysite/local.conf.php
  * build/configs/silverstripe/htaccess.sample to ./.htaccess (note the extra . there)
* Edit mysite/local.conf.php to reflect appropriate DB settings. The following should work for SQLite

<pre>


    global $databaseConfig;
    $databaseConfig = array(
            "type" => "SQLiteDatabase",
            "server" => "localhost",
            "username" => "",
            "password" => "",
            "database" => "silverstripe",
    );


    Security::setDefaultAdmin('admin', 'admin');
    // Email::setAdminEmail('admin@example.org');
    define('SS_LOG_FILE', dirname(__FILE__).'/'.basename(dirname(dirname(__FILE__))).'.log');
</pre>

* Edit mysite/local.conf.php and update the 'defaultAdmin' settings 
* Create the assets/ directory
* Create an _ss_environment.php file in the root folder with the following

<pre>

    // Set the $_FILE_MAPPING for running the test cases, it's basically a fake but useful
    global $_FILE_TO_URL_MAPPING;
    $_FILE_TO_URL_MAPPING[dirname(__FILE__)] = 'http://localhost';
</pre>

* Run dev/build : note that you will NEED to run with '?disable_perms=1' as a parameter to make sure the restrictedobjects module doesn't interfere with things
* All done!

### Configuration

A few things will need to be created first, so login to /admin

* Go to Security
* Create a "Members" group
* Go to Settings, and select the permissions tab. 
* Add Access Authority -> for Permission select View, for Groups select "Members", click Create
* Go to Pages
* Add new -> Site Dashboard Page -> set name = Site
* On the Home page, change type to "Redirector page" and make it redirect to the 'Site' page 
* Remove other pages from the menu
* Add new -> MemberProfilePage -> set name = Profile, just use the default registration options
