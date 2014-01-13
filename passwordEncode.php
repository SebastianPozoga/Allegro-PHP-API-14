<?php

define('ALLEGRO_PASSWORD', 'Twoje hasło');
echo base64_encode(hash('sha256', ALLEGRO_PASSWORD, true));