# SnowTricks

Creating of a collaborative snowboard site based on the Symfony framwork.

------------------------------------------------------------------------------------------------------------------------------------------

## Codacy Badge
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/67bb4f306de2474e8f4e34cafb0fa46b)](https://www.codacy.com/manual/Scratchy-Show/SnowTricks?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Scratchy-Show/SnowTricks&amp;utm_campaign=Badge_Grade)

------------------------------------------------------------------------------------------------------------------------------------------
## Environment used for development
  * Symfony 4.14.3
  * Composer 1.9.1
  * Bootstrap 4.4.1
  * jQuery 3.4.1
  * PHPUnit 9.1.4
  * Xdebug 2.9.5
  * Wampserver 3.2.0
    - PHP 7.4.1
    - Apache 2.4.41
    - MySQL 8.0.18
    
------------------------------------------------------------------------------------------------------------------------------------------

## Install the project
1. Download and install WampServer (or equivalent: MampServer, XampServer, LampServer).

2. Download the project clone in the www folder of WampServer :
```
git clone https://github.com/Scratchy-Show/SnowTricks.git
```

3. **Install the dependencies** - In the root directory of the project, open the CLI (Command-Line Interface) and execute the command :
```
composer install
```

 4. **Create the database** - Still in the root folder, run the command :
 ```
php bin/console doctrine:database:create
```

5.

