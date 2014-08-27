<?php

/* * *************************************************************************************************************
 * Allegro-PHP-API-14.php
 *
 * Wykonany przez: 
 * Sebastian Pożoga
 * (Hostowany na: https://github.com/SebastianPozoga/Allegro-PHP-API-14 )
 * 
 * Klasa rozpowszechcniana na licencji Creative Commons 3.0 BY-SA
 * Stanowi modernizację i utwór pochodny allegrowebapi-php-class v1.1
 * wykonanego przez:
 * NLDS-Group - Marketing & Promotion Agency
 * 
 * Utwór bazowy dostępny pod adresem:
 * https://code.google.com/p/allegrowebapi-php-class/
 * 
 * Biblioteka pozwala na łatwy dostęp do Allegro API
 * Więcej na http://allegro.pl/webapi
  + *************************************************************************************************************
 * WARUNKI KORZYSTANIA Z KLASY Allegro-PHP-API-14.PHP
  + *************************************************************************************************************
 *
 *   Wolno:
 * ---------------------------------
 * - kopiować, rozpowszechniać, odtwarzać i wykonywać utwór
 * - tworzyć utwory zależne
 *
 *
 *   Na następujących warunkach:
 * ---------------------------------
 * - Uznanie autorstwa - Utwór należy oznaczyć w sposób określony przez Twórcę lub Licencjodawcę.
 * - Na tych samych warunkach - Jeśli zmienia się lub przekształca niniejszy utwór, lub tworzy inny na jego podstawie,
 *   można rozpowszechniać powstały w ten sposób nowy utwór tylko na podstawie takiej samej licencji.
 *
 *  Inne prawa - Licencja nie wpływa w żaden sposób na następujące prawa:
 * ---------------------------------
 *  * Uprawnienia wynikające z dozwolonego użytku ani innych obowiązujących ograniczeń lub wyjątków prawa autorskiego.
 *     http://wiki.creativecommons.org/Frequently_Asked_Questions#Do_Creative_Commons_licenses_affect_fair_use.2C_fair_dealing_or_other_exceptions_to_copyright.3F
 *  * Autorskie prawa osobiste autora;
 *     http://wiki.creativecommons.org/Frequently_Asked_Questions#I_don.E2.80.99t_like_the_way_a_person_has_used_my_work_in_a_derivative_work_or_included_it_in_a_collective_work.3B_what_can_I_do.3F
 *  * Ewentualne prawa osób trzecich do utworu lub sposobu wykorzystania utworu, takie jak prawo do wizerunku lub prawo do prywatności.
 *     http://wiki.creativecommons.org/Frequently_Asked_Questions#When_are_publicity_rights_relevant.3F
 *
 *
  + *************************************************************************************************************
 * Uwaga - W celu ponownego użycia utworu lub rozpowszechniania utworu należy wyjaśnić innym warunki licencji, na której udostępnia się utwór.
  + *************************************************************************************************************
 *
 * Klasa do obsługi Allegro.pl
 *
 * Dokumentacja oraz szczegółowe opisy
 * metod dostępnych w WebAPI: http://allegro.pl/webapi/
 *
 * 
  + *************************************************************************************************************
 * Tutorial:
  + *************************************************************************************************************
 *
 * Dodano nowy system logowania, pozwalającym na zakodowanie hasła:
 * 
 * 1. Po pierwsze kodujemy nasze hasło. Np za pomocą skryptu:
 * 
 * $zakodowane_haslo = base64_encode(hash('sha256', $this->_config['allegro_password'], true));
 * echo $zakodowane_haslo;
 * 
 * 2. Ustawiam nasze zakodowane hasło:
 * 
 * define('ALLEGRO_ID', '1');
 * define('ALLEGRO_LOGIN', 'Użytkownik');
 * define('ALLEGRO_PASSWORD', 'Zakodowane hasło');
 * define('ALLEGRO_KEY', 'Twój klucz Allegro');
 * define('ALLEGRO_COUNTRY', '1');
 * 
 * 
 * try {
 * 		$allegro = new AllegroWebAPI();
 * 		$allegro->LoginEnc();
 * 		$cats_list = $allegro->GetCatsData();
 * 		print_r($allegro->objectToArray($cats_list));
 * }
 * catch(SoapFault $fault) {
 * 		print($fault->faultstring);
 * }
 * 
 * 
  + *************************************************************************************************************
 * Zmiany:
  + *************************************************************************************************************
 *
 * - Dodanie szyfrowania haseł.
 * - Ustawienie starej funkcji jako @deprecated (Zawsze szyfruj hasła)
 * - Usunięcie już niewspieranych funkcji:
 *      - FindProductByName
 *      - FindProductByCode
 * - Dodanie funkcji
 *      - GetItemsList
 * 
 * 
  +************************************************************************************************************ */

class AllegroWebAPI {

    protected $_instance;
    protected $_config;
    protected $_session;
    protected $_client;
    protected $_local_version;

    /* Określenie kraju (1 = Polska) */

    const COUNTRY_CODE = ALLEGRO_COUNTRY;

    /**
     * Zapis ustawień oraz połączenie z WebAPI
     */
    public function __construct() {
        $this->_config = array(
            'allegro_id' => ALLEGRO_ID,
            'allegro_key' => ALLEGRO_KEY,
            'allegro_login' => ALLEGRO_LOGIN,
            'allegro_password' => ALLEGRO_PASSWORD
        );

        $this->_client = new SoapClient('https://webapi.allegro.pl/uploader.php?wsdl');
    }

    /*     * ********************************************************************************************************
     * Czarna lista (http://allegro.pl/webapi/documentation.php/theme/id,21)
     * ******************************************************************************************************* */

    /**
     *
     * Metoda pozwala na dodanie wskazanych użytkowników do czarnej listy zalogowanego użytkownika.
     * Użytkownicy dodani do czarnej listy nie mogą kupować żadnych przedmiotów od użytkownika,
     * na którego czarnej liście się znajdują.
     * (http://allegro.pl/webapi/documentation.php/show/id,21)
     *
     * @param array $Users
     * @return array
     */
    public function AddToBlackList($Users) {
        $this->checkConnection();
        return $this->_client->doAddToBlackList(
                        $this->_session['session-handle-part'], $Users
        );
    }

    /**
     * Metoda pozwala na pobranie listy użytkowników, którzy znajdują się na czarnej liście zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,45)
     *
     * @return array
     */
    public function GetBlackListUsers() {
        $this->checkConnection();
        return $this->_client->doGetBlackListUsers(
                        $this->_session['session-handle-part']
        );
    }

    /**
     * Metoda pozwala na usunięcie wskazanych użytkowników z czarnej listy zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,114)
     *
     * @param array $Users
     * @return array
     */
    public function RemoveFromBlackList($Users) {
        $this->checkConnection();
        return $this->_client->doRemoveFromBlackList(
                        $this->_session['session-handle-part'], $Users
        );
    }

    /*     * ********************************************************************************************************
     * Dane kontrahentów (http://allegro.pl/webapi/documentation.php/theme/id,67)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie pełnych danych kontaktowych kontrahentów z danej aukcji.
     * Metoda zwraca różne dane - w zależności od tego, czy zalogowany użytkownik był sprzedającym (user-data, user-sent-to-data),
     * czy kupującym (user-data, user-bank-accounts, company-second-address) w danej aukcji.
     * W przypadku podania niepoprawnego identyfikatora aukcji, zostanie dla niego zwrócona pusta struktura.
     * (http://allegro.pl/webapi/documentation.php/show/id,89)
     *
     * @param array $Options
     * @return array
     */
    public function GetPostBuyData($Options) {
        $this->checkConnection();
        return $this->_client->doGetPostBuyData(
                        $this->_session['session-handle-part'], $Options
        );
    }

    /**
     * Metoda pozwala na pobranie wszystkich danych z wypełnionych przez kupujących formularzy pozakupowych.
     * Metoda zwraca także szczegółowe informacje dot. płatności (realizowanych przez PzA),
     * powiązanych ze wskazanymi transakcjami, informacje nt. wybranego punktu odbioru oraz dane identyfikacyjne
     * dot. przesyłki zawierającej produkty składające się na wskazane transakcje.
     * (http://allegro.pl/webapi/documentation.php/show/id,141)
     *
     * @param array $Options
     * @return array
     */
    public function GetPostBuyFormsData($Options) {
        $this->checkConnection();
        return $this->objectToArray($this->_client->doGetPostBuyFormsData(
                                $this->_session['session-handle-part'], $Options
        ));
    }

    /**
     * Metoda pozwala na pobranie wszystkich danych z wypełnionych przez kupujących (gdy metodę wywołuje sprzedający)
     * lub zalogowanego użytkownika (gdy metodę wywołuje kupujący) Formularzy Opcji Dostawy.
     * W przypadku gdy dla danej aukcji nie został wypełniony FOD - zwracana jest pusta struktura.
     * (http://allegro.pl/webapi/documentation.php/show/id,96)
     *
     * @param array $Options
     * @return array
     */
    public function GetShipmentOptionsFormData($Options) {
        $this->checkConnection();
        return $this->_client->doGetShipmentOptionsFormData(
                        $this->_session['session-handle-part'], $Options['sof-user-type'], $Options['sof-items-id']
        );
    }

    /**
     * Metoda pozwala na pobranie wartości identyfikatorów transakcji (zakupów sfinalizowanych wypełnieniem formularza
     * pozakupowego przez kupującego) na podstawie przekazanych identyfikatorów aukcji. Uzyskane identyfikatory
     * transakcji mogą być następnie wykorzystane np. do pobierania wypełnionych formularzy pozakupowych za pomocą
     * metody doGetPostBuyFormsData. Metoda zwraca jedynie identyfikatory transakcji,
     * dla których - w ramach danej aukcji - wypełnione zostały przez kupujących formularze pozakupowe
     * (http://allegro.pl/webapi/documentation.php/show/id,121)
     *
     * @param array $Options
     * @return array
     */
    public function GetTransactionsIDs($Options) {
        $this->checkConnection();
        return $this->_client->doGetTransactionsIDs(
                        $this->_session['session-handle-part'], $Options['items-id-array'], $Options['user-role']
        );
    }

