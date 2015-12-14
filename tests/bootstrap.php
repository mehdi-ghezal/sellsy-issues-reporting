<?php

date_default_timezone_set('Europe/Paris');

require_once __DIR__ . '/../vendor/autoload.php';

if (! file_exists(__DIR__ . '/Fixtures/Credentials.php')) {
    throw new \RuntimeException('You must create a Fixtures/Credentials.php file from Fixtures/Credentials.php.dist');
}

if (! \Tests\Fixtures\Credentials::$consumerToken ||
    ! \Tests\Fixtures\Credentials::$consumerSecret ||
    ! \Tests\Fixtures\Credentials::$userToken ||
    ! \Tests\Fixtures\Credentials::$userSecret) {

    throw new \RuntimeException('You must fill out Credentials.php');
}