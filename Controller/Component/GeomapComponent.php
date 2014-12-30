<?php

class GeomapComponent extends Component {

    /**
     * @param type $controller 
     */
    function startup(Controller $controller) {
        $this->controller = $controller;
    }

    /**
     * get display message
     * @param type $key 
     */
    public function getErrorMessage() {
        $arr = array("replyCode" => "Error", "replyMsg" => $this->DisplayMessages->display_messages('invalid_address_please_put_valid_address'));
        echo $this->Json->encode($arr);
        exit;
    }

    /**
     * geomap address using google geomap api 
     */
    function getGeomapAddress($lat, $long) {



        $address = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $lat . "," . $long . "&sensor=false");

        $address = json_decode($address);

        //get first index of address list
        if (!empty($address->results[0]->address_components)) {
            $address_components = $address->results[0]->address_components;


            $result['postal_code'] = $this->geomapPostalcode($address_components);
            $country = $this->geomapCountry($address_components);
            $result['country'] = array(
                'long_name' => $country['long_name'],
                'short_name' => $country['short_name']
            );
            $result['state'] = $this->geomapState($address_components);
            $result['city'] = $this->geomapCity($address_components);
            $result['address'] = $this->geomapAddress($address_components);
            $result['extended_address'] = $address->results[0]->formatted_address;
        } else {

            $result['postal_code'] = '';
            $result['country'] = '';
            $result['state'] = '';
            $result['city'] = '';
            $result['address'] = '';
            $result['extended_address'] = '';
        }


        return $result;
    }

    function geomapPostalcode($geoData) {

        $postal_code = '';
        foreach ($geoData as $value) {
            foreach ($value->types as $v) {
                if ($v == 'postal_code') {
                    return $value->long_name;
                }
            }
        }

        return $postal_code;
    }

    function geomapCountry($geoData) {

        $country = array('long_name' => '', 'short_name' => '');
        foreach ($geoData as $value) {
            foreach ($value->types as $v) {
                if ($v == 'country') {
                    $country['long_name'] = $value->long_name;
                    $country['short_name'] = $value->short_name;

                    return $country;
                }
            }
        }
        return $country;
    }

    function geomapState($geoData) {

        $postal_state = '';
        foreach ($geoData as $value) {
            foreach ($value->types as $v) {
                if ($v == 'administrative_area_level_1') {
                    return $value->long_name;
                }
            }
        }
        return $postal_state;
    }

    function geomapCity($geoData) {

        $postal_city = '';
        foreach ($geoData as $value) {
            foreach ($value->types as $v) {
                if ($v == 'locality') {
                    return $value->long_name;
                }
            }
        }
        return $postal_city;
    }

    function geomapAddress($geoData) {

        $address = '';
        foreach ($geoData as $value) {
            foreach ($value->types as $v) {
                if ($v == 'street_number') {
                    $address .= $value->long_name . ', ';
                }
            }
        }
        foreach ($geoData as $value) {
            foreach ($value->types as $v) {
                if ($v == 'route') {
                    $address .= $value->long_name . ', ';
                }
            }
        }
        foreach ($geoData as $value) {
            foreach ($value->types as $v) {
                if ($v == 'neighborhood') {
                    $address .= $value->long_name;
                }
            }
        }

        return trim($address, ',');
    }

    function geo_address($host, $name) {

        /* IP LOCATION FUNCTION TO CREATE AND RETURN DATA */

        $ip = @gethostbyname($host);
        $locationData = CakeSession::read('locationData');

        /* CHECK FOR LOCAL SYSTEM */
        if (substr($ip, 0, 3) == '192') {

            $result['statusCode'] = $ip;
            $result['ipAddress'] = $ip;
            $result['countryCode'] = "IN";
            $result['countryName'] = "INDIA";
            $result['regionName'] = "RAJASTHAN";
            $result['cityName'] = "JAIPUR";
            $result['address'] = $result['cityName'] . ' ' . $result['regionName'] . ' ' . $result['countryCode'];
            $result['zipCode'] = "";
            $result['timeZone'] = "+5:30";
            $result['latitude'] = '26.9260';
            $result['longitude'] = '75.8235';
            CakeSession::write('locationData', $result);
            return $locationData = CakeSession::read('locationData');
        }

        if (!empty($locationData)) {
            return $locationData;
        }
        if (preg_match('/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/', $ip)) {
            $xml = @file_get_contents('http://api.ipinfodb.com/v3/' . $name . '/?key=e03ec524c314a5d8850824b95c5a02ab5334338eb75707825a87ded5849c4465&ip=' . $ip . '&format=xml');
            try {
                $response = @new SimpleXMLElement($xml);
                foreach ($response as $field => $value) {
                    $result[(string) $field] = (string) $value;
                }
                if ($result['countryCode'] != '' && $result['countryCode'] != '-') {
                    $result['address'] = $result['cityName'] . ' ' . $result['regionName'] . ' ' . $result['countryCode'];
                    if (empty($locationData)) {
                        CakeSession::write('locationData', $result);
                    }
                }
                return $result;
            } catch (Exception $e) {
                
            }
        }
        return;
    }

    function get_lat_long_byaddress($address, $ajax = null) {

        $result = array();
        if (isset($address) && $address != '') {


            $address = str_replace(" ", "+", $address);

            $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false");
            $json = json_decode($json);
            $userData = CakeSession::read('Auth.User');

            if ($json->{'status'} == 'OK') {

                $result['address'] = $json->{'results'}[0]->{'formatted_address'};
                $result['latitude'] = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                $result['longitude'] = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};

                CakeSession::write('locationData', $result);
            }
        }
        if (isset($ajax) && $ajax == 1) {
            echo json_encode(CakeSession::read('locationData'));
        } else {
            return $result;
        }
    }

}
