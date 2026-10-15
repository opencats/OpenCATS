## OpenCATS Security

We take security very seriously. We welcome any peer review of our code to ensure OpenCATS can evolve to protect against new vulnerabilities.

### Where should I report security issues?

Please report suspected security vulnerabilities using **GitHub Private Vulnerability Reporting**.

From the OpenCATS GitHub repository, open **Security and quality → Report a vulnerability** and submit the report privately. Please include sufficient detail to reproduce and assess the issue, including affected versions, reproduction steps or proof of concept, expected impact, and any suggested mitigation or fix.

**Do not report security vulnerabilities by email, public GitHub issue, discussion, or pull request.** Public disclosure before a fix is available may put OpenCATS installations at risk.

Valid reports will be handled through a private GitHub Security Advisory so that the reporter and OpenCATS maintainers can discuss, reproduce and remediate the vulnerability confidentially.

For accepted vulnerabilities, the OpenCATS maintainers will **request a CVE through GitHub where appropriate before publication**. If a CVE has already been assigned by another CVE Numbering Authority, please include that information in the private report.

We aim to coordinate publication of the advisory with the availability of a patched OpenCATS release wherever practical.

### Composer

Composer vulnerabilities are released often and will require a review of the Composer.lock file to move to known good versions of dependencies. Other dependencies within the Composer requiements are needed only for testing and can be removed from production systems. These development packages are removed from the releases since version 0.9.7.2, however if you pull in dependencies by using composer (rather than use the releases - ensure you use the --no-dev option as documented here https://documentation.opencats.org/#which-package-to-install)  

### Deployment concerns

We strongly recommend that users check file and folder permissions as per the [documentation](https://documentation.opencats.org). As always, any passwords in use for MariaDB or server hosting should be strongly constructed and unique.
