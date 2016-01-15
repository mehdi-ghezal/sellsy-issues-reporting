<?php

namespace Tests\Catalogue;

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
            'method' => 'Catalogue.getList',
            'params' => array (
                'type' => 'item',
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
                $identifiers[] = $result->id;
            }
        }

        var_dump($identifiers);

        $this->assertEquals(count($identifiers), count(array_unique($identifiers)));
    }
}
