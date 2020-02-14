# Partial Response PSR-15 Middleware

## Purpose

Google in their [Performance Tips](https://cloud.google.com/storage/docs/json_api/v1/how-tos/performance#partial-response) for
APIs, suggest to limit required bandwidth by filtering out unused fields in response. Their APIs 
support additional URL parameter `fields` which asks API to include only specific fields in response.   

This project wraps [php-value-mask](https://github.com/Messere/php-value-mask) library (which provides
filter parsing and filtering) in [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware, 
which can be used with standards compatible frameworks.    

See [php-value-mask](https://github.com/Messere/php-value-mask) project for more details on
filtering syntax. 

## Acknowledgements

* Google's [API performance tips](https://cloud.google.com/storage/docs/json_api/v1/how-tos/performance#partial-response).
* Some helpers from [Middlewares](https://github.com/middlewares) projects were used

## License

[MIT](LICENSE)
