# cubeupload image server

# About

This is the image server component of cubeupload.

# Intended use

The image server is designed to sit behind a reverse cache such as Varnish. The image server doesn't do any content caching.

A typical request looks like:

Request > nginx https proxy > varnish cache > nginx image server vhost > php-fpm > S3

## nginx https proxy

nginx handles incoming HTTPS requests because Varnish doesn't support ssl/tls. HTTP requests can be sent straight to the Varnish cache.


## Varnish

The Varnish component caches any results from the image server. The idea is to minimise the amount of communication with S3 to help save on bills.

Deletion requests from the cubeupload backend will contact the Varnish caches to ensure files are deleted everywhere. The deletion request should also be passed to the image server.


## Image Server

The image server does the real work. It accepts incoming file requests, looks up the filename in the database (or cache), retrieves from S3 and returns to the requestor.

Hash lookups should be cached to reduce the number of database requests if the cache expires.

Deletion requests should also reach the image server so it can clear its own caches. The actual S3 content is managed by the backend.