# Cloudflare dynamic DNS

Checks if your external IP still matches a domain A record on [Cloudflare](http://cloudflare.com). If a change is detected it will update the record. It uses the Cloudflare API 4.0 to achieve this.

## Run

`docker run kidk/cloudflare-dyndns --username test@email.com --api_key 13b9840c523423fd461f6cf481530c47b0e98 --zone 99c0f2f7d53c3425235730ef9cdc80c --domain my.domain.com`

## Arguments

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

    -t/--timeout <time>
         Minutes to wait before checking again

## Questions or bugs
Create an issue or contact me on [Twitter @kidk](https://twitter.com/kidk).
