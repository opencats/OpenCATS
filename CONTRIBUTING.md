# Contributing to OpenCATS

Welcome to OpenCATS! We're excited that you're interested in contributing to the open-source applicant tracking system. Whether you're fixing a bug, adding a feature, improving documentation, or helping with testing, your contributions are valuable and appreciated.

This guide will help you get started and ensure a smooth contribution process.

## Table of Contents

- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Code Standards](#code-standards)
- [Testing Requirements](#testing-requirements)
- [Documentation](#documentation)
- [Pull Request Process](#pull-request-process)
- [License](#license)

## Getting Started

### Prerequisites

Before you begin, ensure you have the following installed:

- Git
- Docker and Docker Compose
- PHP 7.2 or higher (for local development without Docker)
- Composer

### Fork and Clone

1. **Fork the repository** on GitHub by clicking the "Fork" button.

2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/YOUR_USERNAME/opencats.git
   cd opencats
   ```

3. **Add the upstream remote** to keep your fork synchronized:
   ```bash
   git remote add upstream https://github.com/opencats/OpenCATS.git
   ```

### Docker Development Setup

The recommended way to set up your development environment is using Docker.

1. **Copy the development Docker Compose file**:
   ```bash
   cp docker/docker-compose-dev.yml docker-compose.yml
   ```

2. **Start the containers**:
   ```bash
   docker-compose up -d
   ```

3. **Access the application** at `http://localhost:8080` (or the port specified in your configuration).

### Database Setup

If you're setting up a fresh development environment:

1. **Run the database migrations**:
   ```bash
   docker-compose exec app php scripts/migrate.php
   ```

2. **Load sample data** (optional, for testing):
   ```bash
   docker-compose exec app php scripts/load-sample-data.php
   ```

For manual database setup, refer to the SQL files in the `db/` directory.

## Development Workflow

### Branch Strategy

We use a branching model based on Git Flow:

- `master` - Production-ready code
- `develop` - Main development branch (target for PRs)
- Feature branches - For new features and enhancements
- Bugfix branches - For bug fixes
- Hotfix branches - For urgent production fixes

### Creating a Branch

Always create your branch from `develop`:

```bash
git checkout develop
git pull upstream develop
git checkout -b feature/your-feature-name
```

### Branch Naming Conventions

Use descriptive branch names with the appropriate prefix:

| Prefix | Purpose | Example |
|--------|---------|---------|
| `feature/` | New features or enhancements | `feature/api-pagination` |
| `bugfix/` | Bug fixes | `bugfix/login-redirect-issue` |
| `hotfix/` | Urgent production fixes | `hotfix/security-patch` |
| `docs/` | Documentation updates | `docs/api-documentation` |
| `refactor/` | Code refactoring | `refactor/candidate-module` |

### Commit Message Format

Write clear, descriptive commit messages:

```
<type>(<scope>): <short summary>

<optional body with more details>

<optional footer with references>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(api): add pagination support for candidates endpoint

fix(auth): resolve session timeout redirect issue

docs(readme): update installation instructions for Docker
```

### Keep Commits Atomic

- Each commit should represent a single logical change
- Avoid mixing unrelated changes in the same commit
- If you need to fix something unrelated, create a separate commit

## Code Standards

### PHP Version Compatibility

- All code must be compatible with **PHP 7.2+**
- Avoid using features from newer PHP versions unless they are polyfilled
- Test your code on PHP 7.2 to ensure compatibility

### File Size Limits

- **Maximum 1000 lines per file**
- If a file exceeds this limit, refactor it into smaller, focused modules
- Break large classes into smaller components or traits
- Extract utility functions into separate files

### Code Style Guidelines

Follow the existing code style in the project:

- Use **4 spaces** for indentation (no tabs)
- Opening braces on the same line for control structures
- Opening braces on a new line for class and function definitions
- Use meaningful variable and function names
- Keep functions focused and single-purpose

### PHPDoc Comments

Add PHPDoc comments for all new functions, methods, and classes:

```php
/**
 * Retrieves a candidate by their unique identifier.
 *
 * @param int $candidateId The unique identifier of the candidate.
 * @param bool $includeHistory Whether to include activity history.
 *
 * @return array|null The candidate data or null if not found.
 *
 * @throws InvalidArgumentException If the candidate ID is invalid.
 */
public function getCandidateById(int $candidateId, bool $includeHistory = false): ?array
{
    // Implementation
}
```

### Additional Guidelines

- Avoid global variables; use dependency injection
- Handle errors gracefully with proper exception handling
- Sanitize all user input to prevent security vulnerabilities
- Use prepared statements for database queries

## Testing Requirements

### Running Tests

Before submitting a pull request, ensure all tests pass:

```bash
# Using Composer
composer test

# Using PHPUnit directly
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Unit/CandidateTest.php

# Run with Docker
docker-compose exec app composer test
```

### Test Coverage Requirements

- **All new features must include tests**
- **Bug fixes should include a regression test**
- Aim for meaningful test coverage, not just high percentages
- Test edge cases and error conditions

### Types of Tests

| Test Type | Location | Purpose |
|-----------|----------|---------|
| Unit Tests | `tests/Unit/` | Test individual components in isolation |
| Integration Tests | `tests/Integration/` | Test component interactions |
| Behat Tests | `tests/Behat/` | Test UI workflows and user scenarios |

### Writing Tests

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use OpenCATS\Candidate;

class CandidateTest extends TestCase
{
    public function testCandidateCreation(): void
    {
        $candidate = new Candidate([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        $this->assertEquals('John', $candidate->getFirstName());
        $this->assertEquals('Doe', $candidate->getLastName());
    }
}
```

### Behat Tests for UI Features

For features that involve UI interactions, add Behat scenarios:

```gherkin
Feature: Candidate Management
  As a recruiter
  I want to add new candidates
  So that I can track applicants

  Scenario: Add a new candidate
    Given I am logged in as a recruiter
    When I navigate to the candidates page
    And I click "Add Candidate"
    And I fill in the candidate details
    Then I should see a success message
```

## Documentation

### Documentation Requirements

- Update relevant documentation when making changes
- Keep documentation in sync with code changes
- Write for users who may not be familiar with the codebase

### Documentation Locations

| Type | Location |
|------|----------|
| User guides | `docs/` |
| API documentation | `docs/API_DOCUMENTATION.md` |
| Development guides | `docs/development/` |
| Inline code docs | Within source files (PHPDoc) |

### API Documentation

When modifying or adding API endpoints:

1. Update `docs/API_DOCUMENTATION.md`
2. Include request/response examples
3. Document all parameters and their types
4. Note any authentication requirements

### Inline Code Comments

- Add comments explaining "why" rather than "what"
- Document complex algorithms or business logic
- Add TODO comments for known issues (with issue references)

```php
// Calculate weighted score based on skills match
// Formula derived from industry-standard ATS scoring
$score = ($skillsMatch * 0.4) + ($experienceMatch * 0.35) + ($educationMatch * 0.25);
```

## Pull Request Process

### Before Submitting

1. **Ensure your branch is up to date**:
   ```bash
   git fetch upstream
   git rebase upstream/develop
   ```

2. **Run all tests** and ensure they pass

3. **Review your changes** for code style and documentation

### Creating the Pull Request

1. **Push your branch** to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Create a Pull Request** on GitHub against the `develop` branch (not `master`)

3. **Fill out the PR template completely**:
   - Describe what changes you made
   - Explain why the changes are needed
   - Reference any related issues
   - Include testing instructions

### PR Template

```markdown
## Description
Brief description of the changes.

## Related Issues
Fixes #123

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
Describe how to test the changes.

## Checklist
- [ ] Tests pass locally
- [ ] Code follows project style guidelines
- [ ] Documentation updated
- [ ] PHPDoc comments added for new functions
```

### After Submitting

- **Respond to review feedback** promptly
- **Make requested changes** in new commits (don't force-push during review)
- **Keep the PR focused** - open separate PRs for unrelated changes
- **Ensure CI passes** - all automated checks must be green

### Review Process

1. Maintainers will review your PR within a few business days
2. You may receive requests for changes or clarification
3. Once approved, a maintainer will merge your PR
4. Your contribution will be included in the next release

## License

By contributing to OpenCATS, you agree that your contributions will be licensed under the **Mozilla Public License 2.0 (MPL 2.0)**.

- Your code will be open source and freely available
- Others can use, modify, and distribute your contributions
- See the [LICENSE](LICENSE) file for full license text

### What This Means for Contributors

- You retain copyright of your contributions
- You grant the project a license to use your code under MPL 2.0
- You confirm you have the right to contribute the code
- Commercial and non-commercial use is permitted

---

## Questions?

If you have questions about contributing:

- Open a discussion on GitHub
- Check existing issues for similar questions
- Reach out to the maintainers

Thank you for contributing to OpenCATS! Your efforts help make recruitment accessible to organizations of all sizes.
