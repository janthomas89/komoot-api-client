# Komoot API Client

Simple, unofficial Komoot API client that can only do two things:
1) List a user's latest tour ids 
2) Download a user's tour GPX track

**Warnig:** The API client needs the user's credentials, which is basically a security nogo.
It is just meant for a private project with my own Komoot account.
In a public setting, one would prefer an OAuth based login using the official Komoot API.
However, Komoot does grant access to its API for private purpose.

## Install
`composer require janthomas89/komoot-api-client`

## Usage
### List a user's latest tour ids
```
$komoot = new KomootApiClient('foo@bar.de', 'yourPassword');
$latestTourIds = $komoot->getLatestTourIds();
```
 
### Download a user's tour GPX track
```
$komoot = new KomootApiClient('foo@bar.de', 'yourPassword');
$tourGpx = $komoot->getTourGpx(123456);
```
