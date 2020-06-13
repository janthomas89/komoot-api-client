<?php

namespace Janthomas89\KomootApiClient;

use Janthomas89\KomootApiClient\Exception\KomootApiClientException;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Class to connect to a Komoot profile, list tours and
 * download GPX tracks.
 *
 * @package Janthomas89\KomootApiClient
 */
class KomootApiClient
{
    const SIGN_IN_URI = 'https://account.komoot.com/v1/signin';
    const SIGN_IN_TRANSFER_URI = 'https://account.komoot.com/actions/transfer?type=signin';
    const TOURS_URI_TPL = 'https://www.komoot.de/user/%s/tours';
    const TOUR_GPX_URI_TPL = 'https://www.komoot.de/tour/%s/download';

    private HttpBrowser $browser;
    private int $userId;

    /**
     * @param $email
     * @param $password
     */
    public function __construct(string $email, string $password)
    {
        $this->browser = new HttpBrowser(HttpClient::create());
        $this->browser->followRedirects(true);


        $this->login($email, $password);
    }

    /**
     * Logs in the user.
     *
     * @param string $email
     * @param string $password
     */
    private function login(string $email, string $password)
    {
        $this->browser->request('POST', self::SIGN_IN_URI, [
            'email' => $email,
            'password' => $password
        ]);

        /** @var Response $loginResponse */
        $loginResponse = $this->browser->getResponse();
        $responseData = json_decode($loginResponse->getContent(), true);

        if (!isset($responseData['type']) || $responseData['type'] !== 'logged_in') {
            throw new KomootApiClientException(
                'Unable to login user. Invalid response data: ' . $loginResponse->getContent()
            );
        }

        $crawler = $this->browser->request('GET', self::SIGN_IN_TRANSFER_URI);

        /** @var Response $transferLoginResponse */
        $transferLoginResponse = $this->browser->getResponse();

        if ($transferLoginResponse->getStatusCode() !== 200) {
            throw new KomootApiClientException(
                'Unable to transfer login. Invalid status code given: ' . $transferLoginResponse->getStatusCode()
            );
        }

        $this->userId = $this->extractUserId($crawler);
    }

    /**
     * Tries to extract the logged in user id for the given crawler instance. We rely on
     * the assumption that the first /user/... link is the logge din user id. Somehow
     * hacky, but works at the moment.
     *
     * @param Crawler $crawler
     * @return int
     */
    private function extractUserId(Crawler $crawler): int
    {
        $links = $crawler->filter('a')->links();
        foreach ($links as $link) {
            if (preg_match('~/user/(\d+)$~', $link->getUri(), $matches)) {
                return (int)$matches[1];
            }
        }

        throw new KomootApiClientException('Unable to retrieve user id');
    }

    /**
     * Returns a list of the latest tour ids.
     *
     * @return int[]
     */
    public function getLatestTourIds(): array
    {
        $uri = sprintf(self::TOURS_URI_TPL, $this->userId);
        $crawler = $this->browser->request('GET', $uri);

        /** @var Response $response */
        $response = $this->browser->getResponse();

        if ($response->getStatusCode() !== 200) {
            throw new KomootApiClientException(
                'Unable to get latest tour ids. Invalid status code given: ' . $response->getStatusCode()
            );
        }

        return $crawler->filter('[data-tour-id]')->each(function (Crawler $node) {
            return (int)$node->attr('data-tour-id');
        });
    }

    /**
     * Downloads the GPX file for the given tur id.
     *
     * @param int $tourId
     * @return string
     */
    public function getTourGpx(int $tourId): string
    {
        $uri = sprintf(self::TOUR_GPX_URI_TPL, $tourId);
        $this->browser->request('GET', $uri);

        /** @var Response $response */
        $response = $this->browser->getResponse();

        if ($response->getStatusCode() !== 200) {
            throw new KomootApiClientException(
                'Unable to download tour GPX. Invalid status code given: '
                . $response->getStatusCode()
            );
        }

        if ($response->getHeader('Content-Type') !== 'application/gpx+xml') {
            throw new KomootApiClientException(
                'Unable to download tour GPX. Invalid content type given: '
                . $response->getHeader('Content-Type')
            );
        }

        return $response->getContent();
    }
}