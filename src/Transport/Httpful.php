<?php

namespace App\Transport;

use Httpful\Mime;
use Httpful\Request;
use Httpful\Response;

/**
 * Class Httpful
 * @package App\Transport
 */
class Httpful
{
    /**
     * @var string
     */
    const API_ENDPOINT = "https://apifeed.sellsy.com/0/";

    /**
     * @var string
     */
    protected $oauthConsumerKey;

    /**
     * @var string
     */
    protected $oauthSignature;

    /**
     * @var string
     */
    protected $oauthToken;

    /**
     * @param string $consumerToken
     * @param string $consumerSecret
     * @param string $userToken
     * @param string $userSecret
     */
    public function __construct($consumerToken, $consumerSecret, $userToken, $userSecret)
    {
        $this->oauthConsumerKey = rawurlencode($consumerToken);
        $this->oauthToken = rawurlencode($userToken);
        $this->oauthSignature = rawurlencode(rawurlencode($consumerSecret).'&'.rawurlencode($userSecret));
    }

    /**
     * @param array $requestSettings
     * @return mixed
     */
    public function call(array $requestSettings)
    {
        try {
            /** @var Response $httpResponse */
            $httpResponse = Request::post(self::API_ENDPOINT)
                ->addHeader('Authorization', sprintf(
                    "OAuth %s, %s, %s, %s, %s, %s, %s",
                    sprintf('oauth_consumer_key="%s"',      $this->oauthConsumerKey),
                    sprintf('oauth_token="%s"',             $this->oauthToken),
                    sprintf('oauth_nonce="%s"',             md5(time() + rand(0,1000))),
                    sprintf('oauth_timestamp="%s"',         time()),
                    sprintf('oauth_signature_method="%s"',  'PLAINTEXT'),
                    sprintf('oauth_signature="%s"',         $this->oauthSignature),
                    sprintf('oauth_version="%s"',           '1.0')
                ))
                ->contentType(Mime::FORM)
                ->body(array(
                    'request' => 1,
                    'io_mode' => 'json',
                    'do_in' => json_encode($requestSettings),
                ))
                ->send();

            //OAuth issue : Invalid signature
            if (false !== strpos($httpResponse->body, 'oauth_problem=signature_invalid')) {
                throw new \Exception("The oauth signature is invalid, please verify the authentication credentials provided");
            }

            //OAuth issue : Consummer refused
            if (false !== strpos($httpResponse->body, 'oauth_problem=consumer_key_refused)')) {
                throw new \Exception("The consummer key has been refused, please verify it still valid");
            }

            $apiResponse = json_decode($httpResponse->body);

            // Sometimes Sellsy send an empty response ; I suppose it append when an internal error append in Sellsy API
            if (is_null($apiResponse)) {
                throw new \Exception(sprintf(
                    "An unexpected error occurred when contacting the Sellsy API, the response is null with HTTP Code %s",
                    $httpResponse->code
                ));
            }

            if ($apiResponse->status != 'success') {
                $message = $apiResponse;

                if (is_object($apiResponse)) {
                    $message = $apiResponse->error;

                    if (isset($apiResponse->more)) {
                        $message .= ' | ' . $apiResponse->more;
                    }

                    if (is_object($apiResponse->error)) {
                        $message = $apiResponse->error->message;

                        if (isset($apiResponse->error->more)) {
                            $message .= ' | ' . $apiResponse->error->more;
                        }
                    }
                }

                throw new \Exception($message);
            }

            return $apiResponse;
        }
        catch(\Exception $e) {
            throw new \RuntimeException(
                sprintf(
                    'An error occurred during the call of Sellsy API with message "%s". The response is "%s".',
                    $e->getMessage(),
                    isset($httpResponse) ? $httpResponse->raw_body : ""
                ),
                $e->getCode(),
                $e
            );
        }
    }
}