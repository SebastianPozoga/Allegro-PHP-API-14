<?php

//Hasła w osobnym pliku
// po co ktoś się ma gapić na nasz sha ;)
// PAMIĘTAJ DODAĆ SWOJE DANE
require 'passwords.php';

//Nasza biblioteka
require_once 'Allegro-PHP-API-14.php';

//Przykład:
try {
    
    //Utworzenie obiektu Allegro
    $allegro = new AllegroWebAPI();
    
    //Bezpieczne logowanie
    $allegro->LoginEnc();

    //Przygotowanie danych
    $dosearch_request = array(
        'search-string' => 'ipad',
        'search-options' => 136,
        'search-order' => 1,
        'search-order-type' => 1,
        'search-country' => 0,
        'search-category' => 0,
        'search-offset' => 0,
        'search-city' => '',
        'search-state' => NULL,
        'search-price-from' => 0.00,
        'search-price-to' => 30000.00,
        'search-limit' => 1,
        'search-order-fulfillment-time' => 96,
        'search-user' => NULL
    );

    //akacja doSearch
    $cats_list = $allegro->Search($dosearch_request);

    //wyświetlenie rezultatu
    echo "<pre>";
    print_r($cats_list);
    echo "</pre>";
} catch (SoapFault $fault) {
    print($fault->faultstring);
}