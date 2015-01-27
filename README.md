This version is deprecated
==========================

A new version of the library is available on https://github.com/SebastianPozoga/PHP-AllegroApi
New version is re-write, better tested and more flexible for allegro changes. You can use the newest allegro function. I recommend use it.

Allegro-PHP-API-14
==================

PHP Allegro API - provide default interface to access the allegro API and new secure login system (based on sha256)


Example
=======

1. Use passwordEncode.php to encode your password 

`````php
  define('ALLEGRO_PASSWORD', 'Twoje hasło');
  echo base64_encode(hash('sha256', ALLEGRO_PASSWORD, true));
`````

2. Add your user, password and country with php define

`````php
  define('ALLEGRO_ID', '1');
  define('ALLEGRO_LOGIN', 'Your login');
  define('ALLEGRO_PASSWORD', 'Your password encoded by sha256');
  define('ALLEGRO_KEY', 'Your api key');
  define('ALLEGRO_COUNTRY', '1'); 
  // <- country 1 = poland
  // more country code:
  // http://allegro.pl/webapi/documentation.php/show/id,25#method-input  (PL)
`````

3. Show example result by index.php

  https://github.com/SebastianPozoga/Allegro-PHP-API-14/blob/master/index.php
  

Links
=====

* Allegro API function list: 

http://allegro.pl/webapi/documentation.php

* Base (older) version:

https://code.google.com/p/allegrowebapi-php-class/
