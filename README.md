# PHP Dialogflow API

A working [PHP Dialogflow V2 API](https://dialogflow.com/docs/reference/v2-agent-setup) example using the DetectIntent method ([`SessionsClient::detectIntent()`](https://github.com/googleapis/google-cloud-php-dialogflow)).

## Requirements

* [Dialogflow Agent with active V2 API](https://dialogflow.com/docs/reference/v2-agent-setup)
* [Google Account Credentials file](https://cloud.google.com/docs/authentication/production)
* [PHP 7.x](http://php.net/downloads.php)
* [Composer](https://getcomposer.org/)

## How to

* Clone repo `git clone https://bitbucket.org/labonte/php-dialogflow-api.git`
* Enter directory `cd php-dialogflow-api`
* Use .env file or .htaccess file for configuration. Appropriate file templates can be found in the .env.dist and .htaccess.dist files.

### For local testing (using a UNIX based OS)
* Create .env `cp .env.dist .env`
* Edit .env with your favorite editor (eg. `vim .env`)
* Run `composer install` 
* Run `php -S 127.0.0.1:8080 -t .`

### For "production" use (Using apache with mod_rewrite)
* Copy .htaccess `cp .htaccess.dist .htaccess`
* Edit .htaccess with your favorite editor (eg. `vim .htaccess`)
* Run `composer install --no-dev`
* Copy the following files and folders to your webserver
    * .htaccess (you have to create it first!)
    * DetectIntent.php
    * lib.php
    * vendor/
* **According to the .htaccess you must place your Google Account Credentials file in a save place on your server. It's on you to ensure the file is not accessible from the web!**

### For use with nginx
Take a look into the .htaccess.dist file. You have to reproduce the those statements. The resulting config for your nginx location section should look something like this:
```
location /DetectIntent {    
    # Fill in!!!
    # Supply your Google Project ID and path to the credentials file.
    fastcgi_param GOOGLE_PROJECT_ID [YOUR PROJECT ID]
    fastcgi_param GOOGLE_APPLICATION_CREDENTIALS [YOUR GOOGLE APPLICATION CREDENTIALS FILE PATH]
    
    # Optionally change the maximum nesting level of your custom payload objects.
    fastcgi_param FULFILLMENT_MESSAGE_MAX_NESTING 64
    
    fastcgi_pass php;
    fastcgi_index DetectIntent.php;
    ...
}
```
I have not tested this!! The php upstream config is up to you.
