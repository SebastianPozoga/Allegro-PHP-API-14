<?php

class AllegroApi {

    private $_client = NULL;
    private $_session = NULL;
    private $_versionKeys = array();

    function __construct() {
        //init soap client
        $options = array();
        $options['features'] = SOAP_SINGLE_ELEMENT_ARRAYS;
        $this->_client = new SoapClient('https://webapi.allegro.pl/service.php?wsdl', $options);
        $request = array(
            'countryId' => ALLEGRO_COUNTRY_CODE,
            'webapiKey' => ALLEGRO_KEY
        );
        //init main data
        $sys = $this->_client->doQueryAllSysStatus($request);
        $this->_versionKeys = array();
        foreach ($sys->sysCountryStatus->item as $row) {
            $this->_versionKeys[$row->countryId] = $row;
        }
    }

    function loginEnc() {
        $request = array(
            'userLogin' => ALLEGRO_LOGIN,
            'userHashPassword' => WEBAPI_USER_ENCODED_PASSWORD,
            'countryCode' => ALLEGRO_COUNTRY_CODE,
            'webapiKey' => ALLEGRO_KEY,
            'localVersion' => $this->_versionKeys[ALLEGRO_COUNTRY_CODE]->verKey,
        );
        $this->_session = $this->_client->doLoginEnc($request);
    }

    function __call($name, $arguments) {
        $arguments = (array) $arguments[0];
        $arguments['sessionId'] = $this->_session->sessionHandlePart;
        $arguments['webapiKey'] = ALLEGRO_KEY;
        $arguments['countryId'] = ALLEGRO_COUNTRY_CODE;
        $fname = 'do'.ucfirst($name);
        return $this->_client->$fname($arguments);
    }
    
}
