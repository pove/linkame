# Linkame Rest Api Application

Rest API with [Slim Framework 3](http://www.slimframework.com/) and [ReadBeanPHP 4](http://www.redbeanphp.com/index.php)

## Install the Application

Run this command from the directory

    php composer.phar install

Or if you have Composer available

    composer install


* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` is web writeable.

## Create database

Although RedBeanPHP could create the necessaries columns when `freeze` is set to false, it is recomended to use the database creation script `src/scripts/linkamedb.sql`.

## Settings setup

Go to `src/settings.php` file and set all the parameters needed.

**Most important settings:**
- `production`: to switch between development and production settings.
- `redbean.dev` or `redbean.prod`: depending on production switch, set the database parameters here.
  - `freeze`: to switch between [Fluid and Frozen RedBeanPHP](http://www.redbeanphp.com/index.php?p=/fluid_and_frozen) modes.
- `security`
  - `usedevices`: if disabled, web service handles links like a big public box; all mobile devices ([linkame app](https://github.com/pove/linkame.app)) can add links here, and all devices can show these links.
  
    If this setting is enabled, it creates a unique device identifier the first time you access the public `/` web service route from a web browser. Then it will be necessary to register the devices id from the mobile app ([linkame app](https://github.com/pove/linkame.app)).
  - `ekey` and `akey`: 32 bytes key used to encrypt and decrypt all the information stored on the database when the `usedevices` setting is enabled.

## Routes
- GET `/`: main route to render the webpage to show links on the web browser. When `usedevices` is enabled, it creates a new device using the ip and storing an encrypted key on browser cookies.
- GET `/links[/{device}]`: get all links. When `usedevices` is enabled, {device} param is needed.
- POST `/link[/{device}]`: post a new link. When `usedevices` is enabled, {device} param is needed.
- DELETE `/link/{id}[/{device}]`: delete the link with this {id}. When `usedevices` is enabled, {device} param is needed.
- GET `/device/{deviceid}`: get device information to connect from the app when `usedevices` is enabled.