    /**
     * Metoda pozwala na pobranie danych kontaktowych kupujących w aukcjach zalogowanego użytkownika.
     * W przypadku podania błędnego identyfikatora aukcji, struktura jej odpowiadająca nie zostanie zwrócona.
     * (http://allegro.pl/webapi/documentation.php/show/id,110)
     *
     * @param array $Options
     * @return array
     */
    public function MyContact($Options) {
        $this->checkConnection();
        return $this->_client->doMyContact(
                        $this->_session['session-handle-part'], $Options['auction-id-list'], $Options['offset']
        );
    }

    /*     * ********************************************************************************************************
     * Drzewo kategorii (http://allegro.pl/webapi/documentation.php/theme/id,43)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie pełnego drzewa kategorii dostępnych we wskazanym kraju.
     * (http://allegro.pl/webapi/documentation.php/show/id,46)
     *
     * @return array
     */
    public function GetCatsData() {
        return $this->_client->doGetCatsData(
                        self::COUNTRY_CODE, '0', $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie licznika kategorii dostępnych we wskazanym kraju.
     * (http://allegro.pl/webapi/documentation.php/show/id,47)
     *
     * @return array
     */
    public function GetCatsDataCount() {
        return $this->_client->doGetCatsDataCount(
                        self::COUNTRY_CODE, '0', $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie w porcjach pełnego drzewa kategorii dostępnych we wskazanym kraju.
     * Domyślnie zwracanych jest 50 pierwszych kategorii. Rozmiar porcji pozwala regulować parametr package-element,
     * a sterowanie pobieraniem kolejnych porcji danych umożliwia parametr offset.
     * (http://allegro.pl/webapi/documentation.php/show/id,48)
     *
     * @param array $Options
     * @return array
     */
    public function GetCatsDataLimit($Options) {
        return $this->_client->doGetCatsDataLimit(
                        self::COUNTRY_CODE, '0', $this->_config['allegro_key'], $Options['offset'], $Options['package-element']
        );
    }

    /*     * ********************************************************************************************************
     * Dziennik zdarzeń (http://allegro.pl/webapi/documentation.php/theme/id,63)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie informacji z dziennika zdarzeń na temat zmian stanów (rozpoczęcie,
     * zakończenie, złożenie oferty w aukcji z licytacją, zakup przez Kup Teraz!, zmiana w opisie)
     * aukcji zalogowanego użytkownika lub wszystkich aukcji w serwisie. Zwracanych jest zawsze
     * 100 najnowszych informacji o zmianach (zaczynając od punktu podanego w parametrze starting-point),
     * posortowanych rosnąco po czasie ich wystąpienia. W przypadku przekazania w parametrze starting-point
     * wartości 0, zwróconych zostanie 100 chronologicznie najwcześniejszych zmian, do których dostęp ma
     * jeszcze dziennik zdarzeń (zazwyczaj są to dane z ostatnich 8-9 dni). Aby sterować pobieraniem kolejnych
     * porcji danych (tak aby dotrzeć do danych najświeższych), należy w parametrze starting-point przekazywać
     * wartość row-id ostatniego (setnego) elementu, zwracanego w ramach danego wywołania i robić to sukcesywnie,
     * dopóki w wyniku wywołania nie otrzyma się porcji danych mniejszej niż 100 elementów (co będzie świadczyło,
     * że otrzymane dane są danymi najświeższymi).
     * (http://allegro.pl/webapi/documentation.php/show/id,65)
     *
     * @param array $Options
     * @return array
     */
    public function GetSiteJournal($Options) {
        $this->checkConnection();
        return $this->_client->doGetSiteJournal(
                        $this->_session['session-handle-part'], $Options['starting-point'], $Options['info-type']
        );
    }

    /**
     * Metoda pozwala na pobranie informacji z dziennika zdarzeń na temat liczby zmian w aukcjach zalogowanego
     * użytkownika lub we wszystkich aukcjach w serwisie, od zdefiniowanego (w parametrze starting-point)
     * momentu (bierze po uwagę chronologicznie najstarsze 10000 aukcji - zaczynając od podanego punktu startu).
     * Aby sterować momentem rozpoczęcia pobierania informacji o liczbie zmian (tak aby dotrzeć do danych najświeższych),
     * należy w parametrze starting-point przekazywać odpowiednią wartość row-id,
     * zwracaną w ramach wywołania metody doGetSiteJournal.
     * (http://allegro.pl/webapi/documentation.php/show/id,66)
     *
     * @param array $Options
     * @return array
     */
    public function GetSiteJournalInfo($Options) {
        $this->checkConnection();
        return $this->_client->doGetSiteJournalInfo(
                        $this->_session['session-handle-part'], $Options['starting-point'], $Options['info-type']
        );
    }

    /*     * ********************************************************************************************************
     * Informacje o użytkowniku (http://allegro.pl/webapi/documentation.php/theme/id,64)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie prywatnych danych (wraz z dodatkowymi danymi dla konta Firma)
     * z konta zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,84)
     *
     * @return array
     */
    public function GetMyData() {
        $this->checkConnection();
        return $this->_client->doGetMyData(
                        $this->_session['session-handle-part']
        );
    }

    /**
     * Metoda pozwala na sprawdzenie identyfikatora użytkownika za pomocą jego nazwy.
     * (http://allegro.pl/webapi/documentation.php/show/id,102)
     *
     * @param string $Username
     * @return array
     */
    public function GetUserID($Username) {
        return $this->_client->doGetUserID(
                        self::COUNTRY_CODE, $Username, '', $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie listingu wszystkich aukcji wystawianych obecnie przez danego użytkownika.
     * Domyślnie zwracanych jest 25 aukcji posortowanych rosnąco po czasie zakończenia. Rozmiar porcji
     * pozwala regulować parametr limit, a sterowanie pobieraniem kolejnych porcji danych umożliwia parametr offset.
     * (http://allegro.pl/webapi/documentation.php/show/id,103)
     *
     * @param array $Options
     * @return array
     */
    public function GetUserItems($Options) {
        return $this->_client->doGetUserItems(
                        $this->_config['allegro_id'], $this->_config['allegro_key'], self::COUNTRY_CODE, $Options['offset'], $Options['limit']
        );
    }

    /**
     * Metoda pozwala na sprawdzenie nazwy użytkownika za pomocą jego identyfikatora.
     * (http://allegro.pl/webapi/documentation.php/show/id,104)
     *
     * @param int $UserID
     * @return array
     */
    public function GetUserLogin($UserID) {
        return $this->_client->doGetUserLogin(
                        self::COUNTRY_CODE, $UserID, $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie publicznie dostępnych informacji o dowolnym użytkowniku serwisu.
     * Użytkownik może być wskazany za pomocą jego identyfikatora lub nazwy - w przypadku przekazania
     * wartości w obu wymienionych parametrach, zwrócone zostaną informacje o użytkowniku wskazanym w parametrze user-id.
     * (http://allegro.pl/webapi/documentation.php/show/id,341)
     *
     * @param array $Options
     * @return array
     */
    public function ShowUser($Options) {
        return $this->_client->doShowUser(
                        $this->_config['allegro_key'], self::COUNTRY_CODE, $Options['user-id'], $Options['user-login']
        );
    }

    /**
     * Metoda pozwala na podgląd zawartości strony "O mnie" dowolnego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,302)
     *
     * @param int $Username
     * @return array
     */
    public function ShowUserPage($UserID) {
        return $this->_client->doShowUserPage(
                        $this->_config['allegro_key'], self::COUNTRY_CODE, $UserID
        );
    }

    /*     * ********************************************************************************************************
     * Komentarze i ocena sprzedaży (http://allegro.pl/webapi/documentation.php/theme/id,42)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na wystawienie komentarza użytkownikowi będącemu stroną transakcji.
     * (http://allegro.pl/webapi/documentation.php/show/id,42)
     *
     * @param array $Options
     * @return array
     */
    public function Feedback($Options) {
        $this->checkConnection();
        return $this->_client->doFeedback(
                        $this->_session['session-handle-part'], $Options['fe-item-id'], $Options['fe-from-user-id'], $Options['fe-to-user-id'], $Options['fe-comment'], $Options['fe-comment-type'], $Options['fe-op'], $Options['fe-rating']
        );
    }

    /**
     * Metoda pozwala na wystawienie wielu komentarzy na raz użytkownikom będącym stronami transakcji.
     * (http://allegro.pl/webapi/documentation.php/show/id,43)
     *
     * @param array $Options
     * @return array
     */
    public function FeedbackMany($Options) {
        $this->checkConnection();
        return $this->_client->doFeedbackMany(
                        $this->_session['session-handle-part'], $Options
        );
    }

    /**
     * Metoda pozwala na pobranie informacji o komentarzach dowolnego użytkownika. Domyślnie zwracane
     * są wszystkie komentarze (ew. ograniczone typem), posortowane malejąco po czasie ich dodania.
     * Miejsce rozpoczęcia pobierania listy komentarzy pozwala regulować parametr feedback-offset.
     * Należy podać identyfikator użytkownika tylko w jednym z parametrów: feedback-from lub feedback-to.
     * W pierwszym - gdy pobrane mają zostać informacje o komentarzach, które wskazany użytkownik wystawił.
     * W drugim - gdy pobrane mają zostać informacje o komentarzach, które wskazanemu użytkownikowi zostały wystawione.
     * (http://allegro.pl/webapi/documentation.php/show/id,51)
     *
     * @param array $Options
     * @return array
     */
    public function GetFeedback($Options) {
        $this->checkConnection();
        return $this->_client->doGetFeedback(
                        $this->_session['session-handle-part'], $Options['feedback-from'], $Options['feedback-to'], $Options['feedback-offset'], $Options['feedback-kind-list']
        );
    }

    /**
     * Metoda pozwala na pobranie szczegółowych informacji na temat oceny sprzedaży zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,422)
     *
     * @return array
     */
    public function GetMySellRating() {
        $this->checkConnection();
        return $this->_client->doGetMySellRating(
                        $this->_session['session-handle-part']
        );
    }

    /**
     * Metoda pozwala na pobranie listy powodów niezadowolenia z transakcji oraz listy obszarów podlegających ocenie sprzedaży.
     * (http://allegro.pl/webapi/documentation.php/show/id,442)
     *
     * @return array
     */
    public function GetMySellRatingReasons() {
        $this->checkConnection();
        return $this->_client->doGetMySellRatingReasons(
                        $this->_session['session-handle-part'], self::COUNTRY_CODE
        );
    }

    /**
     * Metoda pozwala na pobranie informacji o komentarzach oczekujących na wystawienie przez zalogowanego użytkownika.
     * Domyślnie zwracanych jest 25 elementów. Rozmiar porcji danych pozwala regulować parametr package-size,
     * a sterowanie pobieraniem kolejnych porcji umożliwia parametr offset.
     * (http://allegro.pl/webapi/documentation.php/show/id,105)
     *
     * @param array $Options
     * @return array
     */
    public function GetWaitingFeedbacks($Options) {
        $this->checkConnection();
        return $this->_client->doGetWaitingFeedbacks(
                        $this->_session['session-handle-part'], $Options['offset'], $Options['package-size']
        );
    }

    /**
     * Metoda pozwala na pobranie informacji o liczbie komentarzy oczekujących na wystawienie przez zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,106)
     *
     * @return array
     */
    public function GetWaitingFeedbacksCount() {
        $this->checkConnection();
        return $this->_client->doGetWaitingFeedbacksCount(
                        $this->_session['session-handle-part']
        );
    }

    /**
     * Metoda pozwala na pobranie informacji o komentarzach zalogowanego użytkownika. Domyślnie zwracanych jest 25 ostatnich
     * komentarzy (wystawionych lub otrzymanych), posortowanych malejąco po czasie ich dodania. Miejsce rozpoczęcia
     * pobierania listy komentarzy pozwala regulować parametr offset.
     * (http://allegro.pl/webapi/documentation.php/show/id,111)
     *
     * @param array $Options
     * @return array
     */
    public function MyFeedback2($Options) {
        $this->checkConnection();
        return $this->_client->doMyFeedback2(
                        $this->_session['session-handle-part'], $Options['feedback-type'], $Options['offset'], $Options['desc'], $Options['items-array']
        );
    }

    /**
     * Metoda pozwala na pobranie w porcjach informacji o komentarzach zalogowanego użytkownika.
     * Domyślnie zwracana jest lista wszystkich (wystawionych lub otrzymanych) komentarzy, posortowanych malejąco
     * po czasie ich dodania. Miejsce rozpoczęcia pobierania listy komentarzy pozwala regulować parametr offset.
     * (http://allegro.pl/webapi/documentation.php/show/id,112)
     *
     * @param array $Options
     * @return array
     */
    public function MyFeedback2Limit($Options) {
        $this->checkConnection();
        return $this->_client->doMyFeedback2Limit(
                        $this->_session['session-handle-part'], $Options['feedback-type'], $Options['offset'], $Options['desc'], $Options['items-array'], $Options['package-element']
        );
    }

    /*     * ********************************************************************************************************
     * Komponenty i klucze wersji (http://allegro.pl/webapi/documentation.php/theme/id,61)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie wartości wszystkich wersjonowanych komponentów oraz umożliwia
     * podgląd kluczy wersji dla wszystkich krajów.
     * (http://allegro.pl/webapi/documentation.php/show/id,62)
     *
     * @return array
     */
    public function QueryAllSysStatus() {
        return $this->_client->doQueryAllSysStatus(
                        self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie wartości jednego z wersjonowanych komponentów (program, drzewo kategorii, usługa,
     * parametry, pola formularza sprzedaży, serwisy) oraz umożliwia podgląd klucza wersji dla wskazanego krajów.
     * (http://allegro.pl/webapi/documentation.php/show/id,61)
     *
     * @param int $Component
     * 		1 - usługa Allegro WebAPI,
     * 		2 - aplikacja,
     * 		3 - struktura drzewa kategorii,
     * 		4 - pola formularza sprzedaży,
     * 		5 - serwisy
     *
     * @return array
     */
    public function QuerySysStatus($Component) {
        return $this->_client->doQuerySysStatus(
                        $Component, self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /*     * ********************************************************************************************************
     * Kupujący (http://allegro.pl/webapi/documentation.php/theme/id,101)
     * ******************************************************************************************************* */

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na złożenie oferty kupna w aukcji.
     * (http://allegro.pl/webapi/documentation.php/show/id,382)
     *
     * @param array $Options
     * @return array
     */
    public function BidItem($Options) {
        $this->checkConnection();
        return $this->_client->doBidItem(
                        $this->_session['session-handle-part'], $Options['bid-it-id'], $Options['bid-user-price'], $Options['bid-quantity'], $Options['bid-buy-now']
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na wysłanie prośby o wycofanie oferty kupna złożonej
     * w aukcji przez zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,304)
     *
     * @param array $Options
     * @return array
     */
    public function RequestCancelBid($Options) {
        $this->checkConnection();
        return $this->_client->doRequestCancelBid(
                        $this->_session['session-handle-part'], $Options['request-item-id'], $Options['request-cancel-reason']
        );
    }

    /*     * ********************************************************************************************************
     * Licencjonowanie (http://allegro.pl/webapi/documentation.php/theme/id,62)
     * ******************************************************************************************************* */

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na pobranie przez właściciela klucza daty ważności licencji,
     * udzielonej użytkownikowi o wskazanej nazwie.
     * (http://allegro.pl/webapi/documentation.php/show/id,63)
     *
     * @param string $User
     * @return array
     */
    public function GetAdminUserLicenceDate($User) {
        $this->checkConnection();
        return $this->_client->doGetAdminUserLicenceDate(
                        $this->_session['session-handle-part'], $User
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na pobranie przez zalogowanego użytkownika daty ważności licencji,
     * która została mu udzielona dla klucza podanego przy logowaniu.
     * (http://allegro.pl/webapi/documentation.php/show/id,161)
     *
     * @return array
     */
    public function GetUserLicenceDate() {
        $this->checkConnection();
        return $this->_client->doGetUserLicenceDate(
                        $this->_session['session-handle-part']
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na ustawienie przez właściciela klucza daty ważności licencji użytkownika o wskazanej nazwie.
     * (http://allegro.pl/webapi/documentation.php/show/id,64)
     *
     * @param array $Options
     * @return array
     */
    public function SetUserLicenceDate($Options) {
        $this->checkConnection();
        return $this->_client->doSetUserLicenceDate(
                        $this->_session['session-handle-part'], $Options['user-lic-login'], self::COUNTRY_CODE, $Options['user-lic-date']
        );
    }

    /*     * ********************************************************************************************************
     * Logowanie (http://allegro.pl/webapi/documentation.php/theme/id,22)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na uwierzytelnienie i autoryzację użytkownika za pomocą danych dostępowych do konta
     * (podając hasło w postaci zakodowanej SHA-256 a następnie base64 lub hasło w wersji tekstowej).
     * Po pomyślnym uwierzytelnieniu, użytkownik otrzymuje identyfikator sesji, którym następnie może
     * posłużyć się przy wywoływaniu metod wymagających autoryzacji. Identyfikator sesji zachowuje
     * ważność przez 3 godziny od momentu jego utworzenia.
     * (http://allegro.pl/webapi/documentation.php/show/id,82)
     *
     * @deprecated 2013 1.0 001
     * @param bool $Encode
     */
    public function Login($Encode = false) {
        $version = $this->QuerySysStatus(1);
        $this->_local_version = $version['ver-key'];

        if (!$Encode) {
            $session = $this->_client->doLogin(
                    $this->_config['allegro_login'], $this->_config['allegro_password'], self::COUNTRY_CODE, $this->_config['allegro_key'], $version['ver-key']
            );
        } else {
            if (function_exists('hash') && in_array('sha256', hash_algos())) {
                $pass = hash('sha256', $this->_config['allegro_password'], true);
            } else if (function_exists('mhash') && is_int(MHASH_SHA256)) {
                $pass = mhash(MHASH_SHA256, $this->_config['allegro_password']);
            }

            $password = base64_encode($pass);

            $session = $this->_client->doLoginEnc(
                    $this->_config['allegro_login'], $password, self::COUNTRY_CODE, $this->_config['allegro_key'], $version['ver-key']
            );
        }

        $this->_session = $session;

        unset($password);
        unset($this->_config['allegro_password']);
    }

    /**
     * Metoda działa analogicznie do metody Login. Jednak nasze hasło przehowywane jest w bezpicznej postaci.
     * Hasło podane podczas inicjalizacji musi być zakodowane za pomoca sha256
     * Z przyczyn bezpieczeństwa
     * UŻYWAJ WYŁĄCZNIE TEJ FUNKCJI!!!
     * 
     */
    public function LoginEnc() {
        $version = $this->QuerySysStatus(1);
        $this->_local_version = $version['ver-key'];
        //do
        $session = $this->_client->doLoginEnc(
                $this->_config['allegro_login'], $this->_config['allegro_password'], self::COUNTRY_CODE, $this->_config['allegro_key'], $version['ver-key']
        );
        $this->_session = $session;
        unset($this->_config['allegro_password']);
    }

    /*     * ********************************************************************************************************
     * Modyfikacja aukcji (http://allegro.pl/webapi/documentation.php/theme/id,1)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na dodanie wspólnego, dodatkowego tekstu do opisów aukcji wystawionych przez zalogowanego
     * użytkownika. Treść dodanego tekstu pojawi się pod właściwym opisem, z przypisem Dodano oraz datą i godziną jego dodania.
     * (http://allegro.pl/webapi/documentation.php/show/id,1)
     *
     * @param array $Options
     * @return array
     */
    public function AddDescToItems($Options) {
        $this->checkConnection();
        return $this->_client->doAddDescToItems(
                        $this->_session['session-handle-part'], $Options['items-id-array'], $Options['it-description']
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na odwołanie ofert kupna złożonych w aukcji zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,303)
     *
     * @param array $Options
     * @return array
     */
    public function CancelBidItem($Options) {
        $this->checkConnection();
        return $this->_client->doCancelBidItem(
                        $this->_session['session-handle-part'], $Options['cancel-item-id'], $Options['cancel-bids-array'], $Options['cancel-bids-reason'], $Options['cancel-add-to-black-list']
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na zmianę cen dostępnych w aukcji. Konieczne jest podanie oczekiwanych wartości
     * wszystkich trzech cen (nawet jeżeli np. tylko jedna ma ulec zmianie, w parametrach reprezentujących
     * pozostałe dwie ceny znaleźć powinna się ich aktualna wartość). Przekazanie wartości 0
     * w danym parametrze dezaktywuje wskazana cenę w aukcji.
     * (http://allegro.pl/webapi/documentation.php/show/id,223)
     *
     * @param array $Options
     * @return array
     */
    public function ChangePriceItem($Options) {
        $this->checkConnection();
        return $this->_client->doChangePriceItem(
                        $this->_session['session-handle-part'], $Options['item-id'], $Options['new-starting-price'], $Options['new-reserve-price'], $Options['new-buy-now-price']
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na zmianę liczby przedmiotów dostępnych na aukcji zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,222)
     *
     * @param array $Options
     * @return array
     */
    public function ChangeQuantityItem($Options) {
        $this->checkConnection();
        return $this->_client->doChangeQuantityItem(
                        $this->_session['session-handle-part'], $Options['item-id'], $Options['new-item-quantity']
        );
    }

    /**
     * Metoda pozwala na kończenie przed czasem (z lub bez odwołania ofert) aukcji zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,221)
     *
     * @param array $Options
     * @return array
     */
    public function FinishItem($Options) {
        $this->checkConnection();
        return $this->_client->doFinishItem(
                        $this->_session['session-handle-part'], $Options['finish-item-id'], $Options['finish-cancel-all-bids'], $Options['finish-cancel-reason']
        );
    }

    /**
     * Metoda pozwala na kończenie przed czasem (bez lub z odwołaniem ofert) wielu aukcji zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,623)
     *
     * @param array $Options
     * @return array
     */
    public function FinishItems($Options) {
        $this->checkConnection();
        return $this->_client->doFinishItems(
                        $this->_session['session-handle-part'], $Options
        );
    }

    /*     * ********************************************************************************************************
     * Moje Allegro (http://allegro.pl/webapi/documentation.php/theme/id,44)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie listy ulubionych kategorii zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,49)
     *
     * @return array
     */
    public function GetFavouriteCategories() {
        $this->checkConnection();
        return $this->_client->doGetFavouriteCategories(
                        $this->_session['session-handle-part']
        );
    }

    /**
     * Metoda pozwala na pobranie listy ulubionych sprzedających zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,50)
     *
     * @return array
     */
    public function GetFavouriteSellers() {
        $this->checkConnection();
        return $this->_client->doGetFavouriteSellers(
                        $this->_session['session-handle-part']
        );
    }

    /**
     * Metoda pozwala na pobranie listy aukcji z poszczególnych zakładek Mojego Allegro (licytowane, kupione, niekupione,
     * obserwowane: trwające, obserwowane: zakończone, sprzedawane, sprzedane, niesprzedane, do wystawienia)
     * zalogowanego użytkownika. Domyślnie zwracanych jest pierwszych 25 aukcji z danej zakładki, posortowanych
     * malejąco po czasie ich zakończenia. Możliwe jest także pobranie informacji o wskazanych aukcjach z danej
     * zakładki (items-array). Pełen podgląd nazw oraz identyfikatorów kupujących możliwy jest tylko dla zakładek typu
     * 'sell' i 'sold' - dla pozostałych typów wspomniane dane zwrócone zostaną w formie zanonimizowanej.
     * (http://allegro.pl/webapi/documentation.php/show/id,107)
     *
     * @param array $Options
     * @return array
     */
    public function MyAccount2($Options) {
        $this->checkConnection();
        return $this->_client->doMyAccount2(
                        $this->_session['session-handle-part'], $Options['account-type'], $Options['offset'], $Options['items-array'], $Options['limit']
        );
    }

    /**
     * Metoda pozwala na pobranie informacji o liczbie aukcji z poszczególnych zakładek Mojego Allegro
     * (licytowane, kupione, niekupione, obserwowane: trwające, obserwowane: zakończone, sprzedawane, sprzedane,
     * niesprzedane, do wystawienia) zalogowanego użytkownika. Możliwe jest także pobranie informacji
     * o liczbie aukcji znajdujących się we wskazanej zakładce, z listy przekazanej w items-array.
     * (http://allegro.pl/webapi/documentation.php/show/id,108)
     *
     * @param array $Options
     * @return array
     */
    public function MyAccountItemsCount($Options) {
        $this->checkConnection();
        return $this->_client->doMyAccountItemsCount(
                        $this->_session['session-handle-part'], $Options['account-type'], $Options['items-array']
        );
    }

    /**
     * Metoda pozwala na usuwanie wskazanych aukcji z listingu aukcji obserwowanych
     * (trwających oraz zakończonych) zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,115)
     *
     * @param array $Options
     * @return array
     */
    public function RemoveFromWatchList($Options) {
        $this->checkConnection();
        return $this->_client->doRemoveFromWatchList(
                        $this->_session['session-handle-part'], $Options['items-id-array']
        );
    }

    /*     * ********************************************************************************************************
     * Nowości i komunikaty (http://allegro.pl/webapi/documentation.php/theme/id,69)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie listy komunikatów serwisowych ze strony Nowości i komunikaty dla wskazanego kraju.
     * Zwróconych może być maks. 50 ostatnich komunikatów dla danej kategorii - ich lista posortowana
     * jest malejąco po czasie dodania. W przypadku nie podania daty (an-it-date) lub identyfikatora (ani-it-id)
     * komunikatu, zwrócony zostanie jeden najnowszy komunikat ze wskazanej kategorii.
     * (http://allegro.pl/webapi/documentation.php/show/id,93)
     *
     * @param array $Options
     * @return array
     */
    public function GetServiceInfo($Options) {
        return $this->_client->doGetServiceInfo(
                        self::COUNTRY_CODE, $Options['an-cat-id'], $Options['an-it-date'], $Options['an-it-id'], $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie listy kategorii komunikatów serwisowych ze strony Nowości i komunikaty dla wskazanego kraju.
     * (http://allegro.pl/webapi/documentation.php/show/id,94)
     *
     * @return array
     */
    public function GetServiceInfoCategories() {
        return $this->_client->doGetServiceInfoCategories(
                        self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /*     * ********************************************************************************************************
     * Opłaty i prowizje (http://allegro.pl/webapi/documentation.php/theme/id,66)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie informacji o opłatach związanych z korzystaniem z serwisu odpowiedniego dla wskazanego kraju.
     * (http://allegro.pl/webapi/documentation.php/show/id,88)
     *
     * @return array
     */
    public function GetPaymentData() {
        return $this->_client->doGetPaymentData(
                        self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie bieżącego salda z konta zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,109)
     *
     * @return array
     */
    public function MyBilling() {
        return $this->_client->doMyBilling(
                        self::COUNTRY_CODE
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na sprawdzenie kosztów związanych z wystawieniem aukcji oraz prowizją za zrealizowaną
     * w jej ramach sprzedaż. Sprawdzenie kosztów możliwe jest jedynie dla aukcji zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,301)
     *
     * @param array $Options
     * @return array
     */
    public function MyBillingItem($Options) {
        $this->checkConnection();
        return $this->_client->doMyBillingItem(
                        $this->_session['session-handle-part'], $Options['item-id'], $Options['option']
        );
    }

    /*     * ********************************************************************************************************
     * Płacę z Allegro (http://allegro.pl/webapi/documentation.php/theme/id,65)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie listy wpłat od kupujących (dokonanych za pośrednictwem PzA) za transakcje
     * w ramach aukcji zalogowanego użytkownika. Domyślnie (w przypadku nie zdefiniowana zakresu czasu)
     * pobierana jest lista wpłat z przeciągu ostatniego tygodnia (domyślnie 25 ostatnio dokonanych wpłat),
     * posortowana malejąco po czasie ich realizacji. Listę można filtrować po użytkowniku dokonującym wpłaty
     * (buyer-id), po aukcji której wpłaty dotyczą (item-id) oraz po zakresie czasu, w którym wpłaty zostały
     * dokonane. W przypadku gdy za datę początkową zakresu czasu (trans-recv-date-from) podstawiona zostanie
     * konkretna wartość, a dla daty końcowej zakresu czasu (trans-recv-date-to) przekazane zostanie 0, zwrócona
     * zostanie lista wpłat od daty podanej do daty podanej + 7 dni. W przypadku odwrotnym (gdy dla daty początkowej
     * zakresu czasu przekazane zostanie 0, a dla daty końcowej zakresu czasu podstawiona zostanie konkretna wartość),
     * zwrócona zostanie lista wpłat od daty podanej - 7 dni do daty podanej. Przy podaniu konkretnych wartości
     * zakresu czasu zarówno dla daty początkowej, jak i dla daty końcowej, zwrócona zostanie lista wpłat zrealizowanych
     * w podanym zakresie (ustalony zakres nie może jednak przekraczać 90 dni). Poszczególne filtry można ze sobą łączyć.
     * (http://allegro.pl/webapi/documentation.php/show/id,85)
     *
     * @param array $Options
     * @return array
     */
    public function GetMyIncomingPayments($Options) {
        $this->checkConnection();
        return $this->_client->doGetMyIncomingPayments(
                        $this->_session['session-handle-part'], $Options['buyer-id'], $Options['item-id'], $Options['trans-recv-date-from'], $Options['trans-recv-date-to'], $Options['trans-page-limit'], $Options['trans-offset']
        );
    }

    /**
     * Metoda pozwala na pobranie listy zwrotów (wycofanych wpłat dokonanych za pośrednictwem PzA)
     * za transakcje zrealizowane przez kupujących w ramach aukcji zalogowanego użytkownika.
     * Okres czasu, dla jakiego metoda zwraca dane to ok. 90 dni.
     * (http://allegro.pl/webapi/documentation.php/show/id,522)
     *
     * @param array $Options
     * @return array
     */
    public function GetMyIncomingPaymentsRefunds($Options) {
        $this->checkConnection();
        return $this->_client->doGetMyIncomingPaymentsRefunds(
                        $this->_session['session-handle-part'], $Options['buyer-id'], $Options['item-id'], $Options['limit'], $Options['offset']
        );
    }

    /**
     * Metoda pozwala na pobranie listy wpłat (dokonanych za pośrednictwem PzA) za transakcje
     * zrealizowane przez zalogowanego użytkownika. Domyślnie (w przypadku nie zdefiniowana zakresu czasu)
     * pobierana jest lista wpłat z przeciągu ostatniego tygodnia (domyślnie 25 ostatnio dokonanych wpłat),
     * posortowana malejąco po czasie ich realizacji. Listę można filtrować po użytkowniku, któremu
     * dokonywane były wpłaty (seller-id), po aukcji której wpłaty dotyczą (item-id) oraz po zakresie czasu,
     * w którym wpłaty zostały dokonane. W przypadku gdy za datę początkową zakresu czasu (trans-create-date-from)
     * podstawiona zostanie konkretna wartość, a dla daty końcowej zakresu czasu (trans-create-date-to)
     * przekazane zostanie 0, zwrócona zostanie lista wpłat od daty podanej do daty podanej + 7 dni.
     * W przypadku odwrotnym (gdy dla daty początkowej zakresu czasu przekazane zostanie 0, a dla daty końcowej
     * zakresu czasu podstawiona zostanie konkretna wartość), zwrócona zostanie lista wpłat od daty
     * podanej - 7 dni do daty podanej. Przy podaniu konkretnych wartości zakresu czasu zarówno dla daty początkowej,
     * jak i dla daty końcowej, zwrócona zostanie lista wpłat zrealizowanych w podanym zakresie (ustalony zakres
     * nie może jednak przekraczać 90 dni). Poszczególne filtry można ze sobą łączyć.
     * (http://allegro.pl/webapi/documentation.php/show/id,86)
     *
     * @param array $Options
     * @return array
     */
    public function GetMyPayments($Options) {
        $this->checkConnection();
        return $this->_client->doGetMyPayments(
                        $this->_session['session-handle-part'], $Options['seller-id'], $Options['item-id'], $Options['trans-create-date-from'], $Options['trans-create-date-to'], $Options['trans-page-limit'], $Options['trans-offset']
        );
    }

    /**
     * Metoda pozwala na pobranie listy zwrotów (wycofanych wpłat dokonanych za pośrednictwem PzA) za transakcje
     * zrealizowane przez zalogowanego użytkownika. Okres czasu, dla jakiego metoda zwraca dane to ok. 90 dni.
     * (http://allegro.pl/webapi/documentation.php/show/id,502)
     *
     * @param array $Options
     * @return array
     */
    public function GetMyPaymentsRefunds($Options) {
        $this->checkConnection();
        return $this->_client->doGetMyPaymentsRefunds(
                        $this->_session['session-handle-part'], $Options['seller-id'], $Options['item-id'], $Options['limit'], $Options['offset']
        );
    }

    /**
     * Metoda pozwala na pobranie listy wypłat środków (wpłaconych przez kupujących za pośrednictwem PzA) za transakcje
     * w ramach aukcji zalogowanego użytkownika. Domyślnie (w przypadku nie zdefiniowana zakresu czasu) pobierana
     * jest lista wypłat z przeciągu ostatniego tygodnia (domyślnie 50 ostatnio dokonanych wypłat), posortowana
     * malejąco po czasie ich realizacji. Listę można filtrować po zakresie czasu, w którym wypłaty zostały dokonane.
     * W przypadku gdy za datę początkową zakresu czasu (trans-create-date-from) podstawiona zostanie konkretna wartość,
     * a dla daty końcowej zakresu czasu (trans-create-date-to) przekazane zostanie 0, zwrócona zostanie lista wypłat
     * od daty podanej do daty podanej + 7 dni. W przypadku odwrotnym (gdy dla daty początkowej zakresu czasu przekazane
     * zostanie 0, a dla daty końcowej zakresu czasu podstawiona zostanie konkretna wartość), zwrócona zostanie lista
     * wypłat od daty podanej - 7 dni do daty podanej. Przy podaniu konkretnych wartości zakresu czasu zarówno dla
     * daty początkowej, jak i dla daty końcowej, zwrócona zostanie lista wypłat zrealizowanych w podanym zakresie
     * (ustalony zakres nie może jednak przekraczać 30 dni).
     * (http://allegro.pl/webapi/documentation.php/show/id,87)
     *
     * @param array $Options
     * @return array
     */
    public function GetMyPayouts($Options) {
        $this->checkConnection();
        return $this->_client->doGetMyPayouts(
                        $this->_session['session-handle-part'], $Options['trans-create-date-from'], $Options['trans-create-date-to'], $Options['trans-page-limit'], $Options['trans-offset']
        );
    }

    /**
     * Metoda pozwala na wnioskowanie o dopłatę do transakcji, za którą płatność jest niekompletna.
     * Dla każdej transakcji można wysłać tylko jeden wniosek o dopłatę.
     * (http://allegro.pl/webapi/documentation.php/show/id,662)
     *
     * @param array $Options
     * @return array
     */
    public function RequestSurcharge($Options) {
        $this->checkConnection();
        return $this->_client->doRequestSurcharge(
                        $this->_session['session-handle-part'], $Options['surcharge-trans-id'], $Options['surcharge-value'], $Options['surcharge-message']
        );
    }

    /*     * ********************************************************************************************************
     * Produkty w Allegro (http://allegro.pl/webapi/documentation.php/theme/id,141)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie danych na temat konkretnego produktu z katalogu Produktów w Allegro.
     * Do wywołania metody wymagany jest identyfikator produktu oraz hash - obie wartości mogą być
     * pobrane za pomocą metod doShowItemInfoExt oraz doGetItemsInfo (dla aukcji zintegrowanych z produktem).
     * (http://allegro.pl/webapi/documentation.php/show/id,644)
     *
     * @param array $Options
     * @return array
     */
    public function ShowProductInfo($Options) {
        $this->checkConnection();
        return $this->_client->doShowProductInfo(
                        $this->_session['session-handle-part'], $Options['product-id'], $Options['category-hash']
        );
    }

    /*     * ********************************************************************************************************
     * Rabaty (http://allegro.pl/webapi/documentation.php/theme/id,121)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie pojedynczych aktów zakupowych zrealizowanych przez danego kupującego we
     * wskazanej aukcji (w której sprzedającym był zalogowany użytkownik). Uzyskane dane mogą być następnie
     * wykorzystane np. do udzielania rabatów za pomocą metody doMakeDiscount. Metoda zwraca tylko te akty zakupowe,
     * na które w chwili jej wywołania jest możliwość nałożenia rabatu (nie są one jeszcze opłacone).
     * Wyjątkiem od powyższego jest sytuacja, w której akt zakupowy został opłacony, ale płatność została
     * anulowana - jest on wtedy traktowany jak nieopłacony i informacja o nim zostanie zwrócona.
     * (http://allegro.pl/webapi/documentation.php/show/id,462)
     *
     * @param array $Options
     * @return array
     */
    public function GetDeals($Options) {
        $this->checkConnection();
        return $this->_client->doGetDeals(
                        $this->_session['session-handle-part'], $Options['item-id'], $Options['buyer-id']
        );
    }

    /**
     * Metoda pozwala na udzielenie rabatu kupującemu w ramach danego aktu zakupowego.
     * Możliwe jest udzielenie naraz tylko jednego rodzaju rabatu - albo kwotowego, albo procentowego
     * (wyjątkiem jest możliwość wyzerowania obu parametrów w celu zdjęcia istniejącego rabatu).
     * Każde kolejne wywołanie metody dla tego samego aktu zakupowego nadpisuje rabat ustawiony wcześniej.
     * Za każdym razem rabat jest udzielany od kwoty pierwotnej (bez rabatu) za dany akt zakupowy,
     * nie zaś od kwoty uprzednio "zrabatowanej". Nałożony rabat obniża pierwotną kwotę do zapłacenia za
     * dany akt zakupowy, proporcjonalnie obniżając kwotę jednostkową do zapłacenia za każdy
     * z przedmiotów zakupionych w ramach danego aktu zakupowego.
     * (http://allegro.pl/webapi/documentation.php/show/id,482)
     *
     * @param array $Options
     * @return array
     */
    public function MakeDiscount($Options) {
        $this->checkConnection();
        return $this->_client->doMakeDiscount(
                        $this->_session['session-handle-part'], $Options['deal-it'], $Options['discount-amount'], $Options['discount-percentage']
        );
    }

    /*     * ********************************************************************************************************
     * Różne (http://allegro.pl/webapi/documentation.php/theme/id,25)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na sprawdzenie czy hash weryfikujący poprawność odnośnika, wysłanego do danego
     * kupującego w danej aukcji jest poprawny (został faktycznie wygenerowany za pomocą taga [EXT_LINK_xxx]).
     * Odpowiednie odnośniki wygenerować można na podstawie trzech unikalnych tagów, dostępnych dla każdego
     * użytkownika usługi na stronie: Moje Allegro > WebAPI, w bloku Konfiguracja zewnętrznych linków.
     * (http://allegro.pl/webapi/documentation.php/show/id,23)
     *
     * @param array $Options
     * @return array
     */
    public function CheckExternalKey($Options) {
        return $this->_client->doCheckExternalKey(
                        $this->_config['allegro_key'], $Options['user-id'], $Options['item-id'], $Options['hash-key']
        );
    }

    /**
     * Metoda pozwala na sprawdzenie jaka jest wartość identyfikatora wystawionej aukcji za pomocą
     * identyfikatora aukcji planowanej do wystawienia. W przypadku przekazania numeru planowanej
     * aukcji, która jeszcze się nie rozpoczęła, metoda zwróci 0.
     * (http://allegro.pl/webapi/documentation.php/show/id,24)
     *
     * @param int $Auction
     * @return array
     */
    public function CheckItemIdByFutureItemId($Auction) {
        return $this->_client->doCheckItemIdByFutureItemId(
                        $this->_config['allegro_key'], self::COUNTRY_CODE, $Auction
        );
    }

    /**
     * Metoda pozwala na pobranie listy wszystkich krajów dostępnych w serwisie.
     * (http://allegro.pl/webapi/documentation.php/show/id,25)
     *
     * @return array
     */
    public function GetCountries() {
        return $this->_client->doGetCountries(
                        self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie listy graficznych, systemowych szablonów aukcji dostępnych dla wskazanego kraju.
     * (http://allegro.pl/webapi/documentation.php/show/id,95)
     *
     * @return array
     */
    public function GetServiceTemplates() {
        return $this->_client->doGetServiceTemplates(
                        self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie pełnej listy sposobów dostawy dostępnych we wskazanym kraju.
     * (http://allegro.pl/webapi/documentation.php/show/id,624)
     *
     * @return array
     */
    public function GetShipmentData() {
        return $this->_client->doGetShipmentData(
                        self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie szczegółowych informacji (nazwa, adres WWW, kod kraju,
     * używana strona kodowa, logo, flaga kraju) o dostępnych serwisach aukcyjnych.
     * (http://allegro.pl/webapi/documentation.php/show/id,98)
     *
     * @return array
     */
    public function GetSitesFlagInfo() {
        return $this->_client->doGetSitesFlagInfo(
                        self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie szczegółowych informacji (nazwa, adres WWW, kod kraju,
     * używana strona kodowa, logo) o dostępnych serwisach aukcyjnych.
     * (http://allegro.pl/webapi/documentation.php/show/id,99)
     *
     * @return array
     */
    public function GetSitesInfo() {
        return $this->_client->doGetSitesInfo(
                        self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie listy regionów (dla Polski - województw) dla danego kraju.
     * (http://allegro.pl/webapi/documentation.php/show/id,101)
     *
     * @return array
     */
    public function GetStatesInfo() {
        return $this->_client->doGetStatesInfo(
                        self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie aktualnego (dla danego kraju) czasu z serwera Allegro.
     * (http://allegro.pl/webapi/documentation.php/show/id,81)
     *
     * @return array
     */
    public function GetSystemTime() {
        return $this->_client->doGetSystemTime(
                        self::COUNTRY_CODE, $this->_config['allegro_key']
        );
    }

    /*     * ********************************************************************************************************
     * Sklepy Allegro (http://allegro.pl/webapi/documentation.php/theme/id,70)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na pobranie pełnego drzewa kategorii utworzonych przez zalogowanego użytkownika w jego Sklepie Allegro.
     * (http://allegro.pl/webapi/documentation.php/show/id,97)
     *
     * @return array
     */
    public function GetShopCatsData() {
        $this->checkConnection();
        return $this->_client->doGetShopCatsData(
                        $this->_session['session-handle-part']
        );
    }

    /**
     * Metoda pozwala na wystawienie aukcji w Sklepie Allegro na podstawie aukcji istniejących.
     * Z uwagi na specyfikę działania mechanizmu ponownego wystawiania aukcji - identyfikatory aukcji
     * zwracane na wyjściu, to identyfikatory aukcji na podstawie których nowe aukcje zostały/miały zostać
     * wystawione - nie identyfikatory nowo wystawionych aukcji.
     * (http://allegro.pl/webapi/documentation.php/show/id,322)
     *
     * @param array $Options
     * @return array
     */
    public function SellSomeAgainInShop($Options) {
        return $this->_client->doSellSomeAgainInShop(
                        $this->_config['allegro_key'], $Options['sell-items-array'], $Options['sell-starting-time'], $Options['sell-ahop-duration'], $Options['sell-shop-options'], $Options['sell-prolong-options'], $Options['sell-shop-category']
        );
    }

    /*     * ********************************************************************************************************
     * System zwrotu prowizji (http://allegro.pl/webapi/documentation.php/theme/id,81)
     * ******************************************************************************************************* */

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na anulowanie procedury zwrotu prowizji. Po anulowaniu procedury zwrotu dot.
     * danej transakcji, nie ma możliwości ponownego wystąpienia o zwrot prowizji dla niej.
     * (http://allegro.pl/webapi/documentation.php/show/id,263)
     *
     * @param array $Options
     * @return array
     */
    public function CancelRefundForms($Options) {
        $this->checkConnection();
        return $this->_client->doCancelRefundForms(
                        $this->_session['session-handle-part'], $Options
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na anulowanie ostrzeżeń. Po anulowaniu ostrzeżenia dot. danej transakcji,
     * nie ma możliwości ponownego wystąpienia o zwrot prowizji dla niej. Anulowanie ostrzeżenia
     * jest równoznaczne z ponownym naliczeniem prowizji za sprzedany przedmiot.
     * (http://allegro.pl/webapi/documentation.php/show/id,264)
     *
     * @param array $Options
     * @return array
     */
    public function CancelRefundWarnings($Options) {
        $this->checkConnection();
        return $this->_client->doCancelRefundWarnings(
                        $this->_session['session-handle-part'], $Options
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na pobieranie statusów formularzy zwrotu prowizji dla transakcji,
     * w których sprzedaż nastąpiła z konta zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,262)
     *
     * @param array $Options
     * @return array
     */
    public function GetRefundFormsStatuses($Options) {
        $this->checkConnection();
        return $this->_client->doGetRefundFormsStatuses(
                        $this->_session['session-handle-part'], $Options
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na pobranie listy dostępnych w danym kraju powodów ubiegania się o zwrot prowizji.
     * (http://allegro.pl/webapi/documentation.php/show/id,202)
     *
     * @return array
     */
    public function GetRefundReasons() {
        return $this->_client->doGetRefundReasons(
                        $this->_config['allegro_key'], self::COUNTRY_CODE
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na pobranie listy transakcji dla których trwa lub może trwać procedura zwrotu
     * prowizji (listing zawiera aukcje, nieprzeniesione do archiwum, z zakładek Sprzedane oraz Sprzedaję).
     * Domyślnie pobierana jest lista wszystkich dostępnych transakcji, posortowana rosnąco po czasie
     * zakończenia aukcji. Rozmiar porcji danych pozwala regulować parametr limit, a sterowanie pobieraniem
     * kolejnych porcji danych umożliwia parametr offset.
     * (http://allegro.pl/webapi/documentation.php/show/id,261)
     *
     * @param array $Options
     * @return array
     */
    public function GetRefundTransactions($Options) {
        $this->checkConnection();
        return $this->_client->doGetRefundTransactions(
                        $this->_session['session-handle-part'], $Options['offset'], $Options['limit']
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na wysłanie formularzy zwrotu prowizji (wypełniać je można nie wcześniej niż 7 dni
     * i nie później niż 45 dni od dnia zakończenia sprzedaży), dot. niezrealizowanych przez
     * kupujących transakcji na aukcjach zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,201)
     *
     * @param array $Options
     * @return array
     */
    public function SendRefundForms($Options) {
        $this->checkConnection();
        return $this->_client->doSendRefundForms(
                        $this->_session['session-handle-part'], $Options
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na wysłanie przypomnień o zawarciu transakcji (wypełniać je można nie wcześniej
     * niż 3 dni i nie później niż 30 dni od dnia zakończenia sprzedaży), do kupujących którzy
     * dokonali zakupu na aukcjach zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,241)
     *
     * @param array $Options
     * @return array
     */
    public function SendReminderMessages($Options) {
        $this->checkConnection();
        return $this->_client->doSendReminderMessages(
                        $this->_session['session-handle-part'], $Options
        );
    }

    /*     * ********************************************************************************************************
     * Widok i opcje aukcji (http://allegro.pl/webapi/documentation.php/theme/id,23)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na dodanie wskazanych aukcji do listingu aukcji obserwowanych zalogowanego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,22)
     *
     * @param array $Items
     * @return array
     */
    public function AddWatchList($Items) {
        $this->checkConnection();
        return $this->_client->doAddWatchList(
                        $this->_session['session-handle-part'], $Items
        );
    }

    /**
     * Metoda pozwala na pobranie publicznie dostępnych informacji na temat wszystkich użytkowników,
     * którzy dokonali zakupu w danej aukcji. Pełen podgląd nazw oraz identyfikatorów użytkowników
     * możliwy jest tylko dla użytkowników, którzy wystawili daną aukcję - pozostali użytkownicy
     * otrzymają wspomniane dane w formie zanonimizowanej.
     * (http://allegro.pl/webapi/documentation.php/show/id,44)
     *
     * @param int $Auction
     * @return array
     */
    public function GetBidItem2($Auction) {
        $this->checkConnection();
        return $this->objectToArray($this->_client->doGetBidItem2(
                                $this->_session['session-handle-part'], $Auction
        ));
    }

    /**
     * Metoda pozwala na pobranie wszystkich dostępnych informacji (m.in. opis, kategoria,
     * zdjęcia, parametry, dostępne sposoby dostawy i formy płatności, etc.) o wskazanych aukcjach.
     * (http://allegro.pl/webapi/documentation.php/show/id,52)
     *
     * @param array $Options
     * @return array
     */
    public function GetItemsInfo($Options) {
        $this->checkConnection();
        return $this->_client->doGetItemsInfo(
                        $this->_session['session-handle-part'], $Options['items-id-array'], $Options['get-desc'], $Options['get-image-url'], $Options['get-attribs'], $Options['get-postage-options'], $Options['get-company-info']
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na pobranie kompletnych informacji o aukcji - wraz z listą i danymi kupujących.
     * (http://allegro.pl/webapi/documentation.php/show/id,402)
     *
     * @param array $Options
     * @return array
     */
    public function GetItemTransaction($Options) {
        $this->checkConnection();
        return $this->_client->doGetItemTransaction(
                        $this->_session['session-handle-part'], $Options['item-id'], $Options['item-options']
        );
    }

    /**
     * Tylko w pakiecie Profesjonalnym!
     *
     * Metoda pozwala na wysłanie określonego rodzaju wiadomości do wybranego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,281)
     *
     * @param array $Options
     * @return array
     */
    public function SendEmailToUser($Options) {
        $this->checkConnection();
        return $this->_client->doSendEmailToUser(
                        $this->_session['session-handle-part'], $Options['mail-to-user-item-id'], $Options['mail-to-user-receiver-id'], $Options['mail-to-user-subject-id'], $Options['mail-to-user-option'], $Options['mail-to-user-message']
        );
    }

    /**
     * Metoda pozwala na pobranie wszystkich dostępnych informacji (m.in. opis, kategoria, zdjęcia,
     * parametry, dostępne sposoby dostawy i formy płatności, etc.) o wskazanej aukcji.
     * (http://allegro.pl/webapi/documentation.php/show/id,342)
     *
     * @param array $Options
     * @return array
     */
    public function ShowItemInfoExt($Options) {
        $this->checkConnection();
        return $this->_client->doShowItemInfoExt(
                        $this->_session['session-handle-part'], $Options['item-id'], $Options['get-desc'], $Options['get-image-url'], $Options['get-attribs'], $Options['get-postage-options'], $Options['get-company-info']
        );
    }

    /*     * ********************************************************************************************************
     * Wystawianie aukcji (http://allegro.pl/webapi/documentation.php/theme/id,41)
     * ******************************************************************************************************* */

    /**
     * Metoda pozwala na sprawdzenie ogólnych oraz szczegółowych kosztów związanych z wystawieniem
     * aukcji przed jej faktycznym wystawieniem. Metoda może służyć także jako symulator poprawności
     * wystawienia aukcji, ponieważ struktura pól jaką przyjmuje jako jeden z parametrów jest
     * identyczną z tą przyjmowaną przez doNewAuctionExt.
     * (http://allegro.pl/webapi/documentation.php/show/id,41)
     *
     * @param array $Fields
     * @return array
     */
    public function CheckNewAuctionExt($Fields) {
        $this->checkConnection();
        return $this->_client->doCheckNewAuctionExt(
                        $this->_session['session-handle-part'], $Fields
        );
    }

    /**
     * Metoda pozwala na pobranie listy pól formularza sprzedaży dostępnych we wskazanym kraju.
     * Wybrane pola mogą następnie posłużyć np. do zbudowania i wypełnienia formularza
     * wystawienia nowej aukcji z poziomu metody doNewAuctionExt.
     * (http://allegro.pl/webapi/documentation.php/show/id,91)
     *
     * @return array
     */
    public function GetSellFormFieldsExt() {
        return $this->_client->doGetSellFormFieldsExt(
                        self::COUNTRY_CODE, '0', $this->_config['allegro_key']
        );
    }

    /**
     * Metoda pozwala na pobranie w porcjach listy pól formularza sprzedaży dostępnych we wskazanym kraju.
     * Wybrane pola mogą następnie posłużyć np. do zbudowania i wypełnienia formularza wystawienia
     * nowej aukcji z poziomu metody doNewAuctionExt. Domyślnie zwracanych jest 50 pierwszych pól.
     * Rozmiar porcji pozwala regulować parametr package-element, a sterowanie pobieraniem
     * kolejnych porcji danych umożliwia parametr offset.
     * (http://allegro.pl/webapi/documentation.php/show/id,92)
     *
     * @param array $Options
     * @return array
     */
    public function GetSellFormFieldsExtLimit($Options) {
        return $this->_client->doGetSellFormFieldsExtLimit(
                        self::COUNTRY_CODE, '0', $this->_config['allegro_key'], $Options['offset'], $Options['package-element']
        );
    }

    /**
     * Metoda pozwala na wystawienie nowej aukcji w serwisie. Aby sprawdzić poprawność wystawienia aukcji,
     * należy nadać jej dodatkowy, lokalny identyfikator (local-id), a następnie zweryfikować aukcję za
     * pomocą metody doVerifyItem (wartość local-id jest zawsze unikalna w ramach konta danego użytkownika).
     * Aby przetestować poprawność wypełnienia kolejnych pól formularza sprzedaży i/lub sprawdzić koszta związane
     * z wystawieniem aukcji, bez jej faktycznego wystawiania w serwisie, należy skorzystać z metody doCheckNewAuctionExt.
     * (http://allegro.pl/webapi/documentation.php/show/id,113)
     *
     * @param array $Options
     * @return array
     */
    public function NewAuctionExt($Options) {
        $this->checkConnection();
        return $this->_client->doNewAuctionExt(
                        $this->_session['session-handle-part'], $Options['fields'], $Options['private'], $Options['local-id']
        );
    }

    /**
     * Metoda pozwala na wystawienie aukcji w serwisie na podstawie aukcji istniejących. Z uwagi na specyfikę
     * działania mechanizmu ponownego wystawiania aukcji - identyfikatory aukcji zwracane na wyjściu, to identyfikatory
     * aukcji na podstawie których nowe aukcje zostały/miały zostać wystawione - nie identyfikatory nowo wystawionych aukcji.
     * (http://allegro.pl/webapi/documentation.php/show/id,321)
     *
     * @param array $Options
     * @return array
     */
    public function SellSomeAgain($Options) {
        $this->checkConnection();
        return $this->_client->doSellSomeAgain(
                        $this->_session['session-handle-part'], $Options['sell-items-array'], $Options['sell-starting-time'], $Options['sell-auction-duration'], $Options['sell-option']
        );
    }

    /**
     * Metoda pozwala na sprawdzenie poprawności wystawienia aukcji (utworzonej za pomocą metody
     * doNewAuctionExt, w przypadku gdy przekazano przy jej wywołaniu wartość w parametrze local-id)
     * z konta zalogowanego użytkownika. Wartość local-id jest zawsze unikalna w ramach konta danego użytkownika.
     * (http://allegro.pl/webapi/documentation.php/show/id,181)
     *
     * @param int $LocalID
     * @return array
     */
    public function VerifyItem($LocalID) {
        $this->checkConnection();
        return $this->_client->doVerifyItem(
                        $this->_session['session-handle-part'], $LocalID
        );
    }

    /*     * ********************************************************************************************************
     * Wyszukiwarka i listingi (http://allegro.pl/webapi/documentation.php/theme/id,68)
     * ******************************************************************************************************* */

    /**
     * METODA JEST DOSTĘPNA TYLKO W NOWEJ WERSJI USŁUGI (service.php). Metoda pozwala na pobranie kompletu informacji o ofertach dostępnych
     *  na wszystkich listingach (kategorii, użytkownika, specjalnych) oraz w wynikach wyszukiwania. Możliwe jest filtrowanie danych na wiele
     *  różnych sposobów (m.in. po rodzaju listingu, słowie kluczowym i szczegółach wyszukiwania. cenie, typie oferty czy parametrach w kategorii),
     *  a także ich sortowanie wg dowolnego z dostępnych typów. Domyślnie (bez podania identyfikatora kategorii/działu) zwracana jest lista wszystkich kategorii głównych
     *  (bez informacji o ofertach) oraz lista stałych filtrów dla listingu ofert (w tym lista działów). Logika działania metody została oparta na systemie dynamicznych
     *  filtrów, zwracanych kontekstowo i pozwalających precyzyjnie sterować zakresem zwracanych danych. Tutorial opisujący zasadę działania dynamicznych filtrów znajduje
     *  się pod adresem: http://allegro.pl/webapi/tutorials.php/tutorial/id,281. 
     * Metoda zwraca dane z ostatnich dwóch miesięcy (także aukcje zakończone, które nie zostały jeszcze przeniesione do archiwum).
     * 
     * @param array $Options
     * @return array
     */
    public function GetItemsList($Options) {
        return $this->_client->doGetItemsList(
                        self::COUNTRY_CODE, $this->_config['allegro_key'], $Options['filterOptions'], $Options['sortOptions'], $Options['resultSize'], $Options['resultOffset'], $Options['resultScope']
        );
    }

    /**
     * Metoda pozwala na pobranie listy parametrów dostępnych dla danej kategorii we wskazanym kraju.
     * Wybrane parametry mogą następnie posłużyć np. do budowy filtra przy listowaniu
     * zawartości kategorii z poziomu metody doShowCat.
     * (http://allegro.pl/webapi/documentation.php/show/id,90)
     *
     * @param int $Cat
     * @return array
     */
    public function GetSellFormAttribs($Cat) {
        return $this->_client->doGetSellFormAttribs(
                        self::COUNTRY_CODE, $this->_config['allegro_key'], '0', $Cat
        );
    }

    /**
     * Metoda pozwala na pobranie listingu wszystkich aukcji promowanych obecnie w kategoriach specjalnych
     * (1000 najnowszych, kończące się, promowane na stronie głównej serwisu, promowane na stronach
     * poszczególnych kategorii, aukcje Eko-Użytkowników). Zwracanych jest zawsze 50 aukcji posortowanych
     * rosnąco po czasie zakończenia. Sterowanie pobieraniem kolejnych porcji danych umożliwia parametr offset.
     * (http://allegro.pl/webapi/documentation.php/show/id,100)
     *
     * @param array $Options
     * @return array
     */
    public function GetSpecialItems($Options) {
        $this->checkConnection();
        return $this->_client->doGetSpecialItems(
                        $this->_session['session-handle-part'], $Options['special-type'], $Options['special-group'], $Options['offset']
        );
    }

    /**
     * Metoda pozwala na obsługę mechanizmu wyszukiwarki (wraz z opcjami wyszukiwarki zaawansowanej).
     * Domyślnie zwracanych jest 50 pasujących do zapytania aukcji, posortowanych rosnąco po czasie
     * zakończenia (najpierw listowane są przedmioty z wykupioną opcją promowania, następnie te niepromowane).
     * Dodatkowo zwracana jest również informacja o łącznej liczbie znalezionych aukcji. Rozmiar porcji
     * pozwala regulować parametr search-limit, a sterowanie pobieraniem kolejnych porcji danych umożliwia
     * parametr search-offset. Metoda zapewnia także obsługę mechanizmu słów pomijanych przez
     * wyszukiwarkę - w przypadku gdy słowo takie będzie częścią zapytania, informacja o tym zwrócona
     * zostanie w tablicy search-excluded-words.
     * (http://allegro.pl/webapi/documentation.php/show/id,116)
     *
     * @param array $Query
     * @return array
     */
    public function Search($Query) {
        $this->checkConnection();
        return $this->_client->doSearch(
                        $this->_session['session-handle-part'], $Query
        );
    }

    /**
     * Metoda pozwala na pobranie listingu wszystkich aukcji trwających obecnie we wskazanej kategorii
     * (wraz z dodatkowymi informacjami o kategoriach spokrewnionych z daną kategorią). Domyślnie zwracanych
     * jest 50 aukcji posortowanych rosnąco po czasie zakończenia (najpierw listowane są przedmioty z
     * wykupioną opcją promowania, następnie te niepromowane). Rozmiar porcji pozwala regulować parametr
     * cat-items-limit, a sterowanie pobieraniem kolejnych porcji danych umożliwia parametr cats-items-offset.
     * (http://allegro.pl/webapi/documentation.php/show/id,362)
     *
     * @param array $Options
     * @return array
     */
    public function ShowCat($Options) {
        $this->checkConnection();
        return $this->_client->doShowCat(
                        $this->_session['session-handle-part'], $Options['cat-id'], $Options['cat-item-state'], $Options['cat-item-option'], $Options['cat-item-duration-option'], $Options['cat-attrib-fields'], $Options['cat-sort-options'], $Options['cat-items-price'], $Options['cat-items-offset'], $Options['cat-items-limit']
        );
    }

    /*     * ********************************************************************************************************
     * Przydatne funkcje
     * ******************************************************************************************************* */

    /**
     * Sprawdzanie połączenia oraz poprawnego zalogowania do allegro
     */
    private function checkConnection() {
        if (!$this->_session) {
            throw new userException('Nie utworzono połączenia z kontem allegro. Należy użyć metody <strong>Login()</strong>');
        }
    }

    /**
     * Wywołanie dowolnej metody przez SOAP
     *
     * @param string $Method
     * @param string/int/array $Data
     * @return array
     */
    public function getMethod($Method, $Data = array()) {
        return $this->_client->__soapCall($Method, $Data);
    }

    /**
     * Metoda pozwala na pobranie identyfikatora sesji po zalogowaniu.
     * Do wykorzystania z metodą getMethod
     *
     * @return string
     */
    public function getSession() {
        $this->checkConnection();
        return $this->_session['session-handle-part'];
    }

    /**
     * Metoda pozwala na pobranie używanego kodu kraju.
     * Do wykorzystania z metodą getMethod
     *
     * @return int
     */
    public function getCountry() {
        return self::COUNTRY_CODE;
    }

    /**
     * Metoda pozwala na pobranie aktualnie uzywanego klucza WebAPI
     * Do wykorzystania z metodą getMethod
     *
     * @return string
     */
    public function getKey() {
        return $this->_config['allegro_key'];
    }

    /**
     * Metoda pozwala na pobranie klucza wersji WebAPI
     *
     * @return int
     */
    public function getVersion() {
        $version = $this->QuerySysStatus(1);
        return $version['ver-key'];
    }

    /**
     * Metoda pozwala na pobranie wszystkich aktualnie używanych
     * danych konfiguracyjnych
     *
     * @return array
     */
    public function getConfig() {
        return $this->_config;
    }

    /**
     * Konwersja obietu na tablicę
     *
     * @param object $object
     * @return array
     */
    public function objectToArray($object) {
        if (!is_object($object) && !is_array($object))
            return $object;
        if (is_object($object))
            $object = get_object_vars($object);
        return array_map(array('AllegroWebAPI', 'objectToArray'), $object);
    }

    /**
     * Konwertowanie sekund na czas
     *
     * @param int $Secounds
     * @return string
     */
    public function Sec2Time($Secounds) {
        $Time = new DateTime('@' . $Secounds, new DateTimeZone('UTC'));
        $GetTime = array('dni' => $Time->format('z'),
            'godzin' => $Time->format('G'),
            'minut' => $Time->format('i'),
            'sekund' => $Time->format('s')
        );
        if ($GetTime['dni'] > 1) {
            $TimeLeft = $GetTime['dni'] . " dni";
        } else if ($GetTime['dni'] == 1) {
            $TimeLeft = $GetTime['dni'] . " dzień";
        } else if ($GetTime['godzin'] > 1) {
            $TimeLeft = $GetTime['godzin'] . " godzin";
        } else if ($GetTime['godzin'] == 1) {
            $TimeLeft = $GetTime['godzin'] . " godzina";
        } else if ($GetTime['minut'] > 1) {
            $TimeLeft = $GetTime['minut'] . " minut";
        } else if ($GetTime['minut'] == 1) {
            $TimeLeft = $GetTime['minut'] . " minuta";
        } else if ($GetTime['sekund'] > 1) {
            $TimeLeft = $GetTime['sekund'] . " sekund";
        } else if ($GetTime['sekund'] == 1) {
            $TimeLeft = $GetTime['sekund'] . " sekunda";
        }
        return $TimeLeft;
    }

    /**
     * Pozostały czas do końca aukcji
     *
     * @param int $Secounds
     * @return string
     */
    public function EndDate($Secounds) {
        $GetDay = date("N", time() + $Secounds);
        $num = array("1", "2", "3", "4", "5", "6", "7");
        $pl = array("Poniedziałek", "Wtorek", "Środa", "Czwartek", "Piątek", "Sobota", "Niedziela");
        $GetDay = str_replace($num, $pl, $GetDay);
        $GetDate = date("d-m-Y, H:i:s", time() + $Secounds);
        return $GetDay . " " . $GetDate;
    }

    /**
     * Metoda pozwala na pobranie ścieżki kategorii dla podanego w wywołaniu identyfikatora kategorii.
     *
     * @param array $Options
     * @return array
     */
    public function GetCategoryPath($Options) {
        $this->checkConnection();
        return $this->_client->doGetCategoryPath(
                        $this->_session['session-handle-part'], $Options['category-id']
        );
    }

    /**
     * Punktacja użytkowników
     *
     * @param int $Stars
     * @return string
     */
    public function UserStars($Stars) {
        $IconHost = "http://static.allegrostatic.pl/site_images/1/0/stars/";

        if ($Stars > 12500) {
            $Star = "star3125";
            $While = 4;
        } elseif ($Stars > 12499) {
            $Star = "star3125";
            $While = 4;
        } elseif ($Stars > 9374) {
            $Star = "star3125";
            $While = 3;
        } elseif ($Stars > 6249) {
            $Star = "star3125";
            $While = 2;
        } elseif ($Stars > 3124) {
            $Star = "star3125";
            $While = 1;
        } elseif ($Stars > 2499) {
            $Star = "star625";
            $While = 4;
        } elseif ($Stars > 1874) {
            $Star = "star625";
            $While = 3;
        } elseif ($Stars > 1249) {
            $Star = "star625";
            $While = 2;
        } elseif ($Stars > 624) {
            $Star = "star625";
            $While = 1;
        } elseif ($Stars > 499) {
            $Star = "star125";
            $While = 4;
        } elseif ($Stars > 374) {
            $Star = "star125";
            $While = 3;
        } elseif ($Stars > 249) {
            $Star = "star125";
            $While = 2;
        } elseif ($Stars > 124) {
            $Star = "star125";
            $While = 1;
        } elseif ($Stars > 99) {
            $Star = "star25";
            $While = 4;
        } elseif ($Stars > 74) {
            $Star = "star25";
            $While = 3;
        } elseif ($Stars > 49) {
            $Star = "star25";
            $While = 2;
        } elseif ($Stars > 24) {
            $Star = "star25";
            $While = 1;
        } elseif ($Stars > 19) {
            $Star = "star5";
            $While = 4;
        } elseif ($Stars > 14) {
            $Star = "star5";
            $While = 3;
        } elseif ($Stars > 9) {
            $Star = "star5";
            $While = 2;
        } elseif ($Stars > 4) {
            $Star = "star5";
            $While = 1;
        } elseif ($Stars > 3) {
            $Star = "star1";
            $While = 4;
        } elseif ($Stars > 2) {
            $Star = "star1";
            $While = 3;
        } elseif ($Stars > 1) {
            $Star = "star1";
            $While = 2;
        } elseif ($Stars > 0) {
            $Star = "star1";
            $While = 1;
        } elseif ($Stars > -1) {
            $Star = "star1";
            $While = 0;
        }

        for ($i = 1; $i <= $While; $i++) {
            $GetStars .= "<img src='" . $IconHost . $Star . ".gif' title='" . $Stars . " pkt. allegro' style='vertical-align:middle' alt='' />";
        }
        return $GetStars;
    }

}
?>
