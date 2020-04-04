<?php

namespace Mitra\ActivityPub\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use phpseclib\Crypt\RSA;

final class HttpSignature
{
    private const SIGNATURE_PATTERN = '/^
        keyId="(?P<keyId>
            (?:https?:\/\/[\w\-\.]+[\w]+)
            (?::[\d]+)?
            (?:[\w\-\.#\/@]+)
        )",
        (?:headers="\(request-target\) (?P<headers>[\w\s]+)",)?
        signature="(?P<signature>[\w+\/]+==)"
    /x';

    /**
     * Verify an incoming message based upon its HTTP signature
     *
     * @param ServerRequestInterface $request
     * @param callable $loadPublicKeyPem
     * @return bool True if signature has been verified. Otherwise false
     */
    public static function verify(ServerRequestInterface $request, callable $loadPublicKeyPem): bool
    {
        // Read the Signature header,
        $signature = $request->getHeaderLine('signature');

        if (!$signature) {
            return false;
        }

        // Split it into its parts (keyId, headers and signature)
        if (false === $parts = static::splitSignature($signature)) {
            return false;
        }

        $publicKeyPem = $loadPublicKeyPem($parts['keyId']);

        // Create a comparison string from the plaintext headers we got
        // in the same order as was given in the signature header,
        $data = static::getPlainText(
            explode(' ', trim($parts['headers'])),
            $request
        );

        // Verify that string using the public key and the original
        // signature.
        $rsa = new RSA();
        $rsa->setHash("sha256");
        $rsa->setSignatureMode(RSA::SIGNATURE_PSS);
        $rsa->loadKey($publicKeyPem);

        return $rsa->verify($data, base64_decode($parts['signature'], true));
    }

    /**
     * @param RequestInterface $request The request to sign
     * @param string $privateKey The private key content
     * @param string $publicKeyUrl
     * @param array $headers The headers to respect in the signature
     * @return RequestInterface The signed request
     */
    public static function sign(
        RequestInterface $request,
        string $privateKey,
        string $publicKeyUrl,
        array $headers = ['Host', 'Date']
    ): RequestInterface {
        $plaintext = self::getPlainText($headers, $request);

        $rsa = new RSA();
        $rsa->loadKey($privateKey);
        $rsa->setHash('sha256');
        $rsa->setSignatureMode(RSA::SIGNATURE_PSS);

        $signature = $rsa->sign($plaintext);

        return $request->withHeader('Signature', sprintf(
            'keyId="%s",headers="(request-target) host date",signature="%s"',
            $publicKeyUrl,
            base64_encode($signature)
        ));
    }

    /**
     * Split HTTP signature into its parts (keyId, headers and signature)
     *
     * @param string $signature
     * @return bool|array
     */
    private static function splitSignature(string $signature)
    {
        $matches = [];

        if (!preg_match(self::SIGNATURE_PATTERN, $signature, $matches)) {
            return false;
        }

        // Headers are optional
        if (!isset($matches['headers']) || $matches['headers'] == '') {
            $matches['headers'] = 'date';
        }

        return [
            'keyId' => $matches['keyId'],
            'signature' => $matches['signature'],
            'headers' => $matches['headers']
        ];
    }

    /**
     * Get plain text that has been originally signed
     *
     * @param  array $headers HTTP header keys
     * @param  RequestInterface $request
     * @return string
     */
    private static function getPlainText(array $headers, RequestInterface $request)
    {
        $strings = [];
        $strings[] = sprintf(
            '(request-target) %s %s%s',
            strtolower($request->getMethod()),
            $request->getUri()->getPath(),
            $request->getUri()->getQuery()
                ? '?' . $request->getUri()->getQuery() : ''
        );

        foreach ($headers as $key) {
            if ($request->hasHeader($key)) {
                $lowercaseHeaderName = strtolower($key);
                $strings[] = "$lowercaseHeaderName: " . $request->getHeaderLine($key);
            }
        }

        return implode("\n", $strings);
    }
}
