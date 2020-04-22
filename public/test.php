<?php

declare(strict_types=1);

namespace Mitra;

use Cache\Adapter\PHPArray\ArrayCachePool;
use HttpSignatures\Algorithm;
use HttpSignatures\HeaderList;
use HttpSignatures\Key;
use HttpSignatures\Signer;
use HttpSignatures\Verifier;
use Mitra\Env\Env;
use Mitra\Env\Reader\DelegateReader;
use Mitra\Env\Reader\EnvVarReader;
use Mitra\Env\Reader\GetenvReader;
use Mitra\Env\Writer\NullWriter;
use Psr\Http\Message\RequestFactoryInterface;

require __DIR__ . '/../vendor/autoload.php';

$signatureHeader = "keyId=\"https://mastodon.social/actor#main-key\",algorithm=\"rsa-sha256\",headers=\"(request-target) host date accept\",signature=\"EIWoEa4NZCf35/7CPemxZqQGz9txTLFc7H+2CYeIHPp12x/gjlEhIAa29u8h06IiJzv4ytUyeEwMG4VTExg4z/cvR+QM2FYkF3L8Go5jgy5YzC3NKzCrhI4yYglKTfMT1Yo6FtiddrUhY+OkJhqBX58L4Qdy0iKCW4B12l993Vzn8N6XWR5Y3Y9xV757tHCjXFEF7sELdIW+/Z2EakHbyTUG97T0hd7rZNM9pj4M7HR22+huzNff2YBqYo2LKSD8SZC3Ig0WDmaeSwRJTYkZa9w7s6RxLHtn0h41Uxq879x2/0NPzrNIxP6wIFJTT1ZhYV5XqNKgM3ijUeQUq3fpGQ==\"";

$env = Env::immutable(
    new DelegateReader([new GetenvReader(), new EnvVarReader()]),
    new NullWriter(),
    new ArrayCachePool()
);

$container = AppContainer::init($env);

/** @var RequestFactoryInterface $requestFactory */
$requestFactory = $container[RequestFactoryInterface::class];
$request = $requestFactory->createRequest('GET', 'http://mitra-social.herokuapp.com/user/TiMESPLiNTER')
    ->withHeader('host', 'mitra-social.herokuapp.com')
    ->withHeader('date', 'Mon, 06 Apr 2020 06:50:51 GMT')
    ->withHeader('accept', 'application/activity+json, application/ld+json');

/*$request = $request->withHeader('Signature', $signatureHeader);
/** @var Verifier $verifier /
$verifier = $container[Verifier::class];

var_dump($verifier->isSigned($request));*/

$request = (new Signer(
    new Key('https://mitra-social.herokuapp.com/user/TiMESPLiNTER#main-key', file_get_contents(__DIR__ . '/../fixtures/resources/john.doe-private-key')),
    Algorithm::create('rsa-sha256'),
    new HeaderList(['(request-target)', 'Host', 'Date', 'Accept'])
))->sign($request);

var_dump($request->getHeaderLine('signature'));
