# Contributing to OpenCATS

Thank you for considering contributing to OpenCATS.

OpenCATS is a long-running open-source Applicant Tracking System. Contributions range from small PHP, JavaScript and code-quality fixes through database performance improvements, UI modernisation and substantial new recruitment functionality.

We particularly welcome focused contributions that improve the existing application without unnecessarily increasing its complexity.

## Looking for something to work on?

The best place to start is the:

[OpenCATS Modernisation & Contributor Roadmap](https://github.com/orgs/opencats/projects/2)

The roadmap contains work ranging from small contributor-friendly fixes through to larger future projects.

It includes areas such as:

- PHP 8.4 / PHP 8.5 cleanup
- legacy PHP removal
- Codacy/code-quality improvements
- database and query optimisation
- automated testing
- UI modernisation
- recruiter workflow improvements
- candidate and job-order improvements
- Boolean and proximity searching
- candidate/job matching
- email and communications
- API development
- semantic search and AI-assisted matching

The Project includes filters for contributor-friendly work, area, effort and priority.

If you are new to OpenCATS, starting with a **Small**, **Contributor Friendly** item is recommended.

## Before starting work

For a small bug fix or clearly defined roadmap item, feel free to begin work.

For substantial features, architectural changes or changes which affect the database schema, please discuss the proposed approach first.

This helps avoid contributors spending significant time implementing something which:

- duplicates existing OpenCATS functionality;
- conflicts with work already underway;
- introduces unnecessary architecture or dependencies;
- would need substantial redesign before it could be merged.

If proposing new functionality, please check the [Modernisation & Contributor Roadmap](https://github.com/orgs/opencats/projects/2) first.

For significant new features which are not already on the roadmap, please open an Issue or Discussion before beginning implementation.

## Supported PHP versions

OpenCATS currently targets:

- PHP 8.4.1
- PHP 8.5

New code should work cleanly on both supported versions.

Please do not add compatibility code for unsupported PHP 5.x, PHP 7.x or older PHP 8.x releases unless there is a specific agreed reason.

One of the current maintenance goals is removing unnecessary PHP 5-era compatibility code rather than adding more of it.

## General development principles

OpenCATS has a large existing codebase. Please work with it rather than creating parallel implementations.

When contributing:

- follow existing OpenCATS coding conventions;
- reuse existing OpenCATS functions and utilities;
- reuse the existing OpenCATS database abstraction;
- reuse existing business logic rather than duplicating it;
- keep changes as small and reviewable as practical;
- preserve backwards compatibility unless the change explicitly requires otherwise;
- avoid introducing new frameworks or architectural layers without a clear benefit;
- avoid increasing code complexity while solving an issue;
- remove obsolete code where it is clearly safe to do so;
- add focused regression tests where practical.

A small, clear pull request is generally easier to review and merge than a large PR containing several unrelated improvements.

## Avoid unnecessary rewrites

Modernising OpenCATS does not mean rewriting working modules simply because newer PHP or JavaScript syntax exists.

Changes should normally provide at least one meaningful benefit:

- PHP 8.4 / PHP 8.5 correctness;
- removal of deprecated or obsolete behaviour;
- improved security;
- improved performance;
- reduced complexity;
- improved maintainability;
- improved user experience;
- new agreed ATS functionality.

Large stylistic rewrites make regressions and code review harder and should generally be avoided.

## PHP changes

When changing PHP code:

- target PHP 8.4.1 and PHP 8.5;
- prefer existing OpenCATS helper classes/functions where available;
- do not introduce direct database access where the OpenCATS database layer already provides the required functionality;
- do not suppress PHP warnings or deprecations simply to make them disappear;
- handle `null`, `false` and unexpected return values deliberately;
- avoid adding dynamic properties;
- avoid restoring PHP-version compatibility branches which are no longer required.

When removing PHP 5-era code, make sure the old behaviour is understood before changing it.

Examples of useful legacy cleanup include obsolete constructors, unsupported PHP-version branches, deprecated APIs and old compatibility workarounds.

## Database changes

Database and query changes require particular care because many OpenCATS installations contain years of historical data.

Please:

- reuse the existing OpenCATS database abstraction;
- avoid introducing an ORM or second database layer;
- preserve existing permission/filter behaviour;
- consider performance on large datasets;
- avoid unnecessary full-table scans;
- avoid retrieving columns which are not required;
- justify new indexes using actual query patterns;
- provide migration/upgrade handling for schema changes;
- ensure existing installations can upgrade without manual database edits.

Performance improvements should ideally demonstrate why the new query is preferable to the existing one.

## JavaScript and frontend changes

OpenCATS is being modernised incrementally.

For frontend work:

- use modern browser JavaScript for new straightforward functionality;
- avoid adding new jQuery dependencies;
- do not rewrite complex working jQuery code solely for stylistic reasons;
- reuse existing OpenCATS UI functionality where appropriate;
- maintain Bootstrap compatibility where the relevant screen uses Bootstrap;
- consider responsive behaviour and accessibility;
- avoid adding a large frontend framework for a small feature.

Frontend modernisation should remain incremental and reviewable.

## Recruitment functionality

OpenCATS is first and foremost an Applicant Tracking System.

New recruiter functionality should improve real recruitment workflows rather than simply add more fields or screens.

Where practical:

- use existing Candidate, Contact, Company, Job Order and Activity data;
- avoid maintaining duplicate information which can be derived reliably;
- prefer structured information where it materially improves searching, matching or workflow;
- ensure new information is actually surfaced usefully to recruiters;
- integrate with existing OpenCATS permissions and ownership concepts;
- avoid creating separate parallel activity, communication or matching models.

The roadmap describes the broader direction for activities, next actions, recruiter dashboards, candidate search, matching and communications.

## AI and semantic search

AI functionality is a longer-term enhancement and should build on the normal OpenCATS search and matching functionality rather than replace it.

AI-related contributions should:

- keep ordinary OpenCATS functionality available without an AI service;
- avoid hard-dependence on one commercial provider;
- clearly distinguish source evidence from model inference;
- provide understandable reasons for candidate/job recommendations;
- retain recruiter control over decisions;
- reuse existing OpenCATS candidate and job data.

Please discuss substantial AI architecture before implementation.

## Dependencies

Please avoid adding new dependencies unless they provide a clear benefit which cannot reasonably be achieved using existing OpenCATS or PHP functionality.

New dependencies increase:

- maintenance requirements;
- security exposure;
- release complexity;
- upgrade complexity.

If adding a dependency, explain why it is required in the pull request.

Do not update deliberately pinned dependencies without checking why they are pinned.

## Testing

OpenCATS uses PHPUnit and Behat.

A contribution should include an appropriate regression test where practical.

Use:

- **PHPUnit** for isolated/unit behaviour;
- **Integration tests** for database/application integration;
- **Behat** for user-visible OpenCATS workflows.

Install development dependencies with:

```bash
composer install
```

Run the unit test suite with:

```bash
./vendor/bin/phpunit --testsuite UnitTests
```

The CI environment also runs integration tests and Behat using the test Docker environment.

For workflow changes, useful Behat coverage may include:

- candidate creation/editing;
- company/contact creation;
- job-order creation/editing;
- pipeline progression;
- activities;
- Career Portal applications;
- questionnaires.

Do not create a large test framework for a small bug. A focused regression test which demonstrates the behaviour is preferable.

## Code quality

OpenCATS uses automated code-quality analysis as one signal when reviewing changes.

Please avoid introducing:

- unnecessary complexity;
- duplicated logic;
- unreachable code;
- unused variables/functions;
- large functions where a straightforward simplification is available.

However, do not perform large or risky rewrites simply to improve a quality metric.

A code-quality improvement should leave OpenCATS easier to understand and maintain.

## Pull requests

Keep pull requests focused on one logical change.

Where possible:

- explain the problem being solved;
- explain the approach taken;
- reference the relevant Issue or roadmap item;
- mention any database/schema impact;
- describe how the change was tested;
- include screenshots for meaningful UI changes.

Please avoid mixing unrelated cleanup into a feature or bug-fix PR.

If you discover another worthwhile issue while working, a separate PR is often preferable.

## Pull Request Title Format

Pull request titles must follow this format:

`type: description`

Allowed types are:

- `chore`
- `docs`
- `feat`
- `fix`
- `refactor`
- `security`
- `test`

Scopes are currently not allowed.

Valid:

```text
fix: correct login redirect
```

Not valid:

```text
fix(auth): correct login redirect
```

## Review expectations

Maintainers may request changes for reasons including:

- backwards compatibility;
- existing OpenCATS conventions;
- duplicated functionality;
- database performance;
- PHP 8.4 / PHP 8.5 compatibility;
- increased complexity;
- insufficient regression coverage;
- unnecessarily broad scope.

This is normal code review and helps keep OpenCATS maintainable for both current users and future contributors.

## Contributor-friendly work

If you would like to contribute but are unsure where to begin, visit:

**[OpenCATS Modernisation & Contributor Roadmap](https://github.com/orgs/opencats/projects/2)**

Look particularly at the **Contributor Friendly** and **Ready to Pick Up** views.

There is useful work available at many levels, from small legacy PHP and code-quality fixes through to substantial recruitment features.

Contributions do not need to be large to be valuable.
