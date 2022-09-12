## OpenCATS Security

We take security very seriously. We welcome any peer review of our code to ensure OpenCATS can evolve to protect against new vulnerabilities.

### Where should I report security issues?

In order to give the community time to respond and upgrade we strongly urge you report all security issues privately. Please email russh@opencats.org to provide details and repro steps and any mitigating changes you have identified.

### Password Storage

OpenCATS uses MD5 hashing to store passwords. This will be replaced in future versions

### XSS

The main vector for [XSS](https://en.wikipedia.org/wiki/Cross-site_scripting) attacks is via the career portal (which is disabled by default). htmlspecialchars is used to protect career portal form submissions. Back-end (non-public) web-pages remain vulnerable to XSS. This will be deployed in future releases.

### Malicious uploads

The OpenCATS career portal permits resume uploads. Please review and configure .htaccess as per the [security guidance](https://documentation.opencats.org/technical-configuration-options/vital-security-restrict-access-to-upload-folders-.htaccess) to restrict malicious uploads.

### Composer

Composer vulnerabilities are released often and will require a review of the Composer.lock file to move to known good versions of dependencies. Other dependencies within the Composer requiements are needed only for testing and can be removed from produciton systems. These remain to be documented.

### Deployment concerns

We strongly recommend that users check file and folder permissions as per the [documentation](https://documentation.opencats.org). As always, any passwords in use for MariaDB or server hosting should be strongly constructed and unique.

RussH.
