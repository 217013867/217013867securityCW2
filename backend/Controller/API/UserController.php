<?php

include dirname(__FILE__) . "/../../utils.php";

class UserController extends BaseController
{
    /**
     * "/user/list" Endpoint - Get list of users
     */
    public function listAction()
    {
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();

        $strErrorDesc = '';
        if (strtoupper($requestMethod) == 'GET') {
            try {
                $userModel = new UserModel();
                $intLimit = 10;
                if (isset($arrQueryStringParams['limit']) && $arrQueryStringParams['limit']) {
                    $intLimit = $arrQueryStringParams['limit'];
                }
                $arrUsers = $userModel->getUsers($intLimit);
                $responseData = json_encode($arrUsers);
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }

        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                json_encode(array("result" => $responseData)),
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }


    public function validateAction()
    {
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $strErrorDesc = '';

        $data = json_decode(file_get_contents('php://input'), true);

        if (strtoupper($requestMethod) == 'POST') {

            try {
                // decrypt data by private key

                $private_key = file_get_contents("../rsa_key/server.key");

                $a = openssl_private_decrypt(base64_decode($data['data']), $decrypted, openssl_pkey_get_private($private_key));
                if (!$a) {
                    $this->sendOutput(json_encode(array('error' => "Cannot Validate")),
                        array('Content-Type: application/json')
                    );
                }
                $data = json_decode($decrypted, true);

                $userModel = new UserModel();

                // compare hashed hkid with user input hkid
                $hkid = hash('sha512', $data['hkid']);

                $arrUsers = $userModel->getHKID($hkid, (int)$data['date'], (int)$data['year']);


            } catch
            (Error $e) {
                $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }

            if (!$strErrorDesc) {
                $this->sendOutput(
                    count($arrUsers),
                    array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                );
            } else {
                $this->sendOutput(json_encode(array('error' => $strErrorDesc)),
                    array('Content-Type: application/json', $strErrorHeader)
                );
            }
        }
    }

    public function submitAction()
    {
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $strErrorDesc = '';
        $data = json_decode(file_get_contents('php://input'), true);
        if (strtoupper($requestMethod) == 'POST') {

            try {

                // check recaptcha
                $configStr = file_get_contents("./config.json");
                $config = json_decode($configStr, true);
                $secretKey = $config['recaptchaKey'];

                // post request to server
                $url = 'https://www.google.com/recaptcha/api/siteverify';
                $data = array('secret' => $secretKey, 'response' => $data['token']);

                $options = array(
                    'http' => array(
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method' => 'POST',
                        'content' => http_build_query($data)
                    )
                );
                $context = stream_context_create($options);
                $response = file_get_contents($url, false, $context);
                $responseKeys = json_decode($response, true);


                if ($responseKeys["success"]) {
                    if ($responseKeys['score'] < 0.7)
                        $this->sendOutput(
                            json_encode(array("message" => "Not a human on9")),
                            array('Content-Type: application/json', 'HTTP/1.1 400 Bad Request')
                        );
                }

                // start to validate data
                $validateResult = true;
                // validate income data
                foreach ($data as $key => $value) {
                    if (!validate($value, $key)) {
                        $validateResult = false;
                        break;
                    }
                }

                if (!$validateResult) {
                    $this->sendOutput(
                        json_encode(array("message" => "Invalid data format")),
                        array('Content-Type: application/json', 'HTTP/1.1 400 Bad Request')
                    );
                }

                $userModel = new UserModel();

                // get id of hkid
                $id = $userModel->getHKIDID($data['hkid'], (int)$data['date'], (int)$data['year']);
                if (count($id) == 0) {
                    $this->sendOutput(
                        json_encode(array("message" => "Invalid HKID")),
                        array('Content-Type: application/json', 'HTTP/1.1 400 Bad Request')
                    );
                } else {
                    $result = $userModel->insertHKID($id[0]['id'], $data['email'], $data['phone'],
                        $data['selectedDate'], $data['selectedTime'], $data['location']);

                }


            } catch
            (Error $e) {
                $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }

            if (!$strErrorDesc) {
                if ($result) {
                    $this->sendOutput(
                        json_encode(array("result" => $result)),
                        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                    );
                } else {
                    $this->sendOutput(
                        json_encode(array("result" => $result)),
                        array('Content-Type: application/json', 'HTTP/1.1 400 Bad Request')
                    );
                }
            } else {
                $this->sendOutput(json_encode(array('error' => $strErrorDesc)),
                    array('Content-Type: application/json', $strErrorHeader)
                );
            }
        }
    }
}