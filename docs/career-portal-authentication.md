# Career Portal candidate authentication

Career Portal and candidate registration remain disabled by default. When enabled,
returning candidates select a current vacancy and POST to
`index.php?m=careers&p=candidateLogin&ID=<jobOrderID>` (also reachable through the
`careers/index.php` entry point). Treat this as a sensitive public endpoint.
The existing Careers router also accepts `p` in POST data: deployment rules must
cover that form, duplicate parameters and both entry points, or restrict the
entire public Careers POST surface instead of relying only on a query-string match.

Login checks the job against the public portal listing (including sharing status
and administrative visibility), then consumes the existing CAPTCHA before looking
up candidate details. Successful verification rotates the PHP session ID and
stores only `careerPortalCandidateID` server-side. Later profile and application
operations use that ID. Logout clears it and rotates the session, retaining
unrelated OpenCATS session state.

There is no persistent “Remember my information” authentication. Old `cats…cw`
cookies are ignored; no code reads or writes them, including after an application
or profile update. New applicants still use the existing application workflow;
submitting an application does not itself establish an authenticated session.
Custom registration templates should remove any remaining custom remember-me
labels. The standard legacy placeholder and wording are removed when rendered.

## Optional deployment throttling

Operators exposing candidate registration may apply approximately **5–10 login
attempts per minute per source IP**, with a small burst allowance, using nginx
[`limit_req`](https://nginx.org/en/docs/http/ngx_http_limit_req_module.html),
Apache/mod_security, HAProxy stick-table request-rate rules, or a Cloudflare/WAF
rate-limiting rule. For nginx, a scoped `limit_req_zone` with `rate=10r/m` and
`limit_req ... burst=3 nodelay` illustrates the policy; integrate it with the
existing PHP routing and test all request forms above. Configure trusted-proxy
client IP handling and allow for users sharing an address.

This is deployment guidance, not an OpenCATS security guarantee. CAPTCHA and
appropriate candidate authentication remain necessary; IP throttling alone does
not stop distributed attacks. Internal installations that do not expose the
Career Portal need none of these rules. This patch adds no application-level
attempt counters, delays or rate limiter.

## Separate follow-up

Email, last name and postcode remain low-entropy verification details. A separate
issue should consider **email + a securely generated random candidate access
credential**, storing only its hash server-side. That work is not implemented
here and need not become a username/password/password-reset subsystem.
