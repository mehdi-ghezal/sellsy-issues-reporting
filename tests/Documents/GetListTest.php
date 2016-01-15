<?php

namespace Tests\Documents;

use App\Transport\Httpful;
use Tests\Fixtures\Credentials;

class GetListTest extends \PHPUnit_Framework_TestCase
{
    public function getData($page)
    {
        $transport = new Httpful(
            Credentials::$consumerToken,
            Credentials::$consumerSecret,
            Credentials::$userToken,
            Credentials::$userSecret
        );

        $apiResponse = $transport->call(array(
            'method' => 'Document.getList',
            'params' => array (
                'doctype' => 'estimate',
                'pagination' => array (
                    'pagenum' => $page
                )
            )
        ));

        return $apiResponse->response->result;
    }

    public function testDuplicated()
    {
        $identifiers = array();

        for ($page = 1; $page <= 3 ; $page++) {
            foreach($this->getData($page) as $result) {
                $identifiers[] = $result->ident;
            }
        }

        var_dump($identifiers);

        $this->assertEquals(count($identifiers), count(array_unique($identifiers)));
    }
}
