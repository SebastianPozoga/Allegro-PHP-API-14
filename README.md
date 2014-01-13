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
  // <- country 1 = poland (more)
  // more country code by (PL)
  // http://allegro.pl/webapi/documentation.php/show/id,25#method-input
`````

3. Show example result by index.php

  https://github.com/SebastianPozoga/Allegro-PHP-API-14/blob/master/index.php
  

Links
=====

* Allegro API function list: 

http://allegro.pl/webapi/documentation.php

* Base (older) version:

https://code.google.com/p/allegrowebapi-php-class/
