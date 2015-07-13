# Cloudflare dynamic DNS

Checks if your external IP still matches a domain A record on [Cloudflare](http://cloudflare.com). If a change is detected it will update the record.

## Install

1. Clone repository
2. Install PHP > 5.3 with curl support
3. Install [composer](https://getcomposer.org/download/)
4. `$ composer install`

## Arguments
    php update.php


    -k/--api_key <argument>
         Required. Cloudflare API key


    -d/--domain <argument>
         Required. Domain name


    --help
         Show the help page for this command.


    -u/--username <argument>
         Required. Cloudflare username


    -z/--zone <argument>
         Required. Cloudflare zone

## Example
`php update.php --username test@email.com --api_key 13b9840c523423fd461f6cf481530c47b0e98 --zone 99c0f2f7d53c3425235730ef9cdc80c --domain my.domain.com`

## Questions or bugs
Create an issue or contact me on [Twitter @kidk](https://twitter.com/kidk).
