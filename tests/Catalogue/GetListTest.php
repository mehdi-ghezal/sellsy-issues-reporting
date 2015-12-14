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

    public function testDuplicateDataPage1()
    {
        $identifiers = array();

        foreach($this->getData(1) as $result) {
            $identifiers[] = $result->ident;
        }

        $this->assertEquals(count($identifiers), count(array_unique($identifiers)));
    }

    public function testDuplicateDataPage2()
    {
        $identifiers = array();

        foreach($this->getData(2) as $result) {
            $identifiers[] = $result->ident;
        }

        $this->assertEquals(count($identifiers), count(array_unique($identifiers)));
    }
}
