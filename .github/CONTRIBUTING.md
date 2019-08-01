# Contribution Guide

All contributions (in the form of pull requests, issues and proposals) are always welcomed.
Please see the [contributors page](../../graphs/contributors) for a list of all contributors.

Please check below the standards and practices that this project adheres to.

## Etiquette

This project is open source, and as such, the maintainers give their free time to build and maintain the source code held within. They make the code freely available in the hope that it will be of use to other developers. It would be extremely unfair for them to suffer abuse or anger for their hard work.

Please be considerate towards maintainers when raising issues or presenting pull requests. Let's show the world that developers are civilized and selfless people.

It's the duty of the maintainer to ensure that all submissions to the project are of sufficient quality to benefit the project. Many developers have different skillsets, strengths, and weaknesses. Respect the maintainer's decision, and do not be upset or abusive if your submission is not used.

## Versioning

This package is versioned under the [Semantic Versioning][link-semver] guidelines as much as possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backward compatibility bumps the major and resets the minor and patch.
* New additions without breaking backward compatibility bumps the minor and resets the patch.
* Bug fixes and misc changes bumps the patch.

## Coding Standards

This package is compliant with the [PSR-1][link-psr-1], [PSR-2][link-psr-2] and [PSR-4][link-psr-4].
If you notice any compliance oversights, please send a patch via pull request.

## Issues \ Bugs

If you believe you've found a bug on this project, but you're not sure how to fix it, you may create an issue with `[Bug]` prefixed in the title.

### Procedure

Before filing an issue:

- Attempt to replicate the problem, to ensure that it wasn't a coincidental incident.
- Check the issues tab to ensure that the bug was not already reported.
- Check the pull requests tab to ensure that the bug doesn't have a fix in progress.

### Which Branch?

**ALL** bug fixes should be made to the branch which they belong to. Bug fixes should never be sent to the `master` branch unless they fix features that exist only in the upcoming release.

If a bug is found on a `minor` version `1.1` and it exists on the `major` version `1.0`, the bug fix should be sent to the `1.0` branch which will be afterwards merged into the `1.1` branch.

> **Note:** Pull requests which do not follow these guidelines will be closed without any further notice.

## Proposals

If you have a proposal or a feature request, you may create an issue with `[Proposal]` prefixed in the title.

The proposal should also describe the new feature, as well as implementation ideas.
The proposal will then be reviewed and either approved or denied. Once a proposal is approved, a pull request may be created implementing the new feature.

> When requesting or submitting new features, first consider whether it might be useful to others. Open source projects are used by many developers, who may have entirely different needs to your own. Think about whether or not your feature is likely to be used by other users of the project.

### Procedure

Before create a feature request:

- Check the codebase to ensure that your feature doesn't already exist.
- Check the issues tab to ensure that the feature request was not requested already.
- Check the pull requests tab to ensure that the feature isn't already in progress.

## Running Tests

You will need an install of [Composer](https://getcomposer.org) before continuing.

First, install the dependencies:

```bash
$ composer install
```

Then run phpunit:

```bash
$ vendor/bin/phpunit
```

If the test suite passes on your local machine you should be good to go.

When you make a pull request, the tests will automatically be run again by [Travis CI][link-travis] on multiple php versions.


[link-semver]: http://semver.org
[link-travis]: https://travis-ci.org
[link-psr-1]: http://www.php-fig.org/psr/psr-1/
[link-psr-2]: http://www.php-fig.org/psr/psr-2/
[link-psr-4]: http://www.php-fig.org/psr/psr-4/
